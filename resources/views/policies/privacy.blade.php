@extends('policies.layout')
@section('title', 'Privacy Policy')
@section('content')
<h1>Privacy Policy</h1>
<p class="updated">Last updated: {{ date('d M Y') }}</p>

<h2>1. Introduction</h2>
<p>ReviewFlow ("we", "us", "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform.</p>

<h2>2. Information We Collect</h2>
<p><strong>Information you provide:</strong></p>
<ul>
    <li>Name, email address, and password during registration</li>
    <li>Business name, address, and industry details for client profiles</li>
    <li>Content you create (posts, review replies, leads, invoices)</li>
    <li>Payment information (processed by Razorpay — we do not store card details)</li>
</ul>

<p><strong>Information from third-party integrations:</strong></p>
<ul>
    <li>Google Business Profile data (reviews, locations, posts, photos) via Google OAuth</li>
    <li>Meta/Facebook page data and Instagram accounts via Meta OAuth</li>
    <li>Google account profile information (name, email, profile picture) for Google Sign-In</li>
</ul>

<p><strong>Automatically collected information:</strong></p>
<ul>
    <li>Browser type, IP address, device information</li>
    <li>Pages visited, time spent, and usage patterns within the Platform</li>
    <li>Cookies and similar tracking technologies</li>
</ul>

<h2>3. How We Use Your Information</h2>
<ul>
    <li>To provide and maintain the Platform's services</li>
    <li>To manage your account and subscription</li>
    <li>To process payments and generate invoices</li>
    <li>To sync and display your Google Business Profile and social media data</li>
    <li>To generate AI-powered content (review replies, captions, media) using Google Gemini</li>
    <li>To send service-related emails (verification, billing receipts, important updates)</li>
    <li>To improve and optimize the Platform</li>
</ul>

<h2>4. Data Sharing &amp; Disclosure</h2>
<p>We do not sell, trade, or rent your personal information. We may share data with:</p>
<ul>
    <li><strong>Service providers:</strong> Razorpay (payments), Google Cloud (AI and APIs), SMTP providers (email delivery)</li>
    <li><strong>Google &amp; Meta APIs:</strong> To publish content, sync reviews, and manage your business profiles on your behalf</li>
    <li><strong>Legal requirements:</strong> When required by law, court order, or government request</li>
</ul>

<h2>5. Data Security</h2>
<p>We implement industry-standard security measures including:</p>
<ul>
    <li>HTTPS encryption for all data in transit</li>
    <li>Hashed passwords (bcrypt) — we never store passwords in plain text</li>
    <li>OAuth tokens stored securely and encrypted at rest</li>
    <li>Role-based access control ensuring users only see their own client data</li>
</ul>
<p>While we strive to protect your data, no method of transmission or storage is 100% secure. You use the Platform at your own risk.</p>

<h2>6. Data Retention</h2>
<p>We retain your data for as long as your account is active. Upon account deletion or subscription cancellation:</p>
<ul>
    <li>Your personal data will be deleted within 30 days</li>
    <li>Anonymized usage data may be retained for analytics purposes</li>
    <li>Payment records are retained as required by Indian tax law</li>
</ul>

<h2>7. Cookies</h2>
<p>ReviewFlow uses essential cookies to maintain your login session and CSRF protection. We do not use third-party advertising or tracking cookies. You can disable cookies in your browser settings, but this may affect Platform functionality.</p>

<h2>8. Your Rights</h2>
<p>You have the right to:</p>
<ul>
    <li><strong>Access</strong> the personal data we hold about you</li>
    <li><strong>Correct</strong> inaccurate or incomplete data via your Profile settings</li>
    <li><strong>Delete</strong> your account and associated data by contacting support</li>
    <li><strong>Disconnect</strong> third-party integrations (Google, Meta) at any time from your dashboard</li>
    <li><strong>Export</strong> your data upon request</li>
</ul>

<h2>9. Children's Privacy</h2>
<p>ReviewFlow is not intended for use by individuals under the age of 18. We do not knowingly collect personal data from minors. If we learn that we have collected data from a minor, we will delete it promptly.</p>

<h2>10. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. We will notify you of material changes via email or in-app notification. Your continued use of the Platform after changes take effect constitutes acceptance.</p>

<h2>11. Contact</h2>
<p>For privacy-related inquiries or to exercise your data rights, contact us at <a href="mailto:support@reviewflow.in">support@reviewflow.in</a>.</p>
@endsection
