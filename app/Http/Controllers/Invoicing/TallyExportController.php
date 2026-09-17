<?php

namespace App\Http\Controllers\Invoicing;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Invoice;
use Illuminate\Http\Request;

/**
 * Exports invoices as Tally-compatible XML (Sales Vouchers) that can be
 * imported via Tally's "Gateway of Tally → Import Data → Vouchers".
 *
 * Each invoice becomes one Sales voucher with three ledger entries: the
 * customer (debited for the total), a Sales Account (credited for the
 * pre-tax subtotal), and a GST Output ledger (credited for the GST amount,
 * when there is any). Ledger names must already exist in the target Tally
 * company, or Tally will prompt to create them during import — that's
 * standard Tally behavior, not something this export can skip.
 */
class TallyExportController extends Controller
{
    public function index(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $count = Invoice::where('agency_id', $agencyId)->whereIn('status', ['SENT', 'PAID', 'OVERDUE'])->count();
        $settings = BillingSetting::firstOrCreate(['agency_id' => $agencyId]);

        return view('dashboard.invoicing.tally-export', compact('count', 'settings'));
    }

    public function export(Request $request)
    {
        $data = $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
        ]);

        $agencyId = $request->user()->agency_id;
        $settings = BillingSetting::firstOrCreate(['agency_id' => $agencyId]);

        $query = Invoice::where('agency_id', $agencyId)
            ->where('status', '!=', 'DRAFT') // only real (sent/paid) invoices belong in the books
            ->with('client');

        if (! empty($data['from'])) $query->whereDate('issue_date', '>=', $data['from']);
        if (! empty($data['to'])) $query->whereDate('issue_date', '<=', $data['to']);

        $invoices = $query->orderBy('issue_date')->get();

        $xml = $this->buildXml($invoices, $settings);

        $filename = 'tally-export-'.now()->format('Y-m-d').'.xml';

        return response($xml, 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function buildXml($invoices, BillingSetting $settings): string
    {
        $esc = fn ($v) => htmlspecialchars((string) $v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $vouchers = '';

        foreach ($invoices as $inv) {
            $date = $inv->issue_date->format('Ymd');
            $party = $esc($inv->client->name);
            $total = number_format((float) $inv->total, 2, '.', '');
            $subtotal = number_format((float) $inv->subtotal, 2, '.', '');
            $gst = number_format((float) $inv->gst_total, 2, '.', '');

            $gstLine = $inv->gst_total > 0 ? "
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>Output GST</LEDGERNAME>
       <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
       <AMOUNT>{$gst}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>" : '';

            $vouchers .= "
    <TALLYMESSAGE xmlns:UDF=\"TallyUDF\">
     <VOUCHER VCHTYPE=\"Sales\" ACTION=\"Create\">
      <DATE>{$date}</DATE>
      <VOUCHERTYPENAME>Sales</VOUCHERTYPENAME>
      <VOUCHERNUMBER>{$esc($inv->invoice_number)}</VOUCHERNUMBER>
      <PARTYLEDGERNAME>{$party}</PARTYLEDGERNAME>
      <NARRATION>{$esc('Invoice '.$inv->invoice_number.' — '.$inv->client->name)}</NARRATION>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>{$party}</LEDGERNAME>
       <ISDEEMEDPOSITIVE>Yes</ISDEEMEDPOSITIVE>
       <AMOUNT>-{$total}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>
      <ALLLEDGERENTRIES.LIST>
       <LEDGERNAME>Sales Account</LEDGERNAME>
       <ISDEEMEDPOSITIVE>No</ISDEEMEDPOSITIVE>
       <AMOUNT>{$subtotal}</AMOUNT>
      </ALLLEDGERENTRIES.LIST>{$gstLine}
     </VOUCHER>
    </TALLYMESSAGE>";
        }

        $company = $esc($settings->company_name ?: 'My Company');

        return <<<XML
<ENVELOPE>
 <HEADER>
  <TALLYREQUEST>Import Data</TALLYREQUEST>
 </HEADER>
 <BODY>
  <IMPORTDATA>
   <REQUESTDESC>
    <REPORTNAME>Vouchers</REPORTNAME>
    <STATICVARIABLES>
     <SVCURRENTCOMPANY>{$company}</SVCURRENTCOMPANY>
    </STATICVARIABLES>
   </REQUESTDESC>
   <REQUESTDATA>{$vouchers}
   </REQUESTDATA>
  </IMPORTDATA>
 </BODY>
</ENVELOPE>
XML;
    }
}
