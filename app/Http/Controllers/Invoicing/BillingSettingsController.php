<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use Illuminate\Http\Request;

class BillingSettingsController extends Controller
{
    public function edit(Request $request)
    {
        $settings = BillingSetting::firstOrCreate(['agency_id' => $request->user()->agency_id]);
        return view('dashboard.invoicing.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'business_type' => 'nullable|string|max:60',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'state' => 'nullable|string|max:60',
            'logo' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:50',
            'ifsc' => 'nullable|string|max:20',
            'invoice_prefix' => 'required|string|max:10',
            'default_gst' => 'nullable|numeric|min:0|max:100',
            'currency' => 'nullable|string|max:10',
            'round_total' => 'nullable',
            'tally_company_name' => 'nullable|string|max:255',
            'customer_ledger_group' => 'nullable|string|max:255',
        ]);

        $data['round_total'] = $request->has('round_total');

        $settings = BillingSetting::firstOrCreate(['agency_id' => $request->user()->agency_id]);
        $settings->update($data);

        return back()->with('success', 'Billing settings saved.');
    }
}
