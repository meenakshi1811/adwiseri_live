@extends('admin.layout.main')

@section('main-section')
    @include('partials.notifications_page', [
        'messagesRoute' => route('communication'),
        'settingsRoute' => route('settings') . '#notifications',
        'notificationsSubtitle' => 'Platform alerts and updates',
    ])
@endsection
