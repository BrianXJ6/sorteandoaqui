<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use App\Services\Admin\AdminService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Open\WebContactRequest;

class ApiController extends Controller
{
    /**
     * Create a new Api Controller instance
     *
     * @param \App\Services\Admin\AdminService $adminService
     */
    public function __construct(private AdminService $adminService)
    {
        //
    }

    /**
     * Contact form from web
     *
     * @param \App\Http\Requests\Open\WebContactRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function webContact(WebContactRequest $request): JsonResponse
    {
        $this->adminService->webContact($request->data());

        return new JsonResponse(['message' => __('messages.contact')]);
    }
}
