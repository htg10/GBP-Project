<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicingController extends Controller
{
    // ---- Invoices ----

    public function invoices(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $search = $request->query('q');

        $query = Invoice::where('agency_id', $agencyId)->with('client');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }
        $invoices = $query->orderByDesc('issue_date')->get();

        $invoices->each(function ($inv) {
            if ($inv->isOverdue()) $inv->status = 'OVERDUE';
        });

        $all = Invoice::where('agency_id', $agencyId)->get();
        $stats = [
            'outstanding' => $all->whereIn('status', ['SENT', 'OVERDUE'])->sum('total'),
            'paid_this_month' => $all->where('status', 'PAID')
                ->filter(fn ($i) => $i->paid_at && $i->paid_at->isCurrentMonth())->sum('total'),
            'drafts' => $all->where('status', 'DRAFT')->count(),
        ];

        return response()->json([
            'invoices' => $invoices->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'client_name' => $inv->client?->name,
                'issue_date' => $inv->issue_date,
                'due_date' => $inv->due_date,
                'status' => $inv->status,
                'total' => $inv->total,
            ]),
            'stats' => $stats,
        ]);
    }

    public function createInvoice(Request $request)
    {
        $agencyId = $request->user()->agency_id;

        $data = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:issue_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        Client::where('agency_id', $agencyId)->findOrFail($data['client_id']);

        $invoice = DB::transaction(function () use ($data, $agencyId) {
            $settings = BillingSetting::firstOrCreate(['agency_id' => $agencyId]);
            $number = $settings->invoice_prefix . '-' . str_pad($settings->next_invoice_number, 4, '0', STR_PAD_LEFT);
            $settings->increment('next_invoice_number');

            $subtotal = 0;
            $gstTotal = 0;
            $lines = [];
            foreach ($data['items'] as $item) {
                $lineBase = $item['quantity'] * $item['unit_price'];
                $gstPct = $item['gst_percent'] ?? 18;
                $lineGst = round($lineBase * $gstPct / 100, 2);
                $subtotal += $lineBase;
                $gstTotal += $lineGst;
                $lines[] = [
                    'service_id' => $item['service_id'] ?? null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'gst_percent' => $gstPct,
                    'amount' => $lineBase + $lineGst,
                ];
            }

            $invoice = Invoice::create([
                'agency_id' => $agencyId,
                'client_id' => $data['client_id'],
                'invoice_number' => $number,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'DRAFT',
                'subtotal' => $subtotal,
                'gst_total' => $gstTotal,
                'total' => $subtotal + $gstTotal,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->createMany($lines);

            return $invoice;
        });

        return response()->json($invoice->load('items', 'client'), 201);
    }

    public function showInvoice(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        $invoice->load('items', 'client');
        $settings = BillingSetting::firstOrCreate(['agency_id' => $invoice->agency_id]);

        return response()->json([
            'invoice' => $invoice,
            'settings' => $settings,
        ]);
    }

    public function markSent(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        $invoice->update(['status' => 'SENT']);
        return response()->json(['success' => true]);
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        $invoice->update(['status' => 'PAID', 'paid_at' => now()]);
        return response()->json(['success' => true]);
    }

    public function destroyInvoice(Request $request, Invoice $invoice)
    {
        $this->authorizeInvoice($request, $invoice);
        abort_unless($invoice->status === 'DRAFT', 403, 'Only draft invoices can be deleted.');
        $invoice->delete();
        return response()->json(['success' => true]);
    }

    // ---- Customers ----

    public function customers(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $customers = Client::where('agency_id', $agencyId)
            ->withCount('invoices')
            ->orderBy('name')
            ->get();

        return response()->json($customers->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'gstin' => $c->gstin,
            'billing_address' => $c->billing_address,
            'invoices_count' => $c->invoices_count,
        ]));
    }

    public function storeCustomer(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'gstin' => 'nullable|string|max:20',
            'billing_address' => 'nullable|string|max:255',
        ]);
        $data['agency_id'] = $request->user()->agency_id;
        $customer = Client::create($data);

        return response()->json($customer, 201);
    }

    public function updateCustomer(Request $request, Client $client)
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

        return response()->json(['success' => true]);
    }

    // ---- Services ----

    public function services(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $services = Service::where('agency_id', $agencyId)->with('category')->orderBy('name')->get();

        return response()->json($services->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'price' => $s->price,
            'gst_percent' => $s->gst_percent,
            'status' => $s->status,
            'category' => $s->category?->name,
            'service_category_id' => $s->service_category_id,
        ]));
    }

    public function storeService(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
        ]);
        $data['agency_id'] = $request->user()->agency_id;
        $service = Service::create($data);

        return response()->json($service, 201);
    }

    public function updateService(Request $request, Service $service)
    {
        abort_unless($service->agency_id === $request->user()->agency_id, 403);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'price' => 'required|numeric|min:0',
            'gst_percent' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ]);
        $service->update($data);

        return response()->json(['success' => true]);
    }

    public function destroyService(Request $request, Service $service)
    {
        abort_unless($service->agency_id === $request->user()->agency_id, 403);
        $service->delete();
        return response()->json(['success' => true]);
    }

    // ---- Categories ----

    public function categories(Request $request)
    {
        $categories = ServiceCategory::where('agency_id', $request->user()->agency_id)
            ->withCount('services')
            ->orderBy('name')
            ->get();

        return response()->json($categories->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'services_count' => $c->services_count,
        ]));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $data['agency_id'] = $request->user()->agency_id;
        $cat = ServiceCategory::create($data);

        return response()->json($cat, 201);
    }

    public function destroyCategory(Request $request, ServiceCategory $category)
    {
        abort_unless($category->agency_id === $request->user()->agency_id, 403);
        $category->delete();
        return response()->json(['success' => true]);
    }

    // ---- Expenses ----

    public function expenses(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $month = $request->query('month');

        $query = Expense::where('agency_id', $agencyId);
        if ($month) {
            $query->whereYear('date', substr($month, 0, 4))->whereMonth('date', substr($month, 5, 2));
        }
        $expenses = $query->orderByDesc('date')->get();

        $thisMonth = Expense::where('agency_id', $agencyId)
            ->whereYear('date', now()->year)->whereMonth('date', now()->month)->get();

        $stats = [
            'spent_this_month' => $thisMonth->sum('amount'),
            'count' => $expenses->count(),
        ];

        return response()->json([
            'expenses' => $expenses->map(fn ($e) => [
                'id' => $e->id,
                'date' => $e->date,
                'description' => $e->description,
                'category' => $e->category,
                'method' => $e->method,
                'amount' => $e->amount,
            ]),
            'stats' => $stats,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'method' => 'required|in:CASH,BANK,UPI,CARD,OTHER',
            'amount' => 'required|numeric|min:0',
        ]);
        $data['agency_id'] = $request->user()->agency_id;
        $expense = Expense::create($data);

        return response()->json($expense, 201);
    }

    public function destroyExpense(Request $request, Expense $expense)
    {
        abort_unless($expense->agency_id === $request->user()->agency_id, 403);
        $expense->delete();
        return response()->json(['success' => true]);
    }

    // ---- Billing Settings ----

    public function billingSettings(Request $request)
    {
        $settings = BillingSetting::firstOrCreate(['agency_id' => $request->user()->agency_id]);
        return response()->json($settings);
    }

    public function updateBillingSettings(Request $request)
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
            'round_total' => 'nullable|boolean',
            'tally_company_name' => 'nullable|string|max:255',
            'customer_ledger_group' => 'nullable|string|max:255',
        ]);

        $data['round_total'] = $request->boolean('round_total');

        $settings = BillingSetting::firstOrCreate(['agency_id' => $request->user()->agency_id]);
        $settings->update($data);

        return response()->json(['success' => true]);
    }

    // ---- Private ----

    private function authorizeInvoice(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->agency_id === $request->user()->agency_id, 403);
    }
}
