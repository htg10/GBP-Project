@extends('layouts.app')
@section('title', 'AI Mode')
@section('content')

@push('head')
<style>
    .ai-wrap{display:flex;gap:0;height:calc(100vh - 140px);min-height:480px;background:var(--card);border:1px solid var(--line);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}

    /* Sidebar */
    .ai-side{width:260px;border-right:1px solid var(--line);display:flex;flex-direction:column;flex-shrink:0;background:var(--card);}
    .ai-side-head{padding:18px 16px 14px;border-bottom:1px solid var(--line);}
    .ai-side-head h2{font-size:16px;font-weight:700;margin-bottom:2px;}
    .ai-side-head p{font-size:12px;color:var(--muted);}
    .ai-side-topics{flex:1;overflow-y:auto;padding:10px 10px;}
    .ai-topic{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;cursor:pointer;transition:background .12s;font-size:13px;font-weight:500;color:var(--ink);margin-bottom:2px;}
    .ai-topic:hover{background:var(--teal-soft);}
    .ai-topic .ai-topic-icon{width:32px;height:32px;border-radius:9px;display:grid;place-items:center;font-size:15px;flex-shrink:0;}
    .ai-topic .ai-topic-icon.blue{background:var(--teal-soft);color:var(--teal);}
    .ai-topic .ai-topic-icon.amber{background:var(--amber-soft);color:var(--amber);}
    .ai-topic .ai-topic-icon.green{background:var(--green-soft);color:var(--green);}
    .ai-topic .ai-topic-icon.rose{background:var(--rose-soft);color:var(--rose);}
    .ai-topic .ai-topic-icon.purple{background:#f3eaff;color:var(--purple);}
    .ai-side-footer{padding:12px 14px;border-top:1px solid var(--line);font-size:11.5px;color:var(--muted);text-align:center;}

    /* Chat area */
    .ai-chat{flex:1;display:flex;flex-direction:column;min-width:0;}
    .ai-chat-head{padding:14px 20px;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:10px;}
    .ai-chat-head-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;display:grid;place-items:center;font-size:14px;font-weight:800;flex-shrink:0;}
    .ai-chat-head-info{flex:1;}
    .ai-chat-head-info strong{font-size:14px;display:block;}
    .ai-chat-head-info span{font-size:11.5px;color:var(--muted);}
    .ai-chat-head .ai-status{width:8px;height:8px;border-radius:50%;background:var(--green);flex-shrink:0;}
    .ai-clear{background:none;border:1px solid var(--line);border-radius:8px;padding:6px 12px;font-size:12px;color:var(--muted);cursor:pointer;font-family:inherit;transition:all .15s;}
    .ai-clear:hover{border-color:var(--rose);color:var(--rose);}

    /* Messages */
    .ai-messages{flex:1;overflow-y:auto;padding:20px;display:flex;flex-direction:column;gap:16px;}

    .ai-msg{display:flex;gap:10px;max-width:85%;animation:fadeInMsg .25s ease;}
    .ai-msg.user{align-self:flex-end;flex-direction:row-reverse;}
    .ai-msg-avatar{width:32px;height:32px;border-radius:50%;flex-shrink:0;display:grid;place-items:center;font-size:12px;font-weight:700;}
    .ai-msg.bot .ai-msg-avatar{background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;}
    .ai-msg.user .ai-msg-avatar{background:var(--ink);color:#fff;}
    .ai-msg-content{display:flex;flex-direction:column;gap:3px;}
    .ai-msg-meta{font-size:11px;color:var(--muted);display:flex;align-items:center;gap:6px;}
    .ai-msg.user .ai-msg-meta{justify-content:flex-end;}
    .ai-msg-bubble{padding:12px 16px;font-size:14px;line-height:1.6;border-radius:16px;word-break:break-word;}
    .ai-msg.bot .ai-msg-bubble{background:var(--paper);color:var(--ink);border-bottom-left-radius:4px;border:1px solid var(--line);}
    .ai-msg.user .ai-msg-bubble{background:var(--teal);color:#fff;border-bottom-right-radius:4px;}
    .ai-msg-bubble p{margin:0 0 8px;}
    .ai-msg-bubble p:last-child{margin-bottom:0;}
    .ai-msg-bubble ul,.ai-msg-bubble ol{margin:4px 0 8px 18px;padding:0;}
    .ai-msg-bubble li{margin-bottom:4px;}
    .ai-msg-bubble strong{font-weight:700;}
    .ai-msg-bubble code{background:rgba(0,0,0,.08);padding:1px 5px;border-radius:4px;font-size:13px;}
    .ai-msg.user .ai-msg-bubble code{background:rgba(255,255,255,.15);}
    .ai-msg-actions{display:flex;gap:6px;margin-top:4px;}
    .ai-msg-action{background:none;border:1px solid var(--line);border-radius:6px;padding:3px 8px;font-size:11px;color:var(--muted);cursor:pointer;font-family:inherit;transition:all .12s;}
    .ai-msg-action:hover{border-color:var(--teal);color:var(--teal);}

    /* Typing indicator */
    .ai-typing{display:flex;gap:10px;align-self:flex-start;max-width:85%;animation:fadeInMsg .25s ease;}
    .ai-typing .ai-msg-avatar{background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;}
    .ai-typing-dots{background:var(--paper);border:1px solid var(--line);border-radius:16px;border-bottom-left-radius:4px;padding:14px 18px;display:flex;gap:4px;align-items:center;}
    .ai-typing-dots span{width:7px;height:7px;border-radius:50%;background:var(--muted);animation:typingBounce 1.2s infinite;}
    .ai-typing-dots span:nth-child(2){animation-delay:.15s;}
    .ai-typing-dots span:nth-child(3){animation-delay:.3s;}
    @keyframes typingBounce{0%,60%,100%{transform:translateY(0);opacity:.4;}30%{transform:translateY(-5px);opacity:1;}}
    @keyframes fadeInMsg{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}

    /* Welcome state */
    .ai-welcome{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:30px;text-align:center;}
    .ai-welcome-icon{width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;display:grid;place-items:center;font-size:26px;font-weight:800;margin-bottom:16px;box-shadow:0 8px 24px rgba(76,111,255,.25);}
    .ai-welcome h3{font-size:18px;font-weight:700;margin-bottom:6px;}
    .ai-welcome p{font-size:13.5px;color:var(--muted);max-width:400px;line-height:1.5;}

    /* Quick prompts grid */
    .ai-prompts{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:20px;width:100%;max-width:520px;}
    .ai-prompt{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:12px 14px;text-align:left;cursor:pointer;transition:all .15s;font-size:13px;line-height:1.45;color:var(--ink);font-family:inherit;display:flex;align-items:flex-start;gap:8px;}
    .ai-prompt:hover{border-color:var(--teal);background:var(--teal-soft);transform:translateY(-1px);}
    .ai-prompt-icon{font-size:16px;flex-shrink:0;margin-top:1px;}

    /* Input area */
    .ai-input-wrap{border-top:1px solid var(--line);padding:14px 20px;display:flex;gap:10px;align-items:flex-end;background:var(--card);}
    .ai-input{flex:1;resize:none;min-height:42px;max-height:120px;padding:10px 14px;border-radius:12px;border:1px solid var(--line);font-size:14px;font-family:inherit;background:var(--paper);line-height:1.45;transition:border-color .15s;}
    .ai-input:focus{outline:none;border-color:var(--teal);}
    .ai-send{width:42px;height:42px;border-radius:12px;border:none;background:var(--teal);color:#fff;cursor:pointer;display:grid;place-items:center;font-size:18px;transition:background .15s;flex-shrink:0;}
    .ai-send:hover{background:var(--teal-ink);}
    .ai-send:disabled{opacity:.5;cursor:default;}

    @media (max-width:768px){
        .ai-side{display:none;}
        .ai-wrap{height:calc(100vh - 120px);}
        .ai-prompts{grid-template-columns:1fr;}
    }
</style>
@endpush

<div class="ai-wrap">
    {{-- Sidebar --}}
    <div class="ai-side">
        <div class="ai-side-head">
            <h2>AI Assistant</h2>
            <p>Your marketing co-pilot</p>
        </div>
        <div class="ai-side-topics">
            <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;padding:8px 12px 6px;">Quick Topics</div>
            @php
                $topics = [
                    ['icon' => '★', 'color' => 'amber', 'label' => 'Review Replies', 'prompt' => 'Help me write a professional reply to a negative Google review'],
                    ['icon' => '📝', 'color' => 'blue', 'label' => 'Google Posts', 'prompt' => 'Write a Google Business post announcing a weekend sale'],
                    ['icon' => '📈', 'color' => 'green', 'label' => 'Local SEO Tips', 'prompt' => 'Give me 5 actionable local SEO tips for my business'],
                    ['icon' => '💬', 'color' => 'purple', 'label' => 'Social Captions', 'prompt' => 'Write an engaging Instagram caption for a new product launch'],
                    ['icon' => '🎯', 'color' => 'rose', 'label' => 'Ad Copy', 'prompt' => 'Write Google Ads copy for a local restaurant'],
                ];
            @endphp
            @foreach($topics as $t)
                <div class="ai-topic" onclick="sendFromTopic('{{ addslashes($t['prompt']) }}')">
                    <div class="ai-topic-icon {{ $t['color'] }}">{{ $t['icon'] }}</div>
                    <span>{{ $t['label'] }}</span>
                </div>
            @endforeach

            <div style="font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;padding:16px 12px 6px;">Tools</div>
            @php
                $tools = [
                    ['icon' => '✦', 'color' => 'blue', 'label' => 'Rewrite Text', 'prompt' => 'Rewrite the following text to be more professional and engaging: '],
                    ['icon' => '📋', 'color' => 'green', 'label' => 'Content Calendar', 'prompt' => 'Create a 7-day social media content calendar for a local business'],
                    ['icon' => '🔍', 'color' => 'amber', 'label' => 'Keyword Ideas', 'prompt' => 'Suggest 10 local keywords for a dental clinic in Mumbai'],
                ];
            @endphp
            @foreach($tools as $t)
                <div class="ai-topic" onclick="sendFromTopic('{{ addslashes($t['prompt']) }}')">
                    <div class="ai-topic-icon {{ $t['color'] }}">{{ $t['icon'] }}</div>
                    <span>{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="ai-side-footer">Powered by AI · Uses 1 credit per message</div>
    </div>

    {{-- Chat area --}}
    <div class="ai-chat">
        <div class="ai-chat-head">
            <div class="ai-chat-head-avatar">R</div>
            <div class="ai-chat-head-info">
                <strong>ReviewFlow AI</strong>
                <span><span class="ai-status" style="display:inline-block;vertical-align:middle;margin-right:3px;"></span> Online · Ready to help</span>
            </div>
            <button type="button" class="ai-clear" onclick="clearChat()">Clear chat</button>
        </div>

        <div class="ai-messages" id="chat-messages">
            {{-- Welcome state (shown when no messages) --}}
            <div class="ai-welcome" id="welcome-state">
                <div class="ai-welcome-icon">R</div>
                <h3>How can I help you today?</h3>
                <p>I can write review replies, draft social posts, generate marketing ideas, create ad copy, and much more.</p>

                <div class="ai-prompts">
                    <button type="button" class="ai-prompt" onclick="sendFromPrompt(this)">
                        <span class="ai-prompt-icon">★</span>
                        <span>Write a reply to a 5-star review thanking the customer</span>
                    </button>
                    <button type="button" class="ai-prompt" onclick="sendFromPrompt(this)">
                        <span class="ai-prompt-icon">📝</span>
                        <span>Create a festive Diwali offer post for my business</span>
                    </button>
                    <button type="button" class="ai-prompt" onclick="sendFromPrompt(this)">
                        <span class="ai-prompt-icon">📈</span>
                        <span>How do I get more Google reviews organically?</span>
                    </button>
                    <button type="button" class="ai-prompt" onclick="sendFromPrompt(this)">
                        <span class="ai-prompt-icon">🎯</span>
                        <span>5 local SEO tips for a salon or spa</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="ai-input-wrap">
            <textarea class="ai-input" id="ai-input" rows="1" placeholder="Ask me anything about marketing..." onkeydown="handleKey(event)" oninput="autoGrow(this)"></textarea>
            <button class="ai-send" id="ai-send" onclick="sendMsg()" title="Send">&#10148;</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrf = document.querySelector('meta[name=csrf-token]').content;
const messagesEl = document.getElementById('chat-messages');
const welcomeEl = document.getElementById('welcome-state');
const inputEl = document.getElementById('ai-input');
const sendBtn = document.getElementById('ai-send');
let sending = false;

function handleKey(e){
    if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); sendMsg(); }
}

function autoGrow(el){
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

function hideWelcome(){
    if(welcomeEl) welcomeEl.style.display = 'none';
}

function sendFromPrompt(btn){
    const text = btn.querySelector('span:last-child').textContent;
    inputEl.value = text;
    sendMsg();
}

function sendFromTopic(prompt){
    inputEl.value = prompt;
    inputEl.focus();
    sendMsg();
}

function formatAiText(text){
    let html = text
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code>$1</code>');

    const lines = html.split('\n');
    let result = '';
    let inList = false;

    for(const line of lines){
        const trimmed = line.trim();
        const listMatch = trimmed.match(/^(\d+\.|[-•*])\s+(.*)/);
        if(listMatch){
            if(!inList){ result += '<ul style="margin:6px 0 6px 4px;padding-left:16px;">'; inList = true; }
            result += '<li>' + listMatch[2] + '</li>';
        } else {
            if(inList){ result += '</ul>'; inList = false; }
            if(trimmed === '') continue;
            result += '<p>' + trimmed + '</p>';
        }
    }
    if(inList) result += '</ul>';
    return result;
}

function addMessage(text, who, formatted){
    hideWelcome();
    const now = new Date();
    const timeStr = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');

    const msg = document.createElement('div');
    msg.className = 'ai-msg ' + who;
    msg.innerHTML = `
        <div class="ai-msg-avatar">${who === 'bot' ? 'R' : '{{ strtoupper(substr(auth()->user()->name ?? "Y", 0, 1)) }}'}</div>
        <div class="ai-msg-content">
            <div class="ai-msg-meta">
                <span>${who === 'bot' ? 'ReviewFlow AI' : 'You'}</span>
                <span>${timeStr}</span>
            </div>
            <div class="ai-msg-bubble">${formatted ? text : '<p>' + text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + '</p>'}</div>
            ${who === 'bot' ? '<div class="ai-msg-actions"><button class="ai-msg-action" onclick="copyMsg(this)">📋 Copy</button></div>' : ''}
        </div>
    `;
    messagesEl.appendChild(msg);
    messagesEl.scrollTop = messagesEl.scrollHeight;
    return msg;
}

function showTyping(){
    hideWelcome();
    const el = document.createElement('div');
    el.className = 'ai-typing';
    el.id = 'typing-indicator';
    el.innerHTML = `
        <div class="ai-msg-avatar" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#4c6fff,#6b8afd);color:#fff;display:grid;place-items:center;font-size:12px;font-weight:700;">R</div>
        <div class="ai-typing-dots"><span></span><span></span><span></span></div>
    `;
    messagesEl.appendChild(el);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function hideTyping(){
    const el = document.getElementById('typing-indicator');
    if(el) el.remove();
}

function copyMsg(btn){
    const bubble = btn.closest('.ai-msg-content').querySelector('.ai-msg-bubble');
    const text = bubble.innerText;
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = '✓ Copied';
        setTimeout(() => { btn.textContent = '📋 Copy'; }, 1500);
    });
}

async function sendMsg(){
    if(sending) return;
    const message = inputEl.value.trim();
    if(!message) return;

    inputEl.value = '';
    inputEl.style.height = 'auto';
    addMessage(message, 'user', false);

    sending = true;
    sendBtn.disabled = true;
    showTyping();

    try{
        const res = await fetch("{{ route('ai.send') }}", {
            method:'POST',
            headers:{'X-CSRF-TOKEN': csrf, 'Content-Type':'application/json'},
            body: JSON.stringify({message})
        });
        const data = await res.json();
        hideTyping();

        if(data.error){
            addMessage(data.error, 'bot', true);
        } else {
            const html = formatAiText(data.reply || 'No response.');
            addMessage(html, 'bot', true);
        }
    } catch(e){
        hideTyping();
        addMessage('<p>Something went wrong. Please try again.</p>', 'bot', true);
    } finally {
        sending = false;
        sendBtn.disabled = false;
        inputEl.focus();
    }
}

function clearChat(){
    messagesEl.innerHTML = '';
    messagesEl.appendChild(welcomeEl);
    welcomeEl.style.display = '';
}

inputEl.focus();
</script>
@endpush
@endsection
