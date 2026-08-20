@include('partials.communication_nav_styles')
@php
    $activeTab = $activeTab ?? '';
@endphp
<div class="comm-nav-wrap">
    <div class="comm-nav-tabs">
        <button type="button" class="comm-nav-tab {{ $activeTab === 'messaging' ? 'active' : '' }}" onclick="window.location.href='{{ route('messaging') }}'">
            <i class="fa-solid fa-comment-dots"></i>
            Messaging
        </button>
        <button type="button" class="comm-nav-tab {{ $activeTab === 'meeting_notes' ? 'active' : '' }}" onclick="window.location.href='{{ route('client_discussion') }}'">
            <i class="fa-solid fa-handshake"></i>
            Meeting Notes
        </button>
        <button type="button" class="comm-nav-tab {{ $activeTab === 'communication' ? 'active' : '' }}" onclick="window.location.href='{{ route('communications') }}'">
            <i class="fa-solid fa-inbox"></i>
            Communication
        </button>
        <button type="button" class="comm-nav-tab {{ $activeTab === 'email_broadcast' ? 'active' : '' }}" onclick="window.location.href='{{ route('email_broadcast') }}'">
            <i class="fa-solid fa-paper-plane"></i>
            Email Broadcast
        </button>
    </div>
</div>
