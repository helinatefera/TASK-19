<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Api\BaseController;
use App\Models\Notification;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * GET /api/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()
            ->where('user_id', $request->user()->id)
            ->notExpired()
            ->latest();

        if ($request->query('read') === 'true') {
            $query->whereNotNull('read_at');
        } elseif ($request->query('read') === 'false') {
            $query->unread();
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        return $this->paginated($query);
    }

    /**
     * GET /api/notifications/unread-count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::query()
            ->where('user_id', $request->user()->id)
            ->notExpired()
            ->unread()
            ->count();

        return $this->success(['unread_count' => $count]);
    }

    /**
     * POST /api/notifications/{notification}/read
     */
    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        $this->authorize('markRead', $notification);

        $this->notificationService->markAsRead($notification);

        return $this->success($notification->refresh());
    }

    /**
     * POST /api/notifications/read-all
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return $this->success(['message' => 'All notifications marked as read.']);
    }
}
