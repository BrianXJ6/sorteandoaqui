<?php

namespace App\Http\Controllers\Api\User;

use Illuminate\Http\JsonResponse;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;

class NotificationsController extends Controller
{
    /**
     * Create a new Account Controller instance
     *
     * @param \App\Services\User\UserService $service
     */
    public function __construct(private UserService $service)
    {
        //
    }

    /**
     * Get list of notifications
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        return new JsonResponse(['notifications' => $this->service->notificationsList()]);
    }

    /**
     * Mark notification as read
     *
     * @param string $notificationId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markRead(string $notificationId): JsonResponse
    {
        $this->service->markRead($notificationId);

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }
}
