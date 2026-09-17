<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

/**
 * "Customers" in the invoicing sense — the businesses the agency invoices.
 * This reuses the existing Client model (no separate customer table) so a
 * client added here shows up everywhere else in the app too, and vice versa.
 */
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $customers = Client::where('agency_id', $agencyId)
            ->withCount('invoices')
            ->orderBy('name')
            ->get();

        return view('dashboard.invoicing.customers', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'gstin' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string|max:255',
        ]);
        $data['agency_id'] = $request->user()->agency_id;
        Client::create($data);

        return back()->with('success', 'Customer added.');
    }

    public function update(Request $request, Client $client)
    {
        abort_unless($client->agency_id === $request->user()->agency_id, 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'gstin' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string|max:255',
        ]);
        $client->update($data);

        return back()->with('success', 'Customer updated.');
    }
}
