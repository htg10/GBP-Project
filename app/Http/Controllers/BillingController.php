<?php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    public function __construct(private RazorpayService $razorpay, private CreditService $credits) {}

    private function activePlans()
    {
        return Plan::where('is_active', true)->orderBy('sort')->orderBy('price')->get();
    }

    private function plan(string $code): ?Plan
    {
        return Plan::where('code', $code)->first();
    }

    private function canManage(Request $request): bool
    {
        return in_array($request->user()->role, ['SUPER_ADMIN', 'CLIENT_OWNER'], true);
    }

    public function credits(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $creditBalance = $this->credits->balance($agencyId);
        $usageThisMonth = $this->credits->usageThisMonth($agencyId);
        $creditCosts = CreditService::COSTS;
        $planCredits = CreditService::PLAN_CREDITS;

        $ledger = \App\Models\CreditLedger::where('agency_id', $agencyId)
            ->orderByDesc('created_at')
            ->take(50)
            ->get();

        return view('dashboard.credits', compact('sub', 'creditBalance', 'usageThisMonth', 'creditCosts', 'planCredits', 'ledger'));
    }

    /** User-facing plans page (self-serve upgrade), driven by DB plans. */
    public function plans(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $plans = $this->activePlans();
        $creditBalance = $this->credits->balance($agencyId);
        $razorpayReady = $this->razorpay->configured();
        $canManage = $this->canManage($request);

        return view('dashboard.plans', compact('plans', 'sub', 'creditBalance', 'razorpayReady', 'canManage'));
    }

    /** Instant activation (demo mode / no gateway). */
    public function upgrade(Request $request)
    {
        abort_unless($this->canManage($request), 403, 'You are not allowed to change the plan.');

        $data = $request->validate(['plan' => ['required', Rule::exists('plans', 'code')]]);
        $agencyId = $request->user()->agency_id;
        $plan = $this->plan($data['plan']);

        Subscription::updateOrCreate(
            ['agency_id' => $agencyId],
            ['plan' => $plan->code, 'status' => 'ACTIVE', 'renews_at' => now()->addMonth()]
        );

        $this->credits->setMonthly($agencyId, $plan->code, $plan->credits);

        return redirect()->route('plans')->with(
            'success',
            "You're now on the {$plan->name} plan — ".number_format($plan->credits).' AI credits allocated.'
        );
    }

    /** Client "Buy Credits" page — lists active packages. */
    public function buyCredits(Request $request)
    {
        $packages = CreditPackage::where('is_active', true)->orderBy('sort')->orderBy('credits')->get();
        $balance = $this->credits->balance($request->user()->agency_id);
        $razorpayReady = $this->razorpay->configured();
        $canManage = $this->canManage($request);
        return view('dashboard.buy-credits', compact('packages', 'balance', 'razorpayReady', 'canManage'));
    }

    /** Instant buy (demo / no gateway) — ADDS credits to the balance. */
    public function creditBuyInstant(Request $request)
    {
        abort_unless($this->canManage($request), 403, 'You are not allowed to buy credits.');
        $data = $request->validate(['package' => 'required|exists:credit_packages,id']);
        $pkg = CreditPackage::find($data['package']);

        $new = $this->credits->add(
            $request->user()->agency_id, $pkg->credits, 'credit_purchase',
            "Bought {$pkg->name} ({$pkg->credits} credits)", $request->user()->id
        );

        return back()->with('success', number_format($pkg->credits)." credits added. New balance: ".number_format($new).".");
    }

    /** Razorpay: create order for a credit package. */
    public function creditCheckout(Request $request)
    {
        abort_unless($this->canManage($request), 403);
        $data = $request->validate(['package' => 'required|exists:credit_packages,id']);
        $pkg = CreditPackage::find($data['package']);

        if (! $this->razorpay->configured()) {
            return response()->json(['error' => 'Razorpay keys missing in .env'], 422);
        }

        $order = $this->razorpay->createOrder($pkg->price, 'cr_'.$request->user()->agency_id.'_'.time());
        if (! $order) {
            return response()->json(['error' => 'Could not create order.'], 422);
        }

        Payment::create([
            'agency_id' => $request->user()->agency_id,
            'plan' => 'CREDITS-'.$pkg->credits,
            'amount' => $pkg->price * 100,
            'currency' => 'INR',
            'razorpay_order_id' => $order['id'],
            'status' => 'CREATED',
        ]);

        return response()->json([
            'order_id' => $order['id'], 'amount' => $order['amount'], 'currency' => $order['currency'],
            'package' => $pkg->id, 'name' => $pkg->name, 'key' => config('services.razorpay.key'),
        ]);
    }

    /** Razorpay: verify + ADD the purchased credits. */
    public function creditVerify(Request $request)
    {
        abort_unless($this->canManage($request), 403);
        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'package' => 'required|exists:credit_packages,id',
        ]);

        $ok = $this->razorpay->verifySignature($data['razorpay_order_id'], $data['razorpay_payment_id'], $data['razorpay_signature']);
        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])->first();

        if (! $ok) {
            if ($payment) $payment->update(['status' => 'FAILED']);
            return response()->json(['error' => 'Payment verification failed'], 422);
        }
        if ($payment) $payment->update(['razorpay_payment_id' => $data['razorpay_payment_id'], 'status' => 'PAID']);

        $pkg = CreditPackage::find($data['package']);
        $this->credits->add(
            $request->user()->agency_id, $pkg->credits, 'credit_purchase',
            "Bought {$pkg->name} ({$pkg->credits} credits)", $request->user()->id
        );

        return response()->json(['success' => true]);
    }

    public function topup(Request $request)
    {
        $data = $request->validate([
            'agency_id' => 'required|exists:agencies,id',
            'credits' => 'required|integer|min:1|max:10000',
        ]);

        $newBalance = $this->credits->add(
            $data['agency_id'],
            $data['credits'],
            'manual_topup',
            'Admin top-up by ' . $request->user()->name,
            $request->user()->id
        );

        return back()->with('success', "Added {$data['credits']} credits. New balance: {$newBalance}");
    }

    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $payments = Payment::where('agency_id', $agencyId)->latest()->take(15)->get();
        $razorpayReady = $this->razorpay->configured();

        $creditBalance = $this->credits->balance($agencyId);
        $usageThisMonth = $this->credits->usageThisMonth($agencyId);
        $creditCosts = CreditService::COSTS;

        return view('admin.billing', compact('sub', 'payments', 'razorpayReady', 'creditBalance', 'usageThisMonth', 'creditCosts'));
    }

    /** Step 1: create a Razorpay order for the chosen plan (GST-inclusive price). */
    public function checkout(Request $request)
    {
        abort_unless($this->canManage($request), 403, 'You are not allowed to change the plan.');
        $data = $request->validate(['plan' => ['required', Rule::exists('plans', 'code')]]);
        $plan = $this->plan($data['plan']);

        if (! $this->razorpay->configured()) {
            return response()->json(['error' => 'Razorpay keys missing in .env'], 422);
        }

        $agencyId = $request->user()->agency_id;
        $receipt = 'rf_'.$agencyId.'_'.time();

        $order = $this->razorpay->createOrder($plan->price, $receipt);
        if (! $order) {
            return response()->json(['error' => 'Could not create order. Check keys / logs.'], 422);
        }

        Payment::create([
            'agency_id' => $agencyId,
            'plan' => $plan->code,
            'amount' => $plan->price * 100,
            'currency' => 'INR',
            'razorpay_order_id' => $order['id'],
            'status' => 'CREATED',
        ]);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'plan' => $plan->code,
            'plan_name' => $plan->name,
            'key' => config('services.razorpay.key'),
        ]);
    }

    /** Step 2: verify the payment signature, then activate the plan. */
    public function verify(Request $request)
    {
        abort_unless($this->canManage($request), 403, 'You are not allowed to change the plan.');
        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'plan' => ['required', Rule::exists('plans', 'code')],
        ]);

        $ok = $this->razorpay->verifySignature(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature']
        );

        $payment = Payment::where('razorpay_order_id', $data['razorpay_order_id'])->first();

        if (! $ok) {
            if ($payment) $payment->update(['status' => 'FAILED']);
            return response()->json(['error' => 'Payment verification failed'], 422);
        }

        if ($payment) {
            $payment->update([
                'razorpay_payment_id' => $data['razorpay_payment_id'],
                'status' => 'PAID',
            ]);
        }

        $agencyId = $request->user()->agency_id;
        $plan = $this->plan($data['plan']);

        Subscription::updateOrCreate(
            ['agency_id' => $agencyId],
            ['plan' => $plan->code, 'status' => 'ACTIVE', 'provider' => 'razorpay', 'renews_at' => now()->addMonth()]
        );

        $this->credits->setMonthly($agencyId, $plan->code, $plan->credits);

        return response()->json(['success' => true]);
    }
}
