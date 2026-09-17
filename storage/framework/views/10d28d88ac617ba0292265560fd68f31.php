<?php $__env->startSection('title', 'AI Mode'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-head">
    <div><h1>AI Mode</h1><p>Your marketing assistant — ask anything, powered by AI.</p></div>
</div>

<div class="card" style="padding:0;display:flex;flex-direction:column;height:560px;">
    <!-- messages -->
    <div id="chat" style="flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:14px;">
        <div class="msg-ai" style="align-self:flex-start;max-width:80%;">
            <div style="background:var(--teal-soft);color:#123;border-radius:14px 14px 14px 4px;padding:12px 15px;font-size:14px;line-height:1.55;">
                Hi! I'm your ReviewFlow AI assistant. Ask me to draft a reply, write a social post, suggest marketing ideas, or anything else. Try a quick prompt below 👇
            </div>
        </div>
    </div>

    <!-- quick prompts -->
    <div style="padding:0 16px 10px;display:flex;gap:8px;flex-wrap:wrap;">
        <?php $__currentLoopData = [
            'Write a festive Diwali offer post for a dental clinic',
            'How do I get more Google reviews?',
            'Reply to an angry customer review',
            '5 local SEO tips for a salon',
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <button type="button" class="qp" style="border:1px solid var(--line);background:#fcfcfa;border-radius:999px;padding:6px 12px;font-size:12px;cursor:pointer;"><?php echo e($qp); ?></button>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <!-- input -->
    <div style="border-top:1px solid var(--line);padding:14px 16px;display:flex;gap:10px;">
        <input type="text" id="msg" placeholder="Ask me anything…" style="flex:1;" onkeydown="if(event.key==='Enter')sendMsg()">
        <button class="btn" id="send-btn" onclick="sendMsg()">Send</button>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const chat = document.getElementById('chat');

document.querySelectorAll('.qp').forEach(b => {
    b.addEventListener('click', () => { document.getElementById('msg').value = b.textContent; sendMsg(); });
});

function addBubble(text, who){
    const wrap = document.createElement('div');
    wrap.style.alignSelf = who === 'user' ? 'flex-end' : 'flex-start';
    wrap.style.maxWidth = '80%';
    const bubble = document.createElement('div');
    bubble.style.padding = '12px 15px';
    bubble.style.fontSize = '14px';
    bubble.style.lineHeight = '1.55';
    bubble.style.whiteSpace = 'pre-wrap';
    if (who === 'user') {
        bubble.style.background = 'var(--ink)';
        bubble.style.color = '#fff';
        bubble.style.borderRadius = '14px 14px 4px 14px';
    } else {
        bubble.style.background = 'var(--teal-soft)';
        bubble.style.color = '#123';
        bubble.style.borderRadius = '14px 14px 14px 4px';
    }
    bubble.textContent = text;
    wrap.appendChild(bubble);
    chat.appendChild(wrap);
    chat.scrollTop = chat.scrollHeight;
    return bubble;
}

async function sendMsg(){
    const input = document.getElementById('msg');
    const message = input.value.trim();
    if (!message) return;
    input.value = '';
    addBubble(message, 'user');

    const thinking = addBubble('Thinking…', 'ai');
    const btn = document.getElementById('send-btn');
    btn.disabled = true;

    try {
        const res = await fetch("<?php echo e(route('ai.send')); ?>", {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json'},
            body: JSON.stringify({message})
        });
        const data = await res.json();
        thinking.textContent = data.reply || 'No response.';
    } catch (e) {
        thinking.textContent = 'Something went wrong. Try again.';
    } finally {
        btn.disabled = false;
        input.focus();
        chat.scrollTop = chat.scrollHeight;
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\reviewflow-laravel\resources\views\dashboard\ai-mode.blade.php ENDPATH**/ ?>