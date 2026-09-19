@php
    $timeAgo = $review->review_time ? $review->review_time->diffForHumans() : '';
    $replyTimeAgo = $review->replied_at ? $review->replied_at->diffForHumans() : '';
    $sent = $review->sentiment;
    $badgeCls = $sent === 'POSITIVE' ? 'teal' : ($sent === 'NEGATIVE' ? 'rose' : 'gray');
    $commentLength = mb_strlen($review->comment ?? '');
@endphp
<div class="rv">
    <div class="rv-top">
        @if($review->reviewer_photo)
            <img src="{{ $review->reviewer_photo }}" alt="{{ $review->reviewer_name }}" class="rv-photo" referrerpolicy="no-referrer">
        @else
            <div class="rv-avatar">{{ strtoupper(substr($review->reviewer_name, 0, 1)) }}</div>
        @endif
        <div class="rv-meta">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                <span class="rv-name">{{ $review->reviewer_name }}</span>
                @if($sent)<span class="badge {{ $badgeCls }}" style="font-size:10.5px;padding:2px 8px;">{{ ucfirst(strtolower($sent)) }}</span>@endif
            </div>
            <div class="rv-stars">
                @for($i = 1; $i <= 5; $i++)
                    <span @if($i > $review->star_rating)class="empty"@endif>★</span>
                @endfor
            </div>
        </div>
        <span class="rv-time">{{ $timeAgo }}</span>
    </div>

    @if($review->comment)
        <div class="rv-comment {{ $commentLength > 200 ? 'clamped' : '' }}" id="comment-{{ $review->id }}">{{ $review->comment }}</div>
        @if($commentLength > 200)
            <span class="rv-readmore">Read more</span>
        @endif
    @endif

    {{-- Owner reply --}}
    @if($review->reply_text)
        <div class="rv-reply">
            <div class="rv-reply-head">
                <div class="rv-reply-avatar">{{ $locationInitial }}</div>
                <span class="rv-reply-label">Owner Reply</span>
                @if($replyTimeAgo)<span class="rv-reply-time">{{ $replyTimeAgo }}</span>@endif
            </div>
            <div class="rv-reply-text">{{ $review->reply_text }}</div>
        </div>
    @else
        <div class="rv-reply-form">
            @if($review->draft_reply)
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;font-size:11.5px;font-weight:600;color:var(--amber);">
                    <span>✦</span> AI Draft Ready — review and post
                </div>
            @endif
            <form method="POST" action="{{ route('reviews.reply', $review) }}" id="reply-form-{{ $review->id }}">
                @csrf
                <textarea name="reply_text" id="reply-{{ $review->id }}" rows="3" placeholder="Write a reply to {{ $review->reviewer_name }}…">{{ $review->draft_reply }}</textarea>
                <div class="rv-form-actions">
                    <button type="button" class="btn btn-ghost" style="padding:8px 14px;font-size:12.5px;" onclick="genReply({{ $review->id }}, this)">✦ AI Draft</button>
                    <button type="submit" class="btn" style="padding:8px 16px;font-size:12.5px;">Post Reply</button>
                </div>
            </form>
        </div>
    @endif
</div>
