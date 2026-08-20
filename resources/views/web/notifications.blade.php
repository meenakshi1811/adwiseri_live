@extends('web.layout.main')

@section('main-section')
    @include('partials.notifications_page', [
        'messagesRoute' => route('communications'),
        'settingsRoute' => route('my_settings') . '#notifications',
        'notificationsSubtitle' => 'System alerts and updates based on your preferences',
    ])
@endsection
