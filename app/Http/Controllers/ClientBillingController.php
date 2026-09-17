<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\Request;

/**
 * Billing section for the Client Dashboard: subscription payment history and
 * downloadable (print-to-PDF) invoices with the ReviewFlow logo.
 */
class ClientBillingController extends Controller
{
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $sub = Subscription::where('agency_id', $agencyId)->first();
        $payments = Payment::where('agency_id', $agencyId)->latest()->get();

        return view('dashboard.billing', compact('sub', 'payments'));
    }

    public function invoice(Request $request, Payment $payment)
    {
        // A user may only view invoices for their own agency.
        abort_unless($payment->agency_id === $request->user()->agency_id, 403);

        $plan = Plan::where('code', $payment->plan)->first();
        $gstRate = $plan?->gst_rate ?? 18;

        $total = $payment->amount / 100;            // rupees (GST-inclusive)
        $base = round($total / (1 + $gstRate / 100), 2);
        $gst = round($total - $base, 2);

        $agency = $request->user()->agency;
        $client = $request->user()->client;

        return view('dashboard.invoice', compact('payment', 'plan', 'gstRate', 'total', 'base', 'gst', 'agency', 'client'));
    }
}
