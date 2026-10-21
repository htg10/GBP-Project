@extends('policies.layout')
@section('title', 'Delete Your Account')
@section('content')
<h1>Delete Your Account — Eydia (ReviewFlow)</h1>
<p class="updated">Last updated: {{ date('d M Y') }}</p>

<h2>How to request account deletion</h2>
<p>To delete your Eydia / ReviewFlow account and associated data, send an email to <strong>eydia@gmail.com</strong> from the email address registered on your account, with the subject line <strong>"Delete my account"</strong>.</p>
<p>Please include your registered email address and, if you know it, your business/agency name, so we can locate your account.</p>
<p>We will confirm your request and complete the deletion within 7 business days.</p>

<h2>What gets deleted</h2>
<ul>
    <li>Your login credentials and profile information (name, email, phone)</li>
    <li>Business/client profiles, locations, and connected Google/Meta integration tokens</li>
    <li>Reviews, leads, social posts, Google posts and photos stored for your account</li>
    <li>AI chat history and AI-generated content associated with your account</li>
</ul>

<h2>What may be retained</h2>
<p>Some information may be retained after an account deletion request where we are legally required to do so, specifically:</p>
<ul>
    <li>Invoices, billing records, and payment history — retained as required by applicable Indian tax and accounting laws</li>
    <li>Records needed to resolve disputes, enforce our agreements, or comply with legal obligations</li>
</ul>
<p>Retained records are kept only for the period required by law and are not used for any other purpose.</p>

<h2>Partial deletion</h2>
<p>If you'd like only specific data deleted (for example, a single client's data) rather than your whole account, mention this in your email and we will action it without deleting your account.</p>
@endsection
