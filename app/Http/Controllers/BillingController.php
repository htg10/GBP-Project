<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CreditService;
use App\Services\RazorpayService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    private array $plans = [
        'STARTER' => ['name' => 'Starter', 'price' => 999, 'features' => ['1 GBP location', 'Reviews + AI reply', 'Photo posting']],
        'GROWTH' => ['name' => 'Growth', 'price' => 2999, 'features' => ['Multiple GBP', 'Social posting', 'Lead CRM']],
        'AGENCY' => ['name' => 'Agency', 'price' => 9999, 'features' => ['Unlimited clients', 'Ads reporting', 'White label']],
    ];

    public function __construct(private RazorpayService $razorpay, private CreditService $credits) {}

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

    /** User-facing plans page (self-serve upgrade). */
    public function plans(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $plans = $this->plans;
        $planCredits = CreditService::PLAN_CREDITS;
        $creditBalance = $this->credits->balance($agencyId);
        $razorpayReady = $this->razorpay->configured();
        $canManage = in_array($request->user()->role, ['AGENCY_OWNER', 'SUPER_ADMIN'], true);

        return view('dashboard.plans', compact('plans', 'sub', 'planCredits', 'creditBalance', 'razorpayReady', 'canManage'));
    }

    /** Activate/switch the plan and allocate its monthly credits. */
    public function upgrade(Request $request)
    {
        abort_unless(
            in_array($request->user()->role, ['AGENCY_OWNER', 'SUPER_ADMIN'], true),
            403,
            'Only the account owner can change the plan.'
        );

        $data = $request->validate(['plan' => 'required|in:STARTER,GROWTH,AGENCY']);
        $agencyId = $request->user()->agency_id;

        Subscription::updateOrCreate(
            ['agency_id' => $agencyId],
            ['plan' => $data['plan'], 'status' => 'ACTIVE', 'renews_at' => now()->addMonth()]
        );

        $this->credits->resetMonthly($agencyId, $data['plan']);

        return redirect()->route('plans')->with(
            'success',
            "You're now on the {$this->plans[$data['plan']]['name']} plan — ".number_format(CreditService::PLAN_CREDITS[$data['plan']]).' AI credits allocated.'
        );
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
        $payments = Payment::where('agency_id', $agencyId)->latest()->take(10)->get();
        $plans = $this->plans;
        $razorpayKey = config('services.razorpay.key');
        $razorpayReady = $this->razorpay->configured();

        $creditBalance = $this->credits->balance($agencyId);
        $usageThisMonth = $this->credits->usageThisMonth($agencyId);
        $creditCosts = CreditService::COSTS;
        $planCredits = CreditService::PLAN_CREDITS;

        return view('admin.billing', compact('sub', 'plans', 'payments', 'razorpayKey', 'razorpayReady', 'creditBalance', 'usageThisMonth', 'creditCosts', 'planCredits'));
    }

    /**
     * Step 1: create a Razorpay order for the chosen plan.
     * Returns JSON the frontend uses to open Razorpay Checkout.
     */
    public function checkout(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['AGENCY_OWNER', 'SUPER_ADMIN'], true), 403, 'Only the account owner can change the plan.');
        $data = $request->validate(['plan' => 'required|in:STARTER,GROWTH,AGENCY']);
        $plan = $this->plans[$data['plan']];

        if (! $this->razorpay->configured()) {
            return response()->json(['error' => 'Razorpay keys missing in .env'], 422);
        }

        $agencyId = $request->user()->agency_id;
        $receipt = 'rf_'.$agencyId.'_'.time();

        $order = $this->razorpay->createOrder($plan['price'], $receipt);
        if (! $order) {
            return response()->json(['error' => 'Could not create order. Check keys / logs.'], 422);
        }

        // Record a pending payment.
        Payment::create([
            'agency_id' => $agencyId,
            'plan' => $data['plan'],
            'amount' => $plan['price'] * 100,
            'currency' => 'INR',
            'razorpay_order_id' => $order['id'],
            'status' => 'CREATED',
        ]);

        return response()->json([
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'plan' => $data['plan'],
            'plan_name' => $plan['name'],
            'key' => config('services.razorpay.key'),
        ]);
    }

    /**
     * Step 2: after payment, frontend posts back the ids + signature.
     * We verify, then activate the plan.
     */
    public function verify(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['AGENCY_OWNER', 'SUPER_ADMIN'], true), 403, 'Only the account owner can change the plan.');
        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'plan' => 'required|in:STARTER,GROWTH,AGENCY',
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

        Subscription::updateOrCreate(
            ['agency_id' => $agencyId],
            ['plan' => $data['plan'], 'status' => 'ACTIVE', 'provider' => 'razorpay', 'renews_at' => now()->addMonth()]
        );

        $this->credits->resetMonthly($agencyId, $data['plan']);

        return response()->json(['success' => true]);
    }
}
