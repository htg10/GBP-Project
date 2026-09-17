@extends('layouts.admin')
@section('title', 'Billing')
@section('content')
<div class="page-head"><h1>Billing</h1><p>Choose a plan and pay securely via Razorpay.</p></div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:22px;">
    <div style="background:var(--teal-soft);border:1px solid #cfe6e0;border-radius:12px;padding:14px 18px;font-size:14px;">
        Plan: <strong>{{ $sub->plan ?? 'None' }}</strong>
        @if($sub)<span style="margin-left:6px;font-size:12px;color:var(--teal-ink);">({{ $sub->status }})</span>@endif
    </div>
    <div style="background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;font-size:14px;">
        Credits: <strong style="color:var(--teal);">{{ number_format($creditBalance) }}</strong>
        @if($sub && $sub->monthly_credits > 0)
            <span style="font-size:12px;color:var(--muted);">/ {{ number_format($sub->monthly_credits) }} monthly</span>
        @endif
    </div>
</div>

@if(!$razorpayReady)
    <div class="alert error" style="margin-bottom:18px;">
        Razorpay keys missing. Add RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET to your .env to enable payments.
    </div>
@endif

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px;">
    @foreach($plans as $id=>$p)
        @php $active = ($sub->plan ?? null) === $id; @endphp
        <div class="card" style="{{ $active ? 'border:2px solid var(--teal);' : '' }}">
            <div style="font-size:16px;font-weight:700;">{{ $p['name'] }}</div>
            <div style="font-size:20px;font-weight:700;color:var(--teal);margin:6px 0 4px;">₹{{ number_format($p['price']) }}/mo</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:10px;">{{ number_format($planCredits[$id] ?? 0) }} AI credits/mo</div>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;margin-bottom:16px;">
                @foreach($p['features'] as $f)<li style="font-size:13px;color:#3a4a45;">✓ {{ $f }}</li>@endforeach
            </ul>
            @if($active)
                <button class="btn" style="width:100%;background:transparent;border:1px solid var(--line);color:var(--muted);justify-content:center;" disabled>Current plan</button>
            @else
                <button class="btn pay-btn" style="width:100%;justify-content:center;" data-plan="{{ $id }}" {{ $razorpayReady ? '' : 'disabled' }}>Subscribe →</button>
            @endif
        </div>
    @endforeach
</div>

{{-- Admin credit top-up --}}
<div class="card" style="margin-top:22px;max-width:420px;">
    <strong style="font-size:14px;">Manual credit top-up</strong>
    <form method="POST" action="{{ route('admin.billing.topup') }}" style="display:flex;gap:10px;align-items:flex-end;margin-top:10px;">
        @csrf
        <input type="hidden" name="agency_id" value="{{ auth()->user()->agency_id }}">
        <label style="flex:1;"><span class="lbl">Credits to add</span><input type="number" name="credits" min="1" max="10000" value="100" required></label>
        <button type="submit" class="btn" style="margin-bottom:1px;">+ Add credits</button>
    </form>
</div>

@if($payments->count())
    <div style="margin-top:26px;">
        <strong style="font-size:14px;">Payment history</strong>
        <div class="card" style="padding:0;overflow:hidden;margin-top:10px;">
            <table>
                <thead><tr><th>Date</th><th>Plan</th><th style="text-align:right;">Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($payments as $pay)
                        <tr>
                            <td>{{ $pay->created_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $pay->plan }}</td>
                            <td style="text-align:right;">₹{{ number_format($pay->amount / 100) }}</td>
                            <td><span class="badge {{ $pay->status==='PAID'?'teal':'dark' }}">{{ $pay->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;

document.querySelectorAll('.pay-btn').forEach(btn => {
    btn.addEventListener('click', async () => {
        const plan = btn.dataset.plan;
        btn.textContent = 'Please wait…'; btn.disabled = true;

        try {
            // Step 1: create order on our server
            const res = await fetch("{{ route('admin.billing.checkout') }}", {
                method: 'POST',
                headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                body: JSON.stringify({plan})
            });
            const data = await res.json();
            if (data.error) { alert(data.error); btn.textContent = 'Subscribe →'; btn.disabled = false; return; }

            // Step 2: open Razorpay checkout
            const options = {
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: 'ReviewFlow',
                description: data.plan_name + ' plan',
                order_id: data.order_id,
                theme: {color: '#0f6b5c'},
                handler: async function (response) {
                    // Step 3: verify on our server
                    const vr = await fetch("{{ route('admin.billing.verify') }}", {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                            plan: plan
                        })
                    });
                    const vd = await vr.json();
                    if (vd.success) { alert('Payment successful! Plan activated.'); location.reload(); }
                    else { alert(vd.error || 'Verification failed'); }
                },
                modal: {
                    ondismiss: function () { btn.textContent = 'Subscribe →'; btn.disabled = false; }
                }
            };
            const rzp = new Razorpay(options);
            rzp.open();
        } catch (e) {
            alert('Something went wrong. Check console.');
            btn.textContent = 'Subscribe →'; btn.disabled = false;
        }
    });
});
</script>
@endpush
@endsection
