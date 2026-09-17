@extends('layouts.app')
@section('title', 'Billing Settings')
@section('content')
<div class="page-head">
    <div><h1>Billing Settings</h1><p>Your business details appear on every invoice you send.</p></div>
</div>

<form method="POST" action="{{ route('billing-settings.update') }}" id="settings-form" style="max-width:820px;">
    @csrf
    <input type="hidden" name="logo" id="logo-input" value="{{ $settings->logo }}">

    {{-- Your Business --}}
    <div class="card" style="margin-bottom:18px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Your Business</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
            <label><span class="lbl">Business Type</span>
                <select name="business_type">
                    @php $types = ['Service Business','Product Business','Freelancer','Agency','Restaurant','Retail','Healthcare','Other']; @endphp
                    @foreach($types as $t)
                        <option value="{{ $t }}" {{ $settings->business_type === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="lbl">Business Name *</span><input type="text" name="company_name" value="{{ $settings->company_name }}" required></label>
            <label><span class="lbl">Phone</span><input type="text" name="phone" value="{{ $settings->phone }}" placeholder="+91 98765 43210"></label>
            <label><span class="lbl">Email</span><input type="email" name="email" value="{{ $settings->email }}" placeholder="billing@company.com"></label>
            <label style="grid-column:1;"><span class="lbl">Address</span><textarea name="address" rows="2" placeholder="Street, City, PIN">{{ $settings->address }}</textarea></label>
            <label><span class="lbl">GSTIN</span><input type="text" name="gstin" value="{{ $settings->gstin }}" placeholder="22AAAAA0000A1Z5"></label>
            <label><span class="lbl">State (Place of Supply)</span>
                <select name="state">
                    <option value="">-- Select state --</option>
                    @php $states = ['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal']; @endphp
                    @foreach($states as $s)
                        <option value="{{ $s }}" {{ $settings->state === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="lbl">Logo</span>
                <div style="display:flex;align-items:center;gap:12px;margin-top:2px;">
                    @if($settings->logo)
                        <img src="{{ $settings->logo }}" id="logo-preview" style="width:48px;height:48px;border-radius:10px;object-fit:cover;border:1px solid var(--line);">
                    @else
                        <div id="logo-preview" style="width:48px;height:48px;border-radius:10px;background:var(--teal-soft);display:grid;place-items:center;color:var(--teal-ink);font-weight:700;font-size:18px;border:1px solid var(--line);">{{ strtoupper(substr($settings->company_name ?? 'R', 0, 1)) }}</div>
                    @endif
                    <div>
                        <button type="button" class="btn btn-ghost" style="padding:7px 14px;font-size:12px;" onclick="document.getElementById('logo-file').click()">Choose File</button>
                        <input type="file" id="logo-file" accept="image/*" style="display:none;" onchange="loadLogo(event)">
                    </div>
                </div>
            </label>
        </div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:10px;">Your place of business. Compared with the customer's state to decide CGST+SGST or IGST.</div>
    </div>

    {{-- Invoice Defaults --}}
    <div class="card" style="margin-bottom:18px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Invoice Defaults</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
            <label><span class="lbl">Invoice Prefix</span><input type="text" name="invoice_prefix" value="{{ $settings->invoice_prefix }}" required></label>
            <div style="align-self:end;padding-bottom:2px;margin-top:13px;">
                <span class="lbl" style="margin-top:0;">Next number</span>
                <div style="font-size:13px;padding:10px 12px;background:#f3f1ea;border-radius:10px;color:var(--muted);">{{ $settings->invoice_prefix }}-{{ str_pad($settings->next_invoice_number, 6, '0', STR_PAD_LEFT) }}</div>
            </div>
            <label><span class="lbl">Default GST</span>
                <div style="position:relative;">
                    <input type="number" name="default_gst" value="{{ $settings->default_gst }}" min="0" max="100" step="0.01" style="padding-right:32px;">
                    <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px;">%</span>
                </div>
            </label>
            <label><span class="lbl">Currency</span>
                <select name="currency">
                    <option value="INR" {{ $settings->currency === 'INR' ? 'selected' : '' }}>INR Indian Rupee</option>
                    <option value="USD" {{ $settings->currency === 'USD' ? 'selected' : '' }}>USD US Dollar</option>
                    <option value="GBP" {{ $settings->currency === 'GBP' ? 'selected' : '' }}>GBP British Pound</option>
                    <option value="EUR" {{ $settings->currency === 'EUR' ? 'selected' : '' }}>EUR Euro</option>
                    <option value="AED" {{ $settings->currency === 'AED' ? 'selected' : '' }}>AED UAE Dirham</option>
                </select>
            </label>
        </div>
        <label style="display:flex;align-items:center;gap:10px;margin-top:16px;cursor:pointer;">
            <input type="checkbox" name="round_total" value="1" {{ $settings->round_total ? 'checked' : '' }}
                style="width:auto;accent-color:var(--teal);width:18px;height:18px;">
            <div>
                <div style="font-size:13.5px;font-weight:600;">Round the total to the nearest rupee</div>
                <div style="font-size:11.5px;color:var(--muted);">Counter bills usually round. Leave off for invoices raised against a purchase order.</div>
            </div>
        </label>
    </div>

    {{-- Bank Details --}}
    <div class="card" style="margin-bottom:18px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Bank Details</div>
        <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">Shown on invoices so customers know where to pay.</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
            <label><span class="lbl">Bank Name</span><input type="text" name="bank_name" value="{{ $settings->bank_name }}"></label>
            <label><span class="lbl">Account Number</span><input type="text" name="bank_account" value="{{ $settings->bank_account }}"></label>
            <label><span class="lbl">IFSC Code</span><input type="text" name="ifsc" value="{{ $settings->ifsc }}"></label>
        </div>
    </div>

    {{-- Tally / Accounting --}}
    <div class="card" style="margin-bottom:18px;">
        <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Tally / Accounting</div>
        <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">Used by <a href="{{ route('tally-export') }}" style="color:var(--teal);text-decoration:underline;">Tally Export</a>. The defaults suit most companies — only change the ledger names if your Tally uses different ones.</div>

        @if(!$settings->state)
            <div style="background:var(--rose-soft);border-radius:10px;padding:10px 14px;margin:12px 0;font-size:13px;color:var(--rose);">
                Place of supply: <strong>Not set</strong>. <a href="#" onclick="document.querySelector('[name=state]').focus();return false;" style="color:var(--rose);text-decoration:underline;">Set your state</a> — the Tally export cannot split GST without it.
            </div>
        @endif

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 16px;">
            <label><span class="lbl">Tally Company Name</span><input type="text" name="tally_company_name" value="{{ $settings->tally_company_name }}" placeholder="Exactly as it appears in Tally"></label>
            <label><span class="lbl">Customer Ledger Group</span><input type="text" name="customer_ledger_group" value="{{ $settings->customer_ledger_group }}" placeholder="Sundry Debtors"></label>
        </div>
        <div style="font-size:11.5px;color:var(--muted);margin-top:8px;">Optional. Leave blank to import into whichever company is open in Tally. New customer ledgers are created under this group.</div>
    </div>

    <button type="submit" class="btn" style="padding:12px 28px;">Save Settings</button>
</form>

@push('scripts')
<script>
function loadLogo(e){
    const file = e.target.files[0]; if(!file) return;
    if(file.size > 2*1024*1024){ alert('Logo must be under 2 MB.'); return; }
    const reader = new FileReader();
    reader.onload = () => {
        document.getElementById('logo-input').value = reader.result;
        const preview = document.getElementById('logo-preview');
        if (preview.tagName === 'IMG') {
            preview.src = reader.result;
        } else {
            const img = document.createElement('img');
            img.src = reader.result;
            img.id = 'logo-preview';
            img.style.cssText = 'width:48px;height:48px;border-radius:10px;object-fit:cover;border:1px solid var(--line);';
            preview.replaceWith(img);
        }
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
@endsection
