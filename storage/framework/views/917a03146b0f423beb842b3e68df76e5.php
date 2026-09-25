<?php $__env->startSection('title', 'Shipping Policy'); ?>
<?php $__env->startSection('content'); ?>
<h1>Shipping Policy</h1>
<p class="updated">Last updated: <?php echo e(date('d M Y')); ?></p>

<h2>Digital Product — No Physical Shipping</h2>
<p>ReviewFlow is a 100% digital, cloud-based Software as a Service (SaaS) platform. There are no physical goods or products involved, and therefore <strong>no shipping or delivery of physical items</strong> takes place.</p>

<h2>Service Activation</h2>
<p>Upon successful payment, your subscription or credit top-up is activated <strong>instantly</strong>. You will receive:</p>
<ul>
    <li>Immediate access to all features included in your subscribed plan</li>
    <li>AI credits added to your account balance within seconds</li>
    <li>A confirmation email with your payment receipt and invoice</li>
</ul>

<h2>Access &amp; Availability</h2>
<p>ReviewFlow is accessible from any web browser at any time. We aim for 99.9% uptime. In the event of scheduled maintenance, users will be notified in advance via email or an in-app notification.</p>

<h2>Account Credentials</h2>
<p>Your login credentials (email and password) are created at the time of registration. No physical credentials, tokens, or hardware are shipped. If you signed up via Google OAuth, your Google account serves as your login method.</p>

<h2>Contact</h2>
<p>If you face any issues accessing the platform after payment, contact us at <a href="mailto:support@reviewflow.in">support@reviewflow.in</a> and we will resolve it within 24 hours.</p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('policies.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\policies\shipping.blade.php ENDPATH**/ ?>