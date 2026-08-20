<?php

namespace App\View\Composers;

use App\Models\Contactus;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationViewComposer
{
    /** @var NotificationService */
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function compose(View $view): void
    {
        $contact = Contactus::first();
        $bellCount = 0;
        $envelopeCount = 0;
        $notificationsRoute = '#';
        $messagesRoute = '#';

        $user = $this->resolveUser();
        if ($user) {
            $bellCount = $this->notificationService->bellCount($user);
            $envelopeCount = $this->notificationService->envelopeCount($user);
            $notificationsRoute = $this->notificationService->notificationsRouteForUser($user);
            $messagesRoute = $this->notificationService->messagesRouteForUser($user);
        }

        $view->with([
            'contact' => $contact,
            'bellCount' => $bellCount,
            'envelopeCount' => $envelopeCount,
            'notificationsRoute' => $notificationsRoute,
            'messagesRoute' => $messagesRoute,
            'totalTickets' => $bellCount,
        ]);
    }

    protected function resolveUser(): ?User
    {
        $user = Auth::user();
        if ($user) {
            return $user;
        }

        $affiliate = Auth::guard('affiliates')->user();
        if ($affiliate) {
            return User::where('email', $affiliate->email)->first();
        }

        return null;
    }
}
