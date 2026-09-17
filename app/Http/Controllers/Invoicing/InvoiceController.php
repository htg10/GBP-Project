<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index(Request $request)
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

        // Auto-flag anything SENT and past its due date as OVERDUE (display-time, no cron needed).
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

        return view('dashboard.invoicing.invoices', compact('invoices', 'stats', 'search'));
    }

    public function create(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $clients = Client::where('agency_id', $agencyId)->orderBy('name')->get();
        $services = Service::where('agency_id', $agencyId)->where('status', 'ACTIVE')->get();
        $settings = BillingSetting::firstOrCreate(['agency_id' => $agencyId]);

        // Pre-built (not computed inline in the Blade @json call — casts like
        // (float) inside a directive's parens can confuse Blade's argument parser).
        $serviceOptions = $services->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'price' => (float) $s->price,
            'gst' => (float) $s->gst_percent,
        ])->values();

        return view('dashboard.invoicing.invoice-form', compact('clients', 'services', 'settings', 'serviceOptions'));
    }

    public function store(Request $request)
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
            $number = $settings->invoice_prefix.'-'.str_pad($settings->next_invoice_number, 4, '0', STR_PAD_LEFT);
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

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Request $request, Invoice $invoice)
    {
        $this->authorize($request, $invoice);
        $invoice->load('items', 'client');
        $settings = BillingSetting::firstOrCreate(['agency_id' => $invoice->agency_id]);

        return view('dashboard.invoicing.invoice-show', compact('invoice', 'settings'));
    }

    public function markSent(Request $request, Invoice $invoice)
    {
        $this->authorize($request, $invoice);
        $invoice->update(['status' => 'SENT']);
        return back()->with('success', 'Invoice marked as sent.');
    }

    public function markPaid(Request $request, Invoice $invoice)
    {
        $this->authorize($request, $invoice);
        $invoice->update(['status' => 'PAID', 'paid_at' => now()]);
        return back()->with('success', 'Invoice marked as paid.');
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $this->authorize($request, $invoice);
        abort_unless($invoice->status === 'DRAFT', 403, 'Only draft invoices can be deleted.');
        $invoice->delete();
        return redirect()->route('invoices')->with('success', 'Invoice deleted.');
    }

    private function authorize(Request $request, Invoice $invoice): void
    {
        abort_unless($invoice->agency_id === $request->user()->agency_id, 403);
    }
}
