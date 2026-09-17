@extends('layouts.app')
@section('title', 'Leads CRM')
@section('content')
<div class="page-head">
    <div><h1>Leads CRM</h1><p>Capture leads and move them through your sales pipeline.</p></div>
    <button class="btn" onclick="document.getElementById('lead-modal').classList.add('open')">+ Add lead</button>
</div>

@if($clients->isEmpty())<div class="alert info">No clients yet. Run the seeder to create a demo client.</div>@endif

@php $labels = ['NEW'=>'New','CONTACTED'=>'Contacted','FOLLOW_UP'=>'Follow Up','APPOINTMENT'=>'Appointment','CONVERTED'=>'Converted','LOST'=>'Lost']; @endphp
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;align-items:start;">
    @foreach($stages as $stage)
        @php $items = $leads[$stage] ?? collect(); @endphp
        <div class="card" style="padding:12px;min-height:120px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;padding:0 2px;">
                <strong style="font-size:13px;">{{ $labels[$stage] }}</strong>
                <span style="font-size:11px;color:var(--muted);background:#f0eee6;border-radius:999px;padding:1px 8px;">{{ $items->count() }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                @forelse($items as $lead)
                    <div style="background:#fcfcfa;border:1px solid var(--line);border-radius:10px;padding:11px;">
                        <div style="font-weight:600;font-size:13.5px;margin-bottom:6px;">{{ $lead->name }}</div>
                        @if($lead->phone)<div style="font-size:11.5px;color:var(--muted);">✆ {{ $lead->phone }}</div>@endif
                        @if($lead->email)<div style="font-size:11.5px;color:var(--muted);margin-bottom:6px;">✉ {{ $lead->email }}</div>@endif
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                            <span class="badge gray">{{ ucfirst(strtolower(str_replace('_',' ',$lead->source))) }}</span>
                            <div style="display:flex;gap:3px;">
                                @php $idx = array_search($stage, $stages); @endphp
                                @if($idx > 0)
                                    <form method="POST" action="{{ route('leads.move', $lead) }}">@csrf<input type="hidden" name="stage" value="{{ $stages[$idx-1] }}"><button style="width:24px;height:24px;border-radius:6px;border:1px solid var(--line);background:#fff;cursor:pointer;">‹</button></form>
                                @endif
                                @if($idx < count($stages)-1)
                                    <form method="POST" action="{{ route('leads.move', $lead) }}">@csrf<input type="hidden" name="stage" value="{{ $stages[$idx+1] }}"><button style="width:24px;height:24px;border-radius:6px;border:1px solid var(--line);background:#fff;cursor:pointer;color:var(--teal-ink);">›</button></form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="font-size:11.5px;color:#b8b4a8;text-align:center;padding:10px 0;">—</div>
                @endforelse
            </div>
        </div>
    @endforeach
</div>

<div class="modal-bg" id="lead-modal">
    <div class="modal">
        <h2>Add lead</h2>
        <form method="POST" action="{{ route('leads.store') }}">
            @csrf
            <label><span class="lbl">Client</span><select name="client_id">@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></label>
            <label><span class="lbl">Name</span><input type="text" name="name" required></label>
            <label><span class="lbl">Phone</span><input type="text" name="phone"></label>
            <label><span class="lbl">Email</span><input type="email" name="email"></label>
            <label><span class="lbl">Source</span><select name="source">
                <option value="WEBSITE">Website</option><option value="FACEBOOK">Facebook</option><option value="INSTAGRAM">Instagram</option><option value="WHATSAPP">WhatsApp</option><option value="GOOGLE_FORM">Google Form</option>
            </select></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('lead-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add lead</button>
            </div>
        </form>
    </div>
</div>
@endsection
