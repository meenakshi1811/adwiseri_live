<?php

namespace App\Http\Controllers;

use App\Models\Internal_communications;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** @var NotificationService */
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    protected function resolveUser()
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

    public function index()
    {
        $user = $this->resolveUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $page = 'notifications';
        $notifications = $this->notificationService->recentNotifications($user, 100);
        $bellCount = $this->notificationService->bellCount($user);
        $envelopeCount = $this->notificationService->envelopeCount($user);

        $userType = strtolower((string) $user->user_type);
        $layout = 'web.layout.main';
        if ($userType === 'admin') {
            $layout = 'admin.layout.main';
        } elseif ($userType === 'affiliate') {
            $layout = 'affiliate.layout.main';
        }

        $messagesRoute = $this->notificationService->messagesRouteForUser($user);
        $messagesLabel = $userType === 'affiliate' ? 'Support' : 'Messages';
        $settingsRoute = route('my_settings') . '#notifications';
        $notificationsSubtitle = 'System alerts and updates based on your preferences';
        $canClearNotifications = $this->notificationService->resolveAudience($user) === 'subscriber';

        if ($userType === 'admin') {
            $settingsRoute = route('settings') . '#notifications';
            $notificationsSubtitle = 'Platform alerts and updates';
        } elseif ($userType === 'affiliate') {
            $settingsRoute = null;
            $notificationsSubtitle = 'Platform alerts and promotional updates';
        }

        return view('notifications.index', compact(
            'user',
            'page',
            'notifications',
            'bellCount',
            'envelopeCount',
            'layout',
            'messagesRoute',
            'messagesLabel',
            'settingsRoute',
            'notificationsSubtitle',
            'canClearNotifications'
        ));
    }

    protected function subscriberCanClearNotifications(User $user): bool
    {
        return $this->notificationService->resolveAudience($user) === 'subscriber';
    }

    public function deleteNotification(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user || !$this->subscriberCanClearNotifications($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $ok = $this->notificationService->deleteNotification($user, (int) $id);

        return response()->json([
            'success' => $ok,
            'bellCount' => $this->notificationService->bellCount($user),
        ], $ok ? 200 : 404);
    }

    public function deleteSelectedNotifications(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user || !$this->subscriberCanClearNotifications($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $deleted = $this->notificationService->deleteNotifications($user, $validated['ids']);

        return response()->json([
            'success' => $deleted > 0,
            'deleted' => $deleted,
            'bellCount' => $this->notificationService->bellCount($user),
        ]);
    }

    public function clearAllNotifications(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user || !$this->subscriberCanClearNotifications($user)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $deleted = $this->notificationService->clearAllNotifications($user);

        return response()->json([
            'success' => true,
            'deleted' => $deleted,
            'bellCount' => 0,
        ]);
    }

    public function adminIndex()
    {
        $user = Auth::user();
        if (!$user || strtolower((string) $user->user_type) !== 'admin') {
            return redirect()->route('admin');
        }

        return $this->index();
    }

    public function markRead(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $ok = $this->notificationService->markNotificationRead($user, (int) $id);

        return response()->json([
            'success' => $ok,
            'bellCount' => $this->notificationService->bellCount($user),
        ]);
    }

    public function markAllRead(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $count = $this->notificationService->markAllNotificationsRead($user);

        return response()->json([
            'success' => true,
            'marked' => $count,
            'bellCount' => 0,
        ]);
    }

    public function markAllMessagesRead(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $this->notificationService->markAllMessagesRead($user);

        return response()->json([
            'success' => true,
            'envelopeCount' => 0,
        ]);
    }

    public function markMessageRead(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $message = Internal_communications::find($id);
        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }

        if (!$this->notificationService->userIsMessageRecipient($user, $message)
            && (int) $message->send_by !== (int) $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->notificationService->markMessageRead($user, (int) $message->id);

        return response()->json([
            'success' => true,
            'envelopeCount' => $this->notificationService->envelopeCount($user),
        ]);
    }

    public function deleteMessage(Request $request, $id)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        $message = Internal_communications::find($id);
        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message not found'], 404);
        }

        $isAdmin = strtolower((string) $user->user_type) === 'admin';
        $isOwner = $this->notificationService->userSentMessage($user, $message)
            || (int) $message->user_id === (int) $user->id;

        if (!$isAdmin && !$isOwner) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json([
            'success' => true,
            'envelopeCount' => $this->notificationService->envelopeCount($user),
        ]);
    }

    public function savePreferences(Request $request)
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (strtolower((string) $user->user_type) === 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Notification filters are not applicable for admin accounts.',
            ], 422);
        }

        $definitions = $this->notificationService->typeDefinitionsForUser($user);
        $input = $request->only(array_keys($definitions));

        $this->notificationService->savePreferences($user, $input);

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences saved successfully.',
        ]);
    }

    public function counts()
    {
        $user = $this->resolveUser();
        if (!$user) {
            return response()->json(['success' => false], 401);
        }

        return response()->json([
            'success' => true,
            'bellCount' => $this->notificationService->bellCount($user),
            'envelopeCount' => $this->notificationService->envelopeCount($user),
        ]);
    }

    public function adminSendNotification(Request $request)
    {
        $admin = Auth::user();
        if (!$admin || strtolower((string) $admin->user_type) !== 'admin') {
            return redirect()->route('admin');
        }

        $validated = $request->validate([
            'recipient_group' => 'required|in:Subscribers,Users',
            'notification_type' => 'required|string',
            'title' => 'required|string|max:200',
            'body' => 'nullable|string|max:2000',
            'link' => 'nullable|string|max:500',
            'sendto' => 'required|array|min:1',
            'sendto.*' => 'required',
        ]);

        if (!NotificationService::isValidNotificationType($validated['notification_type'])) {
            return back()->withErrors([
                'notification_type' => 'Invalid notification type selected: ' . $validated['notification_type'],
            ])->withInput();
        }

        $sendto = $validated['sendto'];
        $recipientIds = [];

        if ($validated['recipient_group'] === 'Subscribers') {
            if (in_array('All Subscribers', $sendto, true)) {
                $recipientIds = User::where('user_type', 'Subscriber')->pluck('id')->all();
            } else {
                $recipientIds = User::where('user_type', 'Subscriber')
                    ->whereIn('id', array_map('intval', $sendto))
                    ->pluck('id')
                    ->all();
            }
        } else {
            if (in_array('All Users', $sendto, true)) {
                $recipientIds = User::where('user_type', 'User')->pluck('id')->all();
            } else {
                $recipientIds = User::where('user_type', 'User')
                    ->whereIn('id', array_map('intval', $sendto))
                    ->pluck('id')
                    ->all();
            }
        }

        if (empty($recipientIds)) {
            return back()->withErrors(['sendto' => 'Please select at least one recipient.'])->withInput();
        }

        $recipients = User::whereIn('id', $recipientIds)->get();
        $sentCount = $this->notificationService->notifyUsers(
            $recipients,
            $validated['notification_type'],
            $validated['title'],
            $validated['body'] ?? null,
            $validated['link'] ?? null,
            ['sent_by_admin' => (int) $admin->id]
        );

        if ($sentCount === 0) {
            $typeLabel = NotificationService::notificationTypeLabel($validated['notification_type']);

            return back()->withErrors([
                'sendto' => 'No recipients have "' . $typeLabel . '" enabled in their notification settings.',
            ])->withInput();
        }

        return redirect()->to(route('settings') . '#notifications')
            ->with('notification_sent', 'Notification sent to ' . $sentCount . ' user(s) who have this alert type enabled.');
    }
}
