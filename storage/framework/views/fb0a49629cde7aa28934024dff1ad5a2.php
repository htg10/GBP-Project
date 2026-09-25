<?php $__env->startSection('title', 'Social'); ?>
<?php $__env->startSection('content'); ?>

<?php
    $platMeta = [
        'FACEBOOK'  => ['label'=>'Facebook',  'clr'=>'#1877f2', 'ic'=>'f'],
        'INSTAGRAM' => ['label'=>'Instagram', 'clr'=>'#e1306c', 'ic'=>'◉'],
        'LINKEDIN'  => ['label'=>'LinkedIn',  'clr'=>'#0a66c2', 'ic'=>'in'],
        'X'         => ['label'=>'X',          'clr'=>'#111',    'ic'=>'𝕏'],
    ];
    $badgeFor = fn($s) => $s==='PUBLISHED'?'teal':($s==='SCHEDULED'?'amber':($s==='FAILED'?'rose':'gray'));
?>

<div class="page-head">
    <div><h1>Social Studio</h1><p>Compose, preview and publish to Facebook &amp; Instagram in one place.</p></div>
    <span class="badge <?php echo e($liveCount ? 'teal' : 'gray'); ?>"><?php echo e($liveCount ? '● '.$liveCount.' account(s) connected' : 'No Meta account connected'); ?></span>
</div>

