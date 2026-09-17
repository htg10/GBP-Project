@extends('layouts.app')
@section('title', 'My Profile')
@section('content')
<form method="POST" action="{{ route('profile.update') }}" id="profile-form">
    @csrf
    <input type="hidden" name="avatar" id="avatar-input" value="{{ $user->avatar }}">

    {{-- Profile header --}}
    <div class="card" style="margin-bottom:18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
        <div style="display:flex;align-items:center;gap:18px;">
            <div style="position:relative;">
                <div class="avatar" id="avatar-preview" style="width:72px;height:72px;font-size:26px;border-radius:50%;">
                    @if($user->avatar)<img src="{{ $user->avatar }}" alt="">@else{{ strtoupper(substr($user->name,0,1)) }}@endif
                </div>
                <button type="button" onclick="document.getElementById('photo-file').click()" style="position:absolute;bottom:-2px;right:-2px;width:26px;height:26px;border-radius:50%;background:var(--teal);border:2px solid #fff;color:#fff;cursor:pointer;font-size:11px;display:grid;place-items:center;line-height:1;">+</button>
                <input type="file" id="photo-file" accept="image/*" style="display:none;" onchange="loadPhoto(event)">
            </div>
            <div>
                <div style="font-size:20px;font-weight:700;">{{ $user->name }}</div>
                <div style="font-size:13px;color:var(--muted);">{{ $user->email }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;">
            @if($user->isAdmin())
                <span class="badge teal" style="font-size:12px;padding:5px 14px;">Admin</span>
            @endif
        </div>
    </div>

    {{-- Profile info + Change password side by side --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        {{-- Profile Information --}}
        <div class="card">
            <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Profile Information</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">Update your name, email and phone number.</div>

            <label><span class="lbl">Full Name</span><input type="text" name="name" value="{{ $user->name }}" required></label>

            <label>
                <span class="lbl">Email Address</span>
                <div style="position:relative;">
                    <input type="email" value="{{ $user->email }}" disabled style="padding-right:80px;background:#f3f1ea;color:var(--muted);">
                </div>
            </label>
            <div style="font-size:11.5px;color:var(--muted);margin-top:4px;">Email cannot be changed here. Contact your admin if needed.</div>

            <label>
                <span class="lbl">Phone Number</span>
                <div style="display:flex;gap:8px;">
                    <div style="display:flex;align-items:center;gap:5px;padding:8px 12px;border:1px solid var(--line);border-radius:10px;background:#fcfcfa;white-space:nowrap;font-size:13px;color:var(--muted);flex-shrink:0;">
                        +91
                    </div>
                    <input type="text" name="phone" value="{{ $user->phone }}" placeholder="98765 43210" style="flex:1;">
                </div>
            </label>

            <button type="submit" class="btn" style="margin-top:18px;padding:11px 24px;">Save Changes</button>
        </div>

        {{-- Change Password --}}
        <div class="card">
            <div style="font-size:16px;font-weight:700;margin-bottom:4px;">Change Password</div>
            <div style="font-size:12px;color:var(--muted);margin-bottom:6px;">Use a long, random password to keep your account secure.</div>

            <label><span class="lbl">Current Password</span><input type="password" name="current_password" placeholder="Enter current password"></label>
            <label><span class="lbl">New Password</span><input type="password" name="new_password" placeholder="Enter new password"></label>
            <label><span class="lbl">Confirm New Password</span><input type="password" name="new_password_confirmation" placeholder="Confirm new password"></label>
            <div style="font-size:11.5px;color:var(--muted);margin-top:6px;">Leave blank to keep your current password.</div>

            <button type="submit" class="btn btn-ghost" style="margin-top:18px;padding:11px 24px;">Update Password</button>
        </div>
    </div>
</form>

@push('head')
<style>
    @media (max-width:700px){
        #profile-form > div:last-child{grid-template-columns:1fr !important;}
    }
</style>
@endpush

@push('scripts')
<script>
function loadPhoto(e){
    const file = e.target.files[0]; if(!file) return;
    if(file.size > 2*1024*1024){ alert('Please choose an image under 2 MB.'); return; }
    const reader = new FileReader();
    reader.onload = () => {
        document.getElementById('avatar-input').value = reader.result;
        document.getElementById('avatar-preview').innerHTML = '<img src="'+reader.result+'" alt="">';
    };
    reader.readAsDataURL(file);
}
</script>
@endpush
@endsection
