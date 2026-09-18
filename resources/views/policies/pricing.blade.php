@extends('policies.layout')
@section('title', 'Pricing Policy')
@section('content')
<h1>Pricing Policy</h1>
<p class="updated">Last updated: {{ date('d M Y') }}</p>

<h2>Overview</h2>
<p>ReviewFlow is a subscription-based SaaS platform for local business marketing and Google Business Profile management. All pricing is displayed in Indian Rupees (INR) and is inclusive of applicable GST (Goods and Services Tax) at 18%.</p>

<h2>Subscription Plans</h2>
<p>We offer three subscription tiers, billed monthly:</p>
<div class="card">
    <ul>
        <li><strong>Starter — ₹999/month</strong> — 1 GBP location, Reviews + AI reply, Photo posting, 100 AI credits/month</li>
        <li><strong>Growth — ₹2,999/month</strong> — Multiple GBP locations, Social posting, Lead CRM + WhatsApp, 500 AI credits/month</li>
        <li><strong>Agency — ₹9,999/month</strong> — Unlimited clients, Ads reporting, White label, Invoicing & Tally, 2,000 AI credits/month</li>
    </ul>
</div>
<p>Plan features, pricing, and credit allocations are subject to change. Existing subscribers will be notified at least 15 days before any price revision takes effect on their next billing cycle.</p>

<h2>AI Credit Packages</h2>
<p>In addition to monthly plan credits, users may purchase one-time credit top-up packages. Credits purchased via top-up packs are added to the existing balance and do not expire until the subscription is cancelled. All credit package prices are GST-inclusive.</p>

<h2>Payment Methods</h2>
<p>Payments are processed securely through <strong>Razorpay</strong>. We accept UPI, debit cards, credit cards, net banking, and popular wallets. ReviewFlow does not store any card or banking details on its servers.</p>

<h2>GST &amp; Invoicing</h2>
<p>All prices displayed on the platform include 18% GST. A detailed tax invoice with GST breakup is generated for every payment and can be downloaded from the Billing section of your dashboard.</p>

<h2>Free Trial</h2>
<p>New users may be offered a limited-period free trial at our discretion. Trial terms will be displayed at the time of sign-up. No charges will apply until you choose to subscribe to a paid plan.</p>

<h2>Contact</h2>
<p>For pricing-related questions, reach us at <a href="mailto:support@reviewflow.in">support@reviewflow.in</a>.</p>
@endsection
