<?php $__env->startSection('title', 'Google Posts'); ?>
<?php $__env->startSection('content'); ?>

<?php $__env->startPush('head'); ?>
<style>
    .gp-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;gap:14px;flex-wrap:wrap;}
    .gp-header h1{font-size:22px;font-weight:700;letter-spacing:-.02em;}
    .gp-header p{font-size:13px;color:var(--muted);margin-top:3px;}
    .gp-actions{display:flex;gap:8px;flex-shrink:0;}

    .gp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px;}
    .gp-stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:16px 18px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px;}
    .gp-stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:18px;flex-shrink:0;}
    .gp-stat-icon.blue{background:var(--teal-soft);color:var(--teal);}
    .gp-stat-icon.green{background:var(--green-soft);color:var(--green);}
    .gp-stat-icon.amber{background:var(--amber-soft);color:var(--amber);}
    .gp-stat-icon.rose{background:var(--rose-soft);color:var(--rose);}
    .gp-stat-val{font-size:24px;font-weight:800;line-height:1;}
    .gp-stat-lbl{font-size:11.5px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.04em;margin-top:2px;}

    .gp-tabs{display:flex;gap:6px;margin-bottom:16px;}
    .gp-tab{border:1px solid var(--line);border-radius:999px;padding:8px 18px;font-size:13px;font-weight:600;background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;border:1px solid var(--line);}
    .gp-tab:hover{border-color:var(--teal);color:var(--teal);}
    .gp-tab.active{background:var(--ink);color:#fff;border-color:var(--ink);}
    .gp-tab .count{font-size:11px;opacity:.7;margin-left:3px;}

    .gp-filters{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center;}
    .gp-chip{border:1px solid var(--line);border-radius:999px;padding:6px 14px;font-size:12.5px;font-weight:500;background:var(--card);color:var(--muted);cursor:pointer;transition:all .15s;}
    .gp-chip:hover{border-color:var(--teal);color:var(--teal);}
    .gp-chip.active{background:var(--teal-soft);color:var(--teal-ink);border-color:var(--teal-soft);}

    .gp-card{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);margin-bottom:12px;display:flex;overflow:hidden;transition:border-color .15s;}
    .gp-card:hover{border-color:#d0d5e0;}
    .gp-thumb{width:140px;min-height:120px;flex-shrink:0;background:var(--paper);display:grid;place-items:center;overflow:hidden;position:relative;}
    .gp-thumb img{width:100%;height:100%;object-fit:cover;}
    .gp-thumb-placeholder{color:var(--muted);font-size:28px;}
    .gp-thumb .gp-type-badge{position:absolute;top:8px;left:8px;}
    .gp-body{flex:1;padding:16px 18px;display:flex;flex-direction:column;min-width:0;}
    .gp-body-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:6px;}
    .gp-loc{font-size:12px;color:var(--muted);margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .gp-text{font-size:14px;line-height:1.55;color:var(--ink);flex:1;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;}
    .gp-meta{display:flex;align-items:center;gap:10px;margin-top:auto;padding-top:10px;flex-wrap:wrap;}
    .gp-date{font-size:12px;color:var(--muted);}
    .gp-card-actions{display:flex;gap:6px;margin-left:auto;flex-shrink:0;}
    .gp-card-actions .btn{padding:5px 12px;font-size:11.5px;}

    .gp-photo-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;}
    .gp-photo{background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;transition:border-color .15s;}
    .gp-photo:hover{border-color:#d0d5e0;}
    .gp-photo-img{width:100%;height:170px;object-fit:cover;display:block;background:var(--paper);}
    .gp-photo-body{padding:14px;}
    .gp-photo-meta{display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;}
    .gp-photo-caption{font-size:13px;line-height:1.5;color:var(--ink);margin-bottom:10px;}
    .gp-photo-actions{display:flex;gap:6px;}
    .gp-photo-actions .btn{padding:5px 12px;font-size:11.5px;flex:1;justify-content:center;}

    .post-type-tabs{display:flex;gap:0;border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-bottom:18px;}
    .post-type-tab{flex:1;padding:10px;font-size:13px;font-weight:600;background:var(--card);color:var(--muted);border:none;cursor:pointer;transition:all .15s;}
    .post-type-tab:not(:first-child){border-left:1px solid var(--line);}
    .post-type-tab.active{background:var(--ink);color:#fff;}

    #img-drop{border:2px dashed var(--line);border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:border-color .15s;}
    #img-drop:hover{border-color:var(--teal);}

    @media (max-width:700px){
        .gp-stats{grid-template-columns:repeat(2,1fr);}
        .gp-card{flex-direction:column;}
        .gp-thumb{width:100%;min-height:160px;}
        .gp-photo-grid{grid-template-columns:repeat(auto-fill,minmax(180px,1fr));}
    }
    @media (max-width:480px){
        .gp-stats{grid-template-columns:1fr 1fr;}
    }
</style>
<?php $__env->stopPush(); ?>

<?php
    $totalPosts = $posts->count();
    $published = $posts->where('status', 'PUBLISHED')->count();
    $scheduled = $posts->where('status', 'SCHEDULED')->count();
    $drafts = $posts->whereIn('status', ['DRAFT', 'FAILED'])->count();
    $badgeFor = fn($s) => $s === 'PUBLISHED' ? 'teal' : ($s === 'SCHEDULED' ? 'amber' : ($s === 'FAILED' ? 'rose' : 'gray'));
?>

<div class="gp-header">
    <div>
        <h1>Google Posts</h1>
        <p>Publish updates, offers, and events to your Google Business Profile.</p>
    </div>
    <div class="gp-actions">
        <button class="btn btn-ghost" onclick="document.getElementById('photo-modal').classList.add('open')">📷 New Photo</button>
        <button class="btn" onclick="openPostModal()">+ Add Post</button>
    </div>
</div>

<?php if($locations->isEmpty()): ?>
    <div class="alert info">Add a client with a Google location first — posts and photos attach to a location.</div>
<?php endif; ?>


<div class="gp-stats">
    <div class="gp-stat">
        <div class="gp-stat-icon blue">📝</div>
        <div>
            <div class="gp-stat-val"><?php echo e($totalPosts); ?></div>
            <div class="gp-stat-lbl">Total Posts</div>
        </div>
    </div>
    <div class="gp-stat">
        <div class="gp-stat-icon green">✓</div>
        <div>
            <div class="gp-stat-val"><?php echo e($published); ?></div>
            <div class="gp-stat-lbl">Published</div>
        </div>
    </div>
    <div class="gp-stat">
        <div class="gp-stat-icon amber">⏱</div>
        <div>
            <div class="gp-stat-val"><?php echo e($scheduled); ?></div>
            <div class="gp-stat-lbl">Scheduled</div>
        </div>
    </div>
    <div class="gp-stat">
        <div class="gp-stat-icon rose">📷</div>
        <div>
            <div class="gp-stat-val"><?php echo e($photos->count()); ?></div>
            <div class="gp-stat-lbl">Photos</div>
        </div>
    </div>
</div>


<div class="gp-tabs">
    <button type="button" class="gp-tab active" data-tab="posts" onclick="switchTab('posts', this)">Posts<span class="count">(<?php echo e($totalPosts); ?>)</span></button>
    <button type="button" class="gp-tab" data-tab="photos" onclick="switchTab('photos', this)">Photos<span class="count">(<?php echo e($photos->count()); ?>)</span></button>
</div>


<div id="tab-posts">
    
    <div class="gp-filters">
        <button type="button" class="gp-chip active" data-status="all" onclick="filterPosts('all', this)">All</button>
        <button type="button" class="gp-chip" data-status="PUBLISHED" onclick="filterPosts('PUBLISHED', this)">Published</button>
        <button type="button" class="gp-chip" data-status="SCHEDULED" onclick="filterPosts('SCHEDULED', this)">Scheduled</button>
        <button type="button" class="gp-chip" data-status="DRAFT" onclick="filterPosts('DRAFT', this)">Draft / Failed</button>
    </div>

    <?php if($posts->isEmpty()): ?>
        <div class="card"><div class="empty">No posts yet. Click "Add Post" to create your first update, offer, or event.</div></div>
    <?php else: ?>
        <div id="posts-list">
            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $editData = ['id'=>$p->id,'type'=>$p->type,'body'=>$p->body,'cta_url'=>$p->cta_url]; ?>
                <div class="gp-card" data-status="<?php echo e($p->status); ?>">
                    <div class="gp-thumb">
                        <?php if($p->image): ?>
                            <img src="<?php echo e($p->image); ?>" alt="">
                        <?php else: ?>
                            <div class="gp-thumb-placeholder">📝</div>
                        <?php endif; ?>
                        <span class="badge gp-type-badge <?php echo e($p->type === 'OFFER' ? 'amber' : ($p->type === 'EVENT' ? 'rose' : 'teal')); ?>"><?php echo e(ucfirst(strtolower($p->type))); ?></span>
                    </div>
                    <div class="gp-body">
                        <div class="gp-body-top">
                            <div style="min-width:0;flex:1;">
                                <div class="gp-loc"><?php echo e($p->location->title ?? $p->location->google_name); ?> · <?php echo e($p->location->client->name); ?></div>
                                <div class="gp-text"><?php echo e($p->body); ?></div>
                            </div>
                            <span class="badge <?php echo e($badgeFor($p->status)); ?>"><?php echo e($p->status === 'PUBLISHED' ? 'Live' : ucfirst(strtolower($p->status))); ?></span>
                        </div>
                        <div class="gp-meta">
                            <span class="gp-date"><?php echo e(($p->published_at ?? $p->scheduled_at ?? $p->created_at)->format('M d, Y')); ?></span>
                            <?php if($p->cta_url): ?>
                                <a href="<?php echo e($p->cta_url); ?>" target="_blank" style="font-size:12px;color:var(--teal);font-weight:500;">🔗 CTA Link</a>
                            <?php endif; ?>
                            <div class="gp-card-actions">
                                <button type="button" class="btn btn-ghost" onclick='openEditPost(<?php echo json_encode($editData, 15, 512) ?>)'>Edit</button>
                                <form method="POST" action="<?php echo e(route('gbp-content.posts.publish', $p)); ?>" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-ghost"><?php echo e($p->status === 'PUBLISHED' ? 'Re-publish' : ($p->status === 'FAILED' ? 'Retry' : 'Publish')); ?></button>
                                </form>
                                <form method="POST" action="<?php echo e(route('gbp-content.posts.destroy', $p)); ?>" onsubmit="return confirm('Delete this post?')" style="display:inline;">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-ghost" style="color:var(--rose);border-color:var(--rose-soft);">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <div id="posts-empty" class="card" style="display:none;"><div class="empty">No posts match this filter.</div></div>
    <?php endif; ?>
</div>


<div id="tab-photos" style="display:none;">
    <?php if($photos->isEmpty()): ?>
        <div class="card"><div class="empty">No photos yet. Click "New Photo" to add one.</div></div>
    <?php else: ?>
        <div class="gp-photo-grid">
            <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ph): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="gp-photo">
                    <img src="<?php echo e($ph->image); ?>" alt="" class="gp-photo-img">
                    <div class="gp-photo-body">
                        <div class="gp-photo-meta">
                            <span style="font-size:12px;color:var(--muted);"><?php echo e($ph->location->client->name); ?></span>
                            <span class="badge <?php echo e($badgeFor($ph->status)); ?>"><?php echo e($ph->status); ?></span>
                        </div>
                        <?php if($ph->caption): ?>
                            <p class="gp-photo-caption"><?php echo e($ph->caption); ?></p>
                        <?php endif; ?>
                        <div class="gp-photo-actions">
                            <?php if($ph->status !== 'PUBLISHED'): ?>
                                <form method="POST" action="<?php echo e(route('gbp-content.photos.publish', $ph)); ?>" style="flex:1;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-ghost" style="width:100%;"><?php echo e($ph->status === 'FAILED' ? 'Retry' : 'Publish'); ?></button>
                                </form>
                            <?php endif; ?>
                            <form method="POST" action="<?php echo e(route('gbp-content.photos.destroy', $ph)); ?>" onsubmit="return confirm('Delete this photo?')" style="flex:1;">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="btn btn-ghost" style="width:100%;color:var(--rose);border-color:var(--rose-soft);">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>


<div class="modal-bg" id="post-modal">
    <div class="modal" style="max-width:580px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="margin:0;" id="post-modal-title">Add Post</h2>
            <button type="button" onclick="document.getElementById('post-modal').classList.remove('open')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>

        <div class="post-type-tabs">
            <button type="button" class="post-type-tab active" data-type="UPDATE" onclick="setPostType('UPDATE',this)">Update</button>
            <button type="button" class="post-type-tab" data-type="OFFER" onclick="setPostType('OFFER',this)">Offer</button>
            <button type="button" class="post-type-tab" data-type="EVENT" onclick="setPostType('EVENT',this)">Event</button>
        </div>

        <form method="POST" id="post-form" action="<?php echo e(route('gbp-content.posts.store')); ?>" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="type" id="post-type-input" value="UPDATE">

            <label><span class="lbl">Location</span>
                <select name="gbp_location_id" id="post-location-select">
                    <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($loc->id); ?>" data-business="<?php echo e($loc->client->name); ?>"><?php echo e($loc->title ?? $loc->google_name); ?> — <?php echo e($loc->client->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>

            <div style="margin-top:12px;">
                <span class="lbl">Image (optional)</span>
                <div id="img-drop" onclick="document.getElementById('post-image-input').click()">
                    <div style="font-size:24px;color:var(--muted);">📷</div>
                    <div style="font-size:13px;color:var(--muted);margin-top:6px;">Click to upload or drag & drop</div>
                    <div style="font-size:11px;color:var(--muted);">PNG, JPG, GIF up to 5MB</div>
                </div>
                <input type="file" id="post-image-input" name="image" accept="image/*" style="display:none;" onchange="previewImg(this)">
                <div id="img-preview" style="display:none;margin-top:8px;position:relative;">
                    <img id="img-preview-src" src="" style="width:100%;max-height:180px;object-fit:cover;border-radius:10px;">
                    <button type="button" onclick="clearImg()" style="position:absolute;top:6px;right:6px;width:24px;height:24px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;cursor:pointer;font-size:14px;">&times;</button>
                </div>
            </div>

            <label style="margin-top:12px;">
                <div style="display:flex;justify-content:space-between;"><span class="lbl">Description</span><span id="char-count" style="font-size:11px;color:var(--muted);">0 / 1500</span></div>
                <textarea name="body" id="post-body" rows="4" placeholder="Describe the update, offer, or event..." oninput="document.getElementById('char-count').textContent=this.value.length+' / 1500'" maxlength="1500"></textarea>
            </label>
            <button type="button" onclick="genPostCopy(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:6px;">✦ Generate with AI</button>

            <label style="margin-top:10px;"><span class="lbl">Call to Action URL (optional)</span><input type="url" name="cta_url" placeholder="https://..."></label>

            <div id="offer-fields" style="display:none;">
                <label style="margin-top:10px;"><span class="lbl">Coupon Code</span><input type="text" name="coupon_code" placeholder="e.g. SAVE20"></label>
                <label style="margin-top:10px;"><span class="lbl">Terms & Conditions</span><textarea name="terms" rows="2" placeholder="Offer terms..."></textarea></label>
            </div>

            <div id="date-fields" style="display:none;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:10px;">
                    <label><span class="lbl">Start Date & Time</span><input type="datetime-local" name="start_date"></label>
                    <label><span class="lbl">End Date & Time</span><input type="datetime-local" name="end_date"></label>
                </div>
            </div>

            <label style="margin-top:10px;"><span class="lbl">Schedule (leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>

            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('post-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Save Post</button>
            </div>
        </form>
    </div>
</div>


<div class="modal-bg" id="photo-modal">
    <div class="modal">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="margin:0;">New Photo</h2>
            <button type="button" onclick="document.getElementById('photo-modal').classList.remove('open')" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--muted);">&times;</button>
        </div>
        <form method="POST" action="<?php echo e(route('gbp-content.photos.store')); ?>">
            <?php echo csrf_field(); ?>
            <label><span class="lbl">Location</span>
                <select name="gbp_location_id">
                    <?php $__currentLoopData = $locations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($loc->id); ?>"><?php echo e($loc->title ?? $loc->google_name); ?> — <?php echo e($loc->client->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label><span class="lbl">Image URL</span><input type="url" name="image" id="photo-url" placeholder="https://... (must be a public image URL)"></label>
            <label><span class="lbl">Caption</span><textarea name="caption" id="photo-caption" rows="3" placeholder="Short caption..."></textarea></label>
            <button type="button" onclick="genCaption(this)" style="background:none;border:none;color:var(--teal);font-size:12.5px;font-weight:600;cursor:pointer;margin-top:6px;">✦ Generate caption with AI</button>
            <label style="margin-top:10px;"><span class="lbl">Schedule (leave blank to publish now)</span><input type="datetime-local" name="scheduled_at"></label>
            <div style="display:flex;gap:10px;margin-top:18px;">
                <button type="button" class="btn btn-ghost" style="flex:1;" onclick="document.getElementById('photo-modal').classList.remove('open')">Cancel</button>
                <button type="submit" class="btn" style="flex:1;justify-content:center;">Save Photo</button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function switchTab(tab, btn){
    document.querySelectorAll('.gp-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-posts').style.display = tab === 'posts' ? '' : 'none';
    document.getElementById('tab-photos').style.display = tab === 'photos' ? '' : 'none';
}

function filterPosts(status, btn){
    document.querySelectorAll('.gp-chip').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const cards = document.querySelectorAll('.gp-card');
    let visible = 0;
    cards.forEach(c => {
        const s = c.dataset.status;
        const show = status === 'all'
            || s === status
            || (status === 'DRAFT' && (s === 'DRAFT' || s === 'FAILED'));
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const empty = document.getElementById('posts-empty');
    if (empty) empty.style.display = visible === 0 ? '' : 'none';
}

function openPostModal(){
    document.getElementById('post-form').action = "<?php echo e(route('gbp-content.posts.store')); ?>";
    document.getElementById('post-modal-title').textContent = 'Add Post';
    document.getElementById('post-body').value = '';
    document.getElementById('char-count').textContent = '0 / 1500';
    document.querySelector('#post-form input[name=cta_url]').value = '';
    clearImg();
    setPostType('UPDATE', document.querySelector('.post-type-tab[data-type="UPDATE"]'));
    document.getElementById('post-modal').classList.add('open');
}

function openEditPost(p){
    document.getElementById('post-form').action = "<?php echo e(url('gbp-content/posts')); ?>/" + p.id;
    document.getElementById('post-modal-title').textContent = 'Edit Post';
    document.getElementById('post-body').value = p.body || '';
    document.getElementById('char-count').textContent = (p.body||'').length + ' / 1500';
    document.querySelector('#post-form input[name=cta_url]').value = p.cta_url || '';
    setPostType(p.type, document.querySelector('.post-type-tab[data-type="'+p.type+'"]'));
    document.getElementById('post-modal').classList.add('open');
}

function setPostType(type, btn){
    document.querySelectorAll('.post-type-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('post-type-input').value = type;
    document.getElementById('offer-fields').style.display = type === 'OFFER' ? '' : 'none';
    document.getElementById('date-fields').style.display = (type === 'OFFER' || type === 'EVENT') ? '' : 'none';
}

function previewImg(input){
    if(input.files && input.files[0]){
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('img-preview-src').src = e.target.result;
            document.getElementById('img-preview').style.display = '';
            document.getElementById('img-drop').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function clearImg(){
    const input = document.getElementById('post-image-input');
    if (input) input.value = '';
    const preview = document.getElementById('img-preview');
    if (preview) preview.style.display = 'none';
    const drop = document.getElementById('img-drop');
    if (drop) drop.style.display = '';
}

async function genPostCopy(btn){
    const body = document.getElementById('post-body');
    const type = document.getElementById('post-type-input').value;
    const opt = document.getElementById('post-location-select').selectedOptions[0];
    const business = opt ? opt.dataset.business : 'the business';
    btn.textContent='Generating...';btn.disabled=true;
    try{
        const res = await fetch('<?php echo e(route("gbp-content.posts.generate")); ?>',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({type,business})});
        const data = await res.json();
        if(data.error){alert(data.error);return;}
        body.value = data.body;
        document.getElementById('char-count').textContent = body.value.length + ' / 1500';
    }catch(e){alert('Could not generate.');}
    finally{btn.textContent='✦ Generate with AI';btn.disabled=false;}
}

async function genCaption(btn){
    const caption = document.getElementById('photo-caption');
    const url = document.getElementById('photo-url').value || 'a business photo';
    btn.textContent='Generating...';btn.disabled=true;
    try{
        const res = await fetch('<?php echo e(route("gbp-content.photos.caption")); ?>',{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Content-Type':'application/json'},body:JSON.stringify({prompt:url})});
        const data = await res.json();
        if(data.error){alert(data.error);return;}
        caption.value = data.caption;
    }catch(e){alert('Could not generate.');}
    finally{btn.textContent='✦ Generate caption with AI';btn.disabled=false;}
}

const drop = document.getElementById('img-drop');
if(drop){
    ['dragenter','dragover'].forEach(e => drop.addEventListener(e, ev => {ev.preventDefault();drop.style.borderColor='var(--teal)';}));
    ['dragleave','drop'].forEach(e => drop.addEventListener(e, ev => {ev.preventDefault();drop.style.borderColor='var(--line)';}));
    drop.addEventListener('drop', ev => {
        const files = ev.dataTransfer.files;
        if(files.length){
            document.getElementById('post-image-input').files = files;
            previewImg(document.getElementById('post-image-input'));
        }
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\gbp-content.blade.php ENDPATH**/ ?>