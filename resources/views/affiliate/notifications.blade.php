@extends('affiliate.layout.main')

@section('main-section')
    @include('partials.notifications_page', [
        'messagesRoute' => route('support_affiliate'),
        'messagesLabel' => 'Support',
        'settingsRoute' => null,
        'notificationsSubtitle' => 'Platform alerts and promotional updates',
    ])
@endsection
