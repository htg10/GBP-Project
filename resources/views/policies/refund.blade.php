@extends('policies.layout')
@section('title', 'Cancellation & Refund Policy')
@section('content')
<h1>Cancellation &amp; Refund Policy</h1>
<p class="updated">Last updated: {{ date('d M Y') }}</p>

<h2>1. Subscription Cancellation</h2>
<p>You may cancel your ReviewFlow subscription at any time. Upon cancellation:</p>
<ul>
    <li>Your access to paid features will continue until the <strong>end of the current billing period</strong></li>
    <li>No further charges will be applied after cancellation</li>
    <li>Your data will be retained for 30 days after the billing period ends, after which it may be permanently deleted</li>
    <li>Unused AI credits from your monthly plan allocation will expire at the end of the billing period</li>
</ul>

<h2>2. Refund Policy for Subscriptions</h2>
<div class="card">
    <p><strong>7-Day Refund Window:</strong> If you are unsatisfied with ReviewFlow, you may request a full refund within 7 days of your first subscription payment. This applies only to the first payment on a new account.</p>
</div>
<p>After the 7-day window:</p>
<ul>
    <li>Subscription fees are <strong>non-refundable</strong> for the current billing period</li>
    <li>Partial-month refunds are not provided</li>
    <li>Downgrading to a lower plan takes effect from the next billing cycle — the difference is not refunded for the current month</li>
</ul>

<h2>3. Refund Policy for Credit Packages</h2>
<ul>
    <li><strong>Unused credits:</strong> If you purchased a credit top-up package and have not used any credits from that purchase, you may request a refund within 7 days of purchase</li>
    <li><strong>Partially used credits:</strong> Once any credits from a purchased package have been consumed, the package is non-refundable</li>
    <li>Monthly plan credits (included in your subscription) are not independently refundable</li>
</ul>

<h2>4. How to Request a Refund</h2>
<p>To request a refund, email us at <a href="mailto:support@reviewflow.in">support@reviewflow.in</a> with:</p>
<ul>
    <li>Your registered email address</li>
    <li>Payment date and amount</li>
    <li>Reason for the refund request</li>
</ul>
<p>We will review and respond to your request within <strong>3 business days</strong>.</p>

<h2>5. Refund Processing</h2>
<ul>
    <li>Approved refunds will be processed via the original payment method through Razorpay</li>
    <li>Refunds typically take <strong>5–7 business days</strong> to reflect in your account, depending on your bank</li>
    <li>GST paid on refunded transactions will also be refunded</li>
</ul>

<h2>6. Exceptions</h2>
<p>Refunds will <strong>not</strong> be granted in the following cases:</p>
<ul>
    <li>Account suspension due to violation of our <a href="{{ route('policy.terms') }}">Terms and Conditions</a></li>
    <li>Failure to use the Platform does not constitute grounds for a refund</li>
    <li>Issues caused by third-party services (Google API outages, Meta policy changes, Razorpay downtime)</li>
    <li>Requests made after the eligible refund window has passed</li>
</ul>

<h2>7. Plan Upgrades</h2>
<p>When you upgrade your plan mid-cycle, the price difference is charged immediately (pro-rated). Upgrades are non-refundable once activated. If you wish to downgrade, the change takes effect at the start of the next billing cycle.</p>

<h2>8. Contact</h2>
<p>For cancellation or refund queries, contact us at <a href="mailto:support@reviewflow.in">support@reviewflow.in</a>.</p>
@endsection
