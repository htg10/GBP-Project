<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CreditLedger;
use App\Models\CreditPackage;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(
        private RazorpayService $razorpay,
        private CreditService $credits,
    ) {}

    public function plans(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $plans = Plan::where('is_active', true)->orderBy('sort')->orderBy('price')->get();

        return response()->json([
            'plans' => $plans->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code,
                'price' => $p->price,
                'gst_rate' => $p->gst_rate,
                'credits' => $p->credits,
                'features' => $p->features,
                'permissions' => $p->permissions,
            ]),
            'current_plan' => $sub->plan ?? null,
            'status' => $sub->status ?? null,
            'credit_balance' => $this->credits->balance($agencyId),
            'razorpay_ready' => $this->razorpay->configured(),
        ]);
    }

    public function credits(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();

        $ledger = CreditLedger::where('agency_id', $agencyId)
            ->orderByDesc('created_at')->take(50)->get();

        return response()->json([
            'balance' => $this->credits->balance($agencyId),
            'monthly_credits' => $sub->monthly_credits ?? 0,
            'usage_this_month' => $this->credits->usageThisMonth($agencyId),
            'credit_costs' => CreditService::COSTS,
            'ledger' => $ledger->map(fn ($l) => [
                'id' => $l->id,
                'amount' => $l->amount,
                'balance_after' => $l->balance_after,
                'action' => $l->action,
                'description' => $l->description,
                'created_at' => $l->created_at,
            ]),
        ]);
    }

    /** Active credit packs for the Buy Credits screen. */
    public function creditPackages(Request $request)
    {
        $packages = CreditPackage::where('is_active', true)->orderBy('sort')->orderBy('credits')->get();

        return response()->json([
            'packages' => $packages->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'credits' => $p->credits,
                'price' => $p->price,
                'gst_rate' => $p->gst_rate,
            ]),
            'credit_balance' => $this->credits->balance($request->user()->agency_id),
            'razorpay_ready' => $this->razorpay->configured(),
        ]);
    }

    /** Payment history for the Billing & Invoices screen. */
    public function payments(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $payments = Payment::where('agency_id', $agencyId)->latest()->take(100)->get();

        return response()->json([
            'current_plan' => $sub->plan ?? null,
            'status' => $sub->status ?? null,
            'renews_at' => $sub->renews_at ?? null,
            'payments' => $payments->map(fn ($p) => [
                'id' => $p->id,
                'plan' => $p->plan,
                'amount' => $p->amount / 100,
                'currency' => $p->currency ?? 'INR',
                'status' => $p->status,
                'razorpay_payment_id' => $p->razorpay_payment_id,
                'created_at' => $p->created_at,
            ]),
        ]);
    }

    /** Invoice details for one payment (same numbers the web invoice page shows). */
    public function paymentInvoice(Request $request, Payment $payment)
    {
        abort_unless($payment->agency_id === $request->user()->agency_id, 403);

        $plan = Plan::where('code', $payment->plan)->first();
        $gstRate = $plan?->gst_rate ?? 18;

        $total = $payment->amount / 100;
        $base = round($total / (1 + $gstRate / 100), 2);
        $gst = round($total - $base, 2);

        $user = $request->user();

        return response()->json([
            'invoice_number' => 'INV-' . str_pad((string) $payment->id, 5, '0', STR_PAD_LEFT),
            'date' => $payment->created_at?->format('Y-m-d'),
            'plan_name' => $plan?->name ?? $payment->plan,
            'payment_id' => $payment->razorpay_payment_id,
            'status' => $payment->status,
            'gst_rate' => $gstRate,
            'base' => $base,
            'gst' => $gst,
            'total' => $total,
            'billed_to' => [
                'name' => $user->client?->name ?? $user->agency?->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $this->ensureCanManage($request);

        $data = $request->validate(['plan' => 'required|exists:plans,code']);
        $plan = Plan::where('code', $data['plan'])->firstOrFail();

        if (! $this->razorpay->configured()) {
            return response()->json(['error' => 'Payment gateway not configured.'], 422);
        }

        $receipt = 'plan_' . $request->user()->agency_id . '_' . time();
        $order = $this->razorpay->createOrder($plan->price, $receipt);

        Payment::create([
            'agency_id' => $request->user()->agency_id,
            'plan' => $plan->code,
            'amount' => $plan->price * 100,
            'razorpay_order_id' => $order['id'],
            'status' => 'CREATED',
        ]);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $plan->price * 100,
            'currency' => 'INR',
            'plan' => $plan->code,
            'plan_name' => $plan->name,
            'key' => config('services.razorpay.key'),
        ]);
    }

    public function verify(Request $request)
    {
        $this->ensureCanManage($request);

        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'plan' => 'required|exists:plans,code',
        ]);

        $ok = $this->razorpay->verifySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);

        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])->first();
        if ($payment) {
            $payment->update([
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
                'status' => $ok ? 'PAID' : 'FAILED',
            ]);
        }

        if (! $ok) {
            return response()->json(['error' => 'Payment verification failed.'], 422);
        }

        $plan = Plan::where('code', $data['plan'])->firstOrFail();
        $agencyId = $request->user()->agency_id;

        Subscription::updateOrCreate(
            ['agency_id' => $agencyId],
            [
                'plan' => $plan->code,
                'status' => 'ACTIVE',
                'provider' => 'razorpay',
                'external_id' => $data['razorpay_payment_id'],
                'renews_at' => now()->addMonth(),
            ]
        );

        $this->credits->setMonthly($agencyId, $plan->code, $plan->credits);

        return response()->json(['success' => true]);
    }

    public function creditCheckout(Request $request)
    {
        $this->ensureCanManage($request);

        $data = $request->validate(['package' => 'required|exists:credit_packages,id']);
        $pkg = CreditPackage::findOrFail($data['package']);

        if (! $this->razorpay->configured()) {
            return response()->json(['error' => 'Payment gateway not configured.'], 422);
        }

        $receipt = 'cred_' . $request->user()->agency_id . '_' . time();
        $order = $this->razorpay->createOrder($pkg->price, $receipt);

        Payment::create([
            'agency_id' => $request->user()->agency_id,
            'plan' => 'CREDIT_' . $pkg->id,
            'amount' => $pkg->price * 100,
            'razorpay_order_id' => $order['id'],
            'status' => 'CREATED',
        ]);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $pkg->price * 100,
            'currency' => 'INR',
            'package' => ['id' => $pkg->id, 'name' => $pkg->name, 'credits' => $pkg->credits],
            'key' => config('services.razorpay.key'),
        ]);
    }

    public function creditVerify(Request $request)
    {
        $this->ensureCanManage($request);

        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'package' => 'required|exists:credit_packages,id',
        ]);

        $ok = $this->razorpay->verifySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);

        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])->first();
        if ($payment) {
            $payment->update([
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'razorpay_signature' => $data['razorpay_signature'],
                'status' => $ok ? 'PAID' : 'FAILED',
            ]);
        }

        if (! $ok) {
            return response()->json(['error' => 'Payment verification failed.'], 422);
        }

        $pkg = CreditPackage::findOrFail($data['package']);
        $agencyId = $request->user()->agency_id;

        $newBalance = $this->credits->add(
            $agencyId,
            $pkg->credits,
            'credit_purchase',
            "Bought {$pkg->name} ({$pkg->credits} credits)",
            $request->user()->id
        );

        return response()->json(['success' => true, 'balance' => $newBalance]);
    }

    private function ensureCanManage(Request $request): void
    {
        abort_unless(in_array($request->user()->role, ['SUPER_ADMIN', 'CLIENT_OWNER']), 403);
    }
}
