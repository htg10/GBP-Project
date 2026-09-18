@extends('layouts.app')
@section('title', 'Plans & Upgrade')
@section('content')

<div class="page-head">
    <div>
        <h1>Plans &amp; Upgrade</h1>
        <p>Choose the plan that fits your business. All prices are GST-inclusive.</p>
    </div>
    <a href="{{ route('credits') }}" class="btn btn-ghost">⚡ View Credits</a>
</div>

@php
    $isTrialing = ($sub->status ?? '') === 'TRIALING';
    $current = $isTrialing ? null : ($sub->plan ?? null);
    $accent = ['#22c55e', '#4c6fff', '#8b5cf6', '#f59e0b', '#ec4899'];
@endphp

@if($isTrialing)
<div class="alert info" style="margin-bottom:18px;">
    <strong>No active plan yet.</strong> Choose a plan below to unlock all features and get your monthly AI credits.
</div>
@endif

@if($plans->isEmpty())
    <div class="card"><div class="empty">No plans available yet. Ask your Super Admin to create one.</div></div>
@else
<div class="plan-grid">
    @foreach($plans as $i => $plan)
        @php
            $isCurrent = $current === $plan->code;
            $isPopular = $loop->iteration === 2 && $plans->count() > 2;
            $clr = $accent[$i % count($accent)];
        @endphp
        <div class="plan-card {{ $isCurrent ? 'current' : '' }}" style="--pc:{{ $clr }};">
            @if($isPopular)<div class="plan-tag">Most Popular</div>@endif
            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-price">₹{{ number_format($plan->price) }}<span>/mo</span></div>
            <div class="plan-gst">Incl. {{ $plan->gst_rate }}% GST (₹{{ number_format($plan->gstAmount(),2) }}) · Base ₹{{ number_format($plan->baseAmount(),2) }}</div>
            <div class="plan-credits">{{ number_format($plan->credits) }} AI credits / month</div>
            <ul class="plan-feats">
                @foreach(($plan->features ?? []) as $feat)
                    <li><span class="pf-check">✓</span> {{ $feat }}</li>
                @endforeach
            </ul>
            @php $label = 'Choose'; @endphp
            @if($isCurrent)
                <button class="btn" style="width:100%;justify-content:center;background:#eef1f8;color:var(--muted);cursor:default;" disabled>Current Plan</button>
            @elseif(!$canManage)
                <button class="btn btn-ghost" style="width:100%;justify-content:center;" disabled title="Only the owner can change the plan">Owner only</button>
            @elseif($razorpayReady)
                <button class="btn pay-btn" style="width:100%;justify-content:center;background:var(--pc);" data-plan="{{ $plan->code }}" data-name="{{ $plan->name }}">Choose — Pay ₹{{ number_format($plan->price) }}</button>
            @else
                <form method="POST" action="{{ route('plans.upgrade') }}" onsubmit="return confirm('Switch to the {{ $plan->name }} plan? {{ number_format($plan->credits) }} credits will be allocated.')">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan->code }}">
                    <button type="submit" class="btn" style="width:100%;justify-content:center;background:var(--pc);">Choose {{ $plan->name }}</button>
                </form>
            @endif
        </div>
    @endforeach
</div>
@endif

<div class="card" style="margin-top:18px;display:flex;align-items:center;gap:14px;">
    <div style="width:42px;height:42px;border-radius:12px;background:var(--teal-soft);color:var(--teal-ink);display:grid;place-items:center;font-size:18px;flex-shrink:0;">⚡</div>
    <div style="flex:1;">
        <strong style="font-size:14px;">Current balance: {{ number_format($creditBalance) }} credits</strong>
        <div style="font-size:12.5px;color:var(--muted);margin-top:2px;">
            @if($razorpayReady)
                🔒 Secure payments powered by Razorpay (test mode). GST is included in every price.
            @else
                Demo mode — upgrades activate instantly. Add Razorpay keys in <code>.env</code> to take live payments.
            @endif
        </div>
    </div>
</div>

@if($razorpayReady)
@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
document.querySelectorAll('.pay-btn').forEach(btn => {
    const original = btn.textContent;
    btn.addEventListener('click', async () => {
        const plan = btn.dataset.plan;
        btn.textContent = 'Please wait…'; btn.disabled = true;
        try {
            const res = await fetch("{{ route('plans.checkout') }}", {
                method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                body: JSON.stringify({plan})
            });
            const data = await res.json();
            if (data.error) { alert(data.error); btn.textContent = original; btn.disabled = false; return; }
            const options = {
                key: data.key, amount: data.amount, currency: data.currency,
                name: 'ReviewFlow', description: data.plan_name + ' plan', order_id: data.order_id,
                theme: {color: '#4c6fff'},
                handler: async function (response) {
                    const vr = await fetch("{{ route('plans.verify') }}", {
                        method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature, plan: plan
                        })
                    });
                    const vd = await vr.json();
                    if (vd.success) { alert('Payment successful! Your ' + data.plan_name + ' plan is now active.'); location.reload(); }
                    else { alert(vd.error || 'Verification failed'); btn.textContent = original; btn.disabled = false; }
                },
                modal: { ondismiss: function () { btn.textContent = original; btn.disabled = false; } }
            };
            new Razorpay(options).open();
        } catch (e) { alert('Something went wrong starting checkout.'); btn.textContent = original; btn.disabled = false; }
    });
});
</script>
@endpush
@endif

@push('head')
<style>
    .plan-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;}
    .plan-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:26px 22px;box-shadow:var(--shadow);position:relative;display:flex;flex-direction:column;transition:transform .15s,box-shadow .15s;}
    .plan-card:hover{transform:translateY(-3px);box-shadow:0 14px 34px rgba(76,111,255,.14);}
    .plan-card.current{border:1.5px solid var(--pc);}
    .plan-tag{position:absolute;top:-11px;left:50%;transform:translateX(-50%);background:var(--pc);color:#fff;font-size:11px;font-weight:700;padding:4px 14px;border-radius:999px;white-space:nowrap;}
    .plan-name{font-size:15px;font-weight:700;color:var(--pc);text-transform:uppercase;letter-spacing:.04em;}
    .plan-price{font-size:34px;font-weight:800;margin-top:8px;}
    .plan-price span{font-size:15px;font-weight:500;color:var(--muted);}
    .plan-gst{font-size:11.5px;color:var(--muted);margin-top:3px;}
    .plan-credits{font-size:13px;color:var(--ink);font-weight:600;margin:8px 0 16px;}
    .plan-feats{list-style:none;flex:1;margin-bottom:20px;}
    .plan-feats li{font-size:13.5px;padding:7px 0;border-top:1px solid var(--line);display:flex;align-items:center;gap:9px;}
    .plan-feats li:first-child{border-top:none;}
    .pf-check{width:18px;height:18px;border-radius:50%;background:var(--pc);color:#fff;display:grid;place-items:center;font-size:10px;flex-shrink:0;}
</style>
@endpush
@endsection
