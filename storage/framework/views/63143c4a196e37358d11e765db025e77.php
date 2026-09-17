<?php $__env->startSection('title', 'AI Generated Media'); ?>
<?php $__env->startSection('content'); ?>
<?php $creditBalance = \App\Models\Subscription::where('agency_id', auth()->user()->agency_id)->value('credit_balance') ?? 0; ?>

<div class="page-head">
    <div>
        <h1>AI Image Generator</h1>
        <p>Transform your ideas into stunning visuals</p>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="display:flex;align-items:center;gap:8px;background:var(--card);border:1px solid var(--line);border-radius:12px;padding:8px 14px;">
            <div style="width:32px;height:32px;border-radius:50%;background:var(--teal-soft);display:grid;place-items:center;font-size:14px;"><?php echo e($creditBalance > 50 ? '✓' : '!'); ?></div>
            <div>
                <div style="font-size:18px;font-weight:700;line-height:1;"><?php echo e(number_format($creditBalance)); ?></div>
                <div style="font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Credits Left</div>
            </div>
        </div>
    </div>
</div>


<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:18px;">
    <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
        <div style="width:36px;height:36px;border-radius:10px;background:var(--teal-soft);display:grid;place-items:center;font-size:15px;flex-shrink:0;">1</div>
        <div>
            <div style="font-weight:700;font-size:13.5px;">Describe Your Image</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Write what you want — our AI enhances your prompt automatically.</div>
        </div>
    </div>
    <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
        <div style="width:36px;height:36px;border-radius:10px;background:var(--amber-soft);display:grid;place-items:center;font-size:15px;flex-shrink:0;">2</div>
        <div>
            <div style="font-weight:700;font-size:13.5px;">Keep the Prompt Minimal</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Just describe the core idea in a line or two for a better result.</div>
        </div>
    </div>
    <div class="card" style="display:flex;gap:12px;align-items:flex-start;">
        <div style="width:36px;height:36px;border-radius:10px;background:var(--rose-soft);display:grid;place-items:center;font-size:15px;flex-shrink:0;">3</div>
        <div>
            <div style="font-weight:700;font-size:13.5px;">Push to Google</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Generated images can be sent directly to your GBP profile as photos.</div>
        </div>
    </div>
</div>


<div style="background:var(--teal-soft);border-radius:14px;padding:14px 18px;margin-bottom:18px;display:flex;align-items:center;gap:12px;justify-content:space-between;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:32px;height:32px;border-radius:50%;background:var(--teal);color:#fff;display:grid;place-items:center;font-size:14px;">?</div>
        <div>
            <strong style="font-size:13.5px;">Pro tip — keep your prompt short and simple</strong>
            <div style="font-size:12px;color:var(--teal-ink);margin-top:2px;">You don't need long, detailed instructions. Just describe the main subject or offer. Each generation uses <strong><?php echo e(\App\Services\CreditService::COSTS['ai_media_generate']); ?> credits</strong>.</div>
        </div>
    </div>
    <span class="badge teal" style="white-space:nowrap;font-size:12px;padding:5px 14px;"><?php echo e(\App\Services\CreditService::COSTS['ai_media_generate']); ?> Credits / Image</span>
</div>

<?php if(!config('services.gemini.key')): ?>
    <div class="alert info">No GEMINI_API_KEY set — generated images will be placeholders. Add a key to your .env for real AI images.</div>
<?php endif; ?>


<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:24px;" id="gen-section">
    <div class="card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--teal-soft);display:grid;place-items:center;font-size:16px;">✦</div>
            <div>
                <div style="font-weight:700;">AI Image Generator (<?php echo e(\App\Services\CreditService::COSTS['ai_media_generate']); ?> Credits)</div>
                <div style="font-size:12px;color:var(--muted);">Transform your ideas into stunning visuals</div>
            </div>
        </div>

        <label><span class="lbl">Client (optional)</span>
            <select id="gm-client">
                <option value="">-- No client --</option>
                <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>

        <label><span class="lbl">Image Prompt *</span>
            <textarea id="gm-prompt" rows="4" placeholder="Describe how you want to transform your image..." style="min-height:100px;"></textarea>
        </label>
        <div style="font-size:11.5px;color:var(--muted);margin-top:4px;">Be specific and descriptive for best results (minimum 10 characters).</div>

        <button type="button" class="btn" style="width:100%;justify-content:center;margin-top:18px;padding:13px 20px;" id="gm-submit" onclick="generateMedia()">
            ✦ Generate Image
        </button>
    </div>

    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:300px;">
        <div id="preview-area" style="text-align:center;">
            <div style="width:80px;height:80px;border-radius:16px;background:var(--teal-soft);display:grid;place-items:center;margin:0 auto 12px;font-size:30px;">🖼</div>
            <div style="font-weight:700;font-size:15px;">No image generated yet</div>
            <div style="font-size:13px;color:var(--muted);margin-top:4px;">Fill the form and click Generate</div>
        </div>
    </div>
