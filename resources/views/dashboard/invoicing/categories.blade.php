@extends('layouts.app')
@section('title', 'Categories')
@section('content')
<div class="page-head">
    <div><h1>Categories</h1><p>How your services are grouped in the invoice picker.</p></div>
    <button class="btn" onclick="document.getElementById('cat-modal').classList.add('open')">+ Add Category</button>
</div>

@if($categories->isEmpty())
    <div class="card"><div class="empty">No categories yet. Add one here, or type a category straight into a service.</div></div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
        @foreach($categories as $cat)
            <div class="card" style="display:flex;justify-content:space-between;align-items:center;padding:14px 16px;">
                <div><strong style="font-size:14px;">{{ $cat->name }}</strong><div style="font-size:11.5px;color:var(--muted);margin-top:2px;">{{ $cat->services_count }} service(s)</div></div>
                <form method="POST" action="{{ route('service-categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category?')">
                    @csrf @method('DELETE')
                    <button class="icon-btn" style="background:none;border:none;cursor:pointer;color:var(--rose);">🗑</button>
                </form>
            </div>
        @endforeach
    </div>
@endif

<div class="modal-bg" id="cat-modal">
    <div class="modal">
        <h2>Add category</h2>
        <form method="POST" action="{{ route('service-categories.store') }}">
            @csrf
            <label><span class="lbl">Name</span><input type="text" name="name" required placeholder="e.g. SEO, Ads, Design"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('cat-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Add category</button>
            </div>
        </form>
    </div>
</div>
@endsection
