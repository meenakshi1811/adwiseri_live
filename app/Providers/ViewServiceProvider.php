<?php
namespace App\Providers;

use Illuminate\Support\Facades\View;
use App\View\Composers\NotificationViewComposer;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer([
            'web.layout.footer',
            'web.layout.auth_nav',
            'affiliate.layout.auth_nav',
            'admin.*',
        ], NotificationViewComposer::class);
    }

    public function register()
    {
        //
    }
}

