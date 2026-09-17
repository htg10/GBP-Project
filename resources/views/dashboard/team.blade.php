@extends('layouts.app')
@section('title', 'My Team')
@section('content')
<div class="page-head">
    <div>
        <h1>My Team</h1>
        <p>Add Staff and Marketing Managers for your business. They only see your data.</p>
    </div>
    <button class="btn" onclick="document.getElementById('team-modal').classList.add('open')">+ Add member</button>
</div>

@php $labels = ['STAFF'=>'Staff','MARKETING_MANAGER'=>'Marketing Manager']; @endphp

<div class="card" style="padding:0;overflow:hidden;">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th style="text-align:right;">Actions</th></tr></thead>
        <tbody>
            @forelse($team as $u)
                <tr>
                    <td style="display:flex;align-items:center;gap:10px;">
                        <div class="avatar" style="width:32px;height:32px;font-size:12px;">{{ strtoupper(substr($u->name,0,1)) }}</div>
                        <strong>{{ $u->name }}</strong>
                    </td>
                    <td style="color:var(--muted);">{{ $u->email }}</td>
                    <td><span class="badge teal">{{ $labels[$u->role] ?? ucwords(strtolower(str_replace('_',' ',$u->role))) }}</span></td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('team.destroy', $u) }}" style="display:inline;" onsubmit="return confirm('Remove this team member?')">
                            @csrf @method('DELETE')<button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);font-size:14px;">🗑 Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty" style="padding:30px;">No team members yet. Add your first Staff or Marketing Manager.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="modal-bg" id="team-modal">
    <div class="modal">
        <h2>Add team member</h2>
        <p style="font-size:13px;color:var(--muted);margin-bottom:8px;">This person will be created under your business only.</p>
        <form method="POST" action="{{ route('team.store') }}">
            @csrf
            <label><span class="lbl">Name</span><input type="text" name="name" required></label>
            <label><span class="lbl">Email</span><input type="email" name="email" required></label>
            <label><span class="lbl">Temporary password</span><input type="password" name="password" minlength="8" required></label>
            <label><span class="lbl">Role</span><select name="role">
                <option value="STAFF">Staff</option>
                <option value="MARKETING_MANAGER">Marketing Manager</option>
            </select></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('team-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add member</button>
            </div>
        </form>
    </div>
</div>
@endsection
