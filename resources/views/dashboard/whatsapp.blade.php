@extends('layouts.app')
@section('title', 'WhatsApp')
@section('content')
<div class="page-head">
    <div><h1>WhatsApp</h1><p>Send template messages and track your conversation history.</p></div>
    <button class="btn" onclick="document.getElementById('wa-modal').classList.add('open')">Send message</button>
</div>

@if($messages->isEmpty())
    <div class="card"><div class="empty">No messages yet. Send your first template message.</div></div>
@else
    <div class="card" style="padding:0;overflow:hidden;">
        @foreach($messages as $i => $m)
            <div style="padding:14px 18px;{{ $i>0?'border-top:1px solid var(--line);':'' }}display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-weight:600;font-size:13.5px;">✆ {{ $m->to_phone }}</div>
                    <div style="font-size:12.5px;color:var(--muted);margin-top:3px;">{{ $m->template ? ucwords(str_replace('_',' ',$m->template)) : $m->body }}</div>
                </div>
                <div style="text-align:right;">
                    <span class="badge {{ $m->status==='queued'?'amber':'teal' }}">{{ $m->status }}</span>
                    <div style="font-size:11px;color:var(--muted);margin-top:5px;">{{ $m->created_at->format('d M, h:i A') }}</div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<div class="modal-bg" id="wa-modal">
    <div class="modal">
        <h2>Send WhatsApp message</h2>
        <form method="POST" action="{{ route('whatsapp.send') }}">
            @csrf
            <label><span class="lbl">To phone</span><input type="text" name="to_phone" placeholder="+91 98765 43210" required></label>
            <label><span class="lbl">Template</span><select name="template">
                <option value="review_request">Review request</option><option value="appointment_reminder">Appointment reminder</option><option value="lead_followup">Lead follow-up</option><option value="promo_broadcast">Promo broadcast</option>
            </select></label>
            <label><span class="lbl">Note (optional)</span><input type="text" name="body"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('wa-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Send</button>
            </div>
        </form>
    </div>
</div>
@endsection