</div>


<?php if($media->isNotEmpty()): ?>
<div style="margin-bottom:12px;font-size:16px;font-weight:700;">Generated Images</div>
<div id="media-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px;">
    <?php $__currentLoopData = $media; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card" style="padding:12px;">
            <img src="<?php echo e($m->image_data); ?>" alt="" style="width:100%;height:160px;object-fit:cover;border-radius:10px;background:#f3f1ea;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:9px;">
                <span class="badge <?php echo e($m->source === 'ai' ? 'teal' : 'gray'); ?>"><?php echo e($m->source === 'ai' ? 'AI' : 'Placeholder'); ?></span>
                <span style="font-size:11px;color:var(--muted);"><?php echo e($m->created_at->format('d M, h:i A')); ?></span>
            </div>
            <p style="font-size:12.5px;color:#2a3a35;margin-top:6px;line-height:1.4;"><?php echo e(\Illuminate\Support\Str::limit($m->prompt, 80)); ?></p>
            <?php if($locations->isNotEmpty()): ?>
                <form method="POST" action="<?php echo e(route('ai-media.use-as-photo', $m)); ?>" style="margin-top:9px;display:flex;gap:6px;">
                    <?php echo csrf_field(); ?>
                    <select name="gbp_location_id" style="flex:1;padding:6px 8px;font-size:11.5px;">
                        <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->title ?: $loc->google_name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="btn" style="padding:6px 10px;font-size:11.5px;white-space:nowrap;">Use as photo</button>
                </form>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('ai-media.destroy', $m)); ?>" onsubmit="return confirm('Delete this image?')" style="margin-top:6px;">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-ghost" style="width:100%;padding:5px 10px;font-size:11.5px;color:var(--rose);">Delete</button>
            </form>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<?php $__env->startPush('head'); ?>
<style>
    @media (max-width:700px){
        #gen-section{grid-template-columns:1fr !important;}
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
async function generateMedia(){
    const prompt = document.getElementById('gm-prompt').value.trim();
    const client_id = document.getElementById('gm-client').value;
    if (!prompt || prompt.length < 10) { alert('Please describe the image (at least 10 characters).'); return; }

    const btn = document.getElementById('gm-submit');
    const preview = document.getElementById('preview-area');
    btn.innerHTML = '<span style="display:inline-block;animation:spin 1s linear infinite;">↻</span> Generating...';
    btn.disabled = true;
    preview.innerHTML = '<div style="font-size:14px;color:var(--muted);">Generating your image...</div>';

    try {
        const res = await fetch("<?php echo e(route('ai-media.generate')); ?>", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json'},
            body: JSON.stringify({prompt, client_id: client_id || null})
        });
        const data = await res.json();

        if (data.error) {
            preview.innerHTML = '<div style="color:var(--rose);font-size:14px;">' + data.error + '</div>';
            return;
        }

        if (data.media) {
            preview.innerHTML = '<img src="' + data.media.image_data + '" style="max-width:100%;max-height:350px;border-radius:12px;">';
            setTimeout(() => location.reload(), 1500);
        }
    } catch (e) {
        preview.innerHTML = '<div style="color:var(--rose);font-size:14px;">Could not generate image. Try again.</div>';
    } finally {
        btn.innerHTML = '✦ Generate Image'; btn.disabled = false;
    }
}
</script>
<style>@keyframes spin{from{transform:rotate(0)}to{transform:rotate(360deg)}}</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\ai-media.blade.php ENDPATH**/ ?>