<div class="social-wrap">
    
    <div class="card composer">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div style="width:38px;height:38px;border-radius:11px;background:linear-gradient(135deg,#4c6fff,#8b5cf6);display:grid;place-items:center;color:#fff;font-size:17px;">✎</div>
            <div><div style="font-weight:700;">Create Post</div><div style="font-size:12px;color:var(--muted);">Write once, publish everywhere</div></div>
        </div>

        <form method="POST" action="<?php echo e(route('social.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Client</span>
                <select name="client_id" required>
                    <?php $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <span class="lbl">Platform</span>
            <div class="plat-picker">
                <?php $__currentLoopData = $platMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="plat-opt" style="--pc:<?php echo e($m['clr']); ?>;">
                        <input type="radio" name="platform" value="<?php echo e($key); ?>" <?php echo e($loop->first ? 'checked' : ''); ?> onchange="setPreviewPlatform('<?php echo e($m['label']); ?>','<?php echo e($m['clr']); ?>')">
                        <span class="plat-ic"><?php echo e($m['ic']); ?></span><?php echo e($m['label']); ?>

                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <label style="margin-top:6px;">
                <div style="display:flex;justify-content:space-between;"><span class="lbl">Caption</span><span style="font-size:11px;color:var(--muted);" id="soc-count">0 chars</span></div>
                <textarea name="body" id="soc-body" rows="5" placeholder="What's happening at your business?" oninput="updatePreview()" required></textarea>
            </label>
            <button type="button" onclick="genCaption(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:6px;">✨ Write with AI</button>

            <div style="margin-top:10px;">
                <span class="lbl">Image (optional)</span>
                <div class="soc-img-tabs">
                    <button type="button" class="soc-img-tab active" onclick="switchImgMode('upload',this)">📷 Upload</button>
                    <button type="button" class="soc-img-tab" onclick="switchImgMode('url',this)">🔗 URL</button>
                </div>
                <div id="soc-upload-mode">
                    <div class="soc-dropzone" id="soc-dropzone" onclick="document.getElementById('soc-file-input').click()">
                        <div style="font-size:22px;color:var(--muted);">📷</div>
                        <div style="font-size:13px;color:var(--muted);margin-top:6px;">Click to upload or drag & drop</div>
                        <div style="font-size:11px;color:var(--muted);">PNG, JPG, WebP up to 10MB</div>
                    </div>
                    <input type="file" id="soc-file-input" name="media_file" accept="image/*" style="display:none;" onchange="handleSocFile(this)">
                    <div id="soc-file-preview" style="display:none;margin-top:8px;">
                        <div style="position:relative;border-radius:10px;overflow:hidden;">
                            <img id="soc-file-preview-img" src="" style="width:100%;max-height:160px;object-fit:cover;display:block;">
                            <button type="button" onclick="clearSocFile()" style="position:absolute;top:6px;right:6px;width:24px;height:24px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;cursor:pointer;font-size:14px;">&times;</button>
                        </div>
                    </div>
                </div>
                <div id="soc-url-mode" style="display:none;">
                    <input type="url" name="media_url" id="soc-media" placeholder="https://... (public image)" oninput="updatePreview()">
                </div>
            </div>
            <label style="margin-top:10px;"><span class="lbl">Schedule (leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>

            <button type="submit" class="btn" style="width:100%;justify-content:center;margin-top:16px;padding:12px;">🚀 Publish Post</button>
        </form>
    </div>

    
    <div class="card preview-card">
        <div style="font-size:12px;color:var(--muted);font-weight:600;margin-bottom:12px;">LIVE PREVIEW</div>
        <div class="soc-preview">
            <div class="sp-head">
                <div class="sp-av" id="pv-badge" style="background:#1877f2;">f</div>
                <div><div class="sp-name">Your Business</div><div class="sp-plat" id="pv-plat">Facebook · Just now</div></div>
            </div>
            <div class="sp-body" id="pv-body">Your caption preview will appear here…</div>
            <div class="sp-img" id="pv-img" style="display:none;"><img id="pv-img-src" src="" alt=""></div>
            <div class="sp-actions"><span>👍 Like</span><span>💬 Comment</span><span>↗ Share</span></div>
        </div>
    </div>
</div>


<div style="margin:24px 0 12px;font-size:16px;font-weight:700;">Recent Posts</div>
<?php if($posts->isEmpty()): ?>
    <div class="card"><div class="empty">No posts yet. Compose your first post above.</div></div>
<?php else: ?>
<div class="soc-grid">
    <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $m = $platMeta[$post->platform] ?? ['label'=>$post->platform,'clr'=>'#888','ic'=>'•']; ?>
        <div class="card" style="padding:14px;">
            <div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">
                <div style="width:30px;height:30px;border-radius:8px;background:<?php echo e($m['clr']); ?>;color:#fff;display:grid;place-items:center;font-size:13px;font-weight:700;"><?php echo e($m['ic']); ?></div>
                <div style="flex:1;min-width:0;"><div style="font-size:12.5px;font-weight:700;"><?php echo e($m['label']); ?></div><div style="font-size:11px;color:var(--muted);"><?php echo e($post->client->name ?? ''); ?></div></div>
                <span class="badge <?php echo e($badgeFor($post->status)); ?>"><?php echo e($post->status === 'PUBLISHED' ? 'Live' : $post->status); ?></span>
            </div>
            <?php if(!empty($post->media_urls)): ?><img src="<?php echo e($post->media_urls[0]); ?>" style="width:100%;height:130px;object-fit:cover;border-radius:10px;margin-bottom:8px;background:#eef1f8;"><?php endif; ?>
            <p style="font-size:13px;line-height:1.5;"><?php echo e(Str::limit($post->body, 140)); ?></p>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:9px;border-top:1px solid var(--line);">
                <span style="font-size:11px;color:var(--muted);"><?php echo e($post->created_at->diffForHumans()); ?></span>
                <?php if($post->status === 'FAILED'): ?>
                    <form method="POST" action="<?php echo e(route('social.retry', $post)); ?>"><?php echo csrf_field(); ?><button class="btn btn-ghost" style="padding:4px 10px;font-size:11px;">Retry</button></form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<?php $__env->startPush('head'); ?>
<style>
    .social-wrap{display:grid;grid-template-columns:1.3fr 1fr;gap:16px;}
    .plat-picker{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:6px 0 4px;}
    .plat-opt{position:relative;display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 4px;border:1.5px solid var(--line);border-radius:10px;font-size:12.5px;font-weight:600;cursor:pointer;transition:.12s;}
    .plat-opt input{position:absolute;opacity:0;}
    .plat-opt .plat-ic{width:20px;height:20px;border-radius:6px;background:var(--pc);color:#fff;display:grid;place-items:center;font-size:11px;font-weight:800;}
    .plat-opt:has(input:checked){border-color:var(--pc);background:color-mix(in srgb, var(--pc) 8%, transparent);}
    .preview-card{align-self:start;position:sticky;top:70px;}
    .soc-preview{border:1px solid var(--line);border-radius:14px;overflow:hidden;background:var(--card);}
    .sp-head{display:flex;align-items:center;gap:10px;padding:12px 14px;}
    .sp-av{width:38px;height:38px;border-radius:50%;color:#fff;display:grid;place-items:center;font-weight:800;font-size:15px;}
    .sp-name{font-size:13.5px;font-weight:700;}
    .sp-plat{font-size:11px;color:var(--muted);}
    .sp-body{padding:0 14px 12px;font-size:13.5px;line-height:1.5;white-space:pre-wrap;word-break:break-word;}
    .sp-img img{width:100%;max-height:260px;object-fit:cover;display:block;}
    .sp-actions{display:flex;justify-content:space-around;padding:10px;border-top:1px solid var(--line);font-size:12px;color:var(--muted);}
    .soc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;}
    .soc-img-tabs{display:flex;gap:0;border:1px solid var(--line);border-radius:8px;overflow:hidden;margin-bottom:10px;}
    .soc-img-tab{flex:1;padding:7px 10px;font-size:12px;font-weight:600;background:var(--card);color:var(--muted);border:none;cursor:pointer;transition:all .15s;font-family:inherit;}
    .soc-img-tab:not(:first-child){border-left:1px solid var(--line);}
    .soc-img-tab.active{background:var(--ink);color:#fff;}
    .soc-dropzone{border:2px dashed var(--line);border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:all .15s;}
    .soc-dropzone:hover,.soc-dropzone.dragover{border-color:var(--teal);background:var(--teal-soft);}
    @media (max-width:820px){ .social-wrap{grid-template-columns:1fr;} .preview-card{position:static;} .plat-picker{grid-template-columns:repeat(2,1fr);} }
</style>
<?php $__env->stopPush(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
function updatePreview(){
    const body = document.getElementById('soc-body').value;
    const media = document.getElementById('soc-media').value;
    document.getElementById('pv-body').textContent = body || 'Your caption preview will appear here…';
    document.getElementById('soc-count').textContent = body.length + ' chars';
    const imgWrap = document.getElementById('pv-img');
    if(media){ document.getElementById('pv-img-src').src = media; imgWrap.style.display=''; }
    else { imgWrap.style.display='none'; }
}
function setPreviewPlatform(label, clr){
    document.getElementById('pv-badge').style.background = clr;
    document.getElementById('pv-plat').textContent = label + ' · Just now';
    document.getElementById('pv-badge').textContent = {Facebook:'f',Instagram:'◉',LinkedIn:'in',X:'𝕏'}[label] || '•';
}
async function genCaption(btn){
    const body = document.getElementById('soc-body');
    const prompt = body.value.trim() || 'a friendly social media post for a local business';
    btn.textContent='Writing…'; btn.disabled=true;
    try{
        const res = await fetch("<?php echo e(route('social.caption')); ?>",{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({prompt})});
        const data = await res.json();
        if(res.status === 402 && window.handlePlanRequired(data)) return;
        if(data.error){ alert(data.error); return; }
        body.value = data.body; updatePreview();
    }catch(e){ alert('Could not generate.'); }
    finally{ btn.textContent='✨ Write with AI'; btn.disabled=false; }
}

function switchImgMode(mode, btn){
    document.querySelectorAll('.soc-img-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('soc-upload-mode').style.display = mode === 'upload' ? '' : 'none';
    document.getElementById('soc-url-mode').style.display = mode === 'url' ? '' : 'none';
    if(mode === 'upload'){ document.getElementById('soc-media').value = ''; updatePreview(); }
    else { clearSocFile(); }
}

function handleSocFile(input){
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 10 * 1024 * 1024) { alert('File too large. Maximum size is 10MB.'); input.value = ''; return; }
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('soc-file-preview-img').src = e.target.result;
        document.getElementById('soc-file-preview').style.display = '';
        document.getElementById('soc-dropzone').style.display = 'none';
        const imgWrap = document.getElementById('pv-img');
        document.getElementById('pv-img-src').src = e.target.result;
        imgWrap.style.display = '';
    };
    reader.readAsDataURL(file);
}

function clearSocFile(){
    const input = document.getElementById('soc-file-input');
    if(input) input.value = '';
    document.getElementById('soc-file-preview').style.display = 'none';
    document.getElementById('soc-dropzone').style.display = '';
    document.getElementById('pv-img').style.display = 'none';
}

const socDrop = document.getElementById('soc-dropzone');
if(socDrop){
    ['dragenter','dragover'].forEach(ev => socDrop.addEventListener(ev, e => { e.preventDefault(); socDrop.classList.add('dragover'); }));
    ['dragleave','drop'].forEach(ev => socDrop.addEventListener(ev, e => { e.preventDefault(); socDrop.classList.remove('dragover'); }));
    socDrop.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if(files.length && files[0].type.startsWith('image/')){
            const input = document.getElementById('soc-file-input');
            input.files = files;
            handleSocFile(input);
        }
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\social.blade.php ENDPATH**/ ?>