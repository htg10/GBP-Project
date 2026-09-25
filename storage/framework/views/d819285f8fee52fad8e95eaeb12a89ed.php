<?php $__env->startSection('title', 'Terms and Conditions'); ?>
<?php $__env->startSection('content'); ?>
<h1>Terms and Conditions</h1>
<p class="updated">Last updated: <?php echo e(date('d M Y')); ?></p>

<h2>1. Acceptance of Terms</h2>
<p>By accessing or using ReviewFlow ("the Platform"), you agree to be bound by these Terms and Conditions. If you do not agree, you must not use the Platform. These terms apply to all users including Super Admins, Client Owners, Staff, and Marketing Managers.</p>

<h2>2. Description of Service</h2>
<p>ReviewFlow is a SaaS platform that provides AI-powered tools for local business marketing, including but not limited to: Google Business Profile management, review monitoring and AI-generated replies, social media posting, lead CRM, WhatsApp integration, AI media generation, invoicing, keyword research, and competitor analysis.</p>

<h2>3. Account Registration</h2>
<ul>
    <li>You must provide accurate, current, and complete information during registration.</li>
    <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
    <li>You must be at least 18 years old or have legal authority to enter into these terms.</li>
    <li>One person or entity may not maintain more than one Super Admin account per agency.</li>
</ul>

<h2>4. User Roles &amp; Responsibilities</h2>
<p>The Platform supports multiple user roles (Super Admin, Client Owner, Staff, Marketing Manager). Each role has specific access levels. The Super Admin is responsible for managing all users and ensuring compliance with these terms across their agency.</p>

<h2>5. Acceptable Use</h2>
<p>You agree not to:</p>
<ul>
    <li>Use the Platform for any unlawful purpose or in violation of any applicable laws</li>
    <li>Post or publish misleading, defamatory, or spam content through the Platform</li>
    <li>Attempt to access other users' accounts or data without authorization</li>
    <li>Reverse engineer, decompile, or attempt to extract the source code of the Platform</li>
    <li>Use automated bots or scrapers to interact with the Platform</li>
    <li>Violate Google's, Meta's, or any third-party API Terms of Service through your use of the Platform</li>
</ul>

<h2>6. Third-Party Integrations</h2>
<p>ReviewFlow integrates with Google Business Profile, Meta (Facebook/Instagram), and other third-party services. Your use of these integrations is subject to the respective third-party terms. ReviewFlow is not responsible for changes, outages, or policy modifications by these providers.</p>

<h2>7. AI-Generated Content</h2>
<p>The Platform uses AI (Google Gemini) to generate review replies, social captions, media, and other content. While we strive for accuracy, AI-generated content may not always be perfect. You are responsible for reviewing and approving all AI-generated content before it is published.</p>

<h2>8. Payment &amp; Billing</h2>
<ul>
    <li>Subscription fees are billed monthly in advance via Razorpay.</li>
    <li>All prices are in INR and include 18% GST.</li>
    <li>Failed payments may result in temporary suspension of your account.</li>
    <li>Credit top-ups are non-refundable once used.</li>
</ul>

<h2>9. Data Ownership &amp; Privacy</h2>
<p>You retain ownership of all content and data you upload or create on the Platform. ReviewFlow processes your data solely to provide the service. For details on how we handle your information, please see our <a href="<?php echo e(route('policy.privacy')); ?>">Privacy Policy</a>.</p>

<h2>10. Intellectual Property</h2>
<p>The ReviewFlow name, logo, design, and all platform code are the intellectual property of ReviewFlow and its creators. You may not copy, distribute, or create derivative works from any part of the Platform without written permission.</p>

<h2>11. Limitation of Liability</h2>
<p>To the maximum extent permitted by law, ReviewFlow shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the Platform. Our total liability shall not exceed the amount you paid to ReviewFlow in the 3 months preceding the event giving rise to the claim.</p>

<h2>12. Termination</h2>
<p>We may suspend or terminate your account if you violate these terms. You may cancel your subscription at any time from your account settings. Upon cancellation, your access continues until the end of the current billing period.</p>

<h2>13. Changes to Terms</h2>
<p>We reserve the right to modify these terms at any time. Material changes will be communicated via email or in-app notification at least 15 days before they take effect. Continued use after the effective date constitutes acceptance.</p>

<h2>14. Governing Law</h2>
<p>These Terms shall be governed by and construed in accordance with the laws of India. Any disputes shall be subject to the exclusive jurisdiction of the courts in New Delhi, India.</p>

<h2>15. Contact</h2>
<p>For questions about these Terms, contact us at <a href="mailto:support@reviewflow.in">support@reviewflow.in</a>.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('policies.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\policies\terms.blade.php ENDPATH**/ ?>