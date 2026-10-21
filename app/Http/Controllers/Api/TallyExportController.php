<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BillingSetting;
use App\Models\Invoice;
use Illuminate\Http\Request;

/**
 * API version of the Tally export. Builds the same Sales-voucher XML as the
 * web Tally Export page, returned as a file for the mobile app to share.
 */
class TallyExportController extends Controller
{
    public function summary(Request $request)
    {
        $agencyId = $request->user()->agency_id;
        $settings = BillingSetting::firstOrCreate(['agency_id' => $agencyId]);

        return response()->json([
            'invoice_count' => Invoice::where('agency_id', $agencyId)->whereIn('status', ['SENT', 'PAID', 'OVERDUE'])->count(),
            'company_name' => $settings->company_name,
            'tally_company_name' => $settings->tally_company_name,
            'state' => $settings->state,
        ]);
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
            ->where('status', '!=', 'DRAFT')
            ->with('client');

        if (! empty($data['from'])) $query->whereDate('issue_date', '>=', $data['from']);
        if (! empty($data['to'])) $query->whereDate('issue_date', '<=', $data['to']);

        $invoices = $query->orderBy('issue_date')->get();

        $xml = $this->buildXml($invoices, $settings);
        $filename = 'tally-export-' . now()->format('Y-m-d') . '.xml';

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

        $company = $esc($settings->tally_company_name ?: ($settings->company_name ?: 'My Company'));

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
