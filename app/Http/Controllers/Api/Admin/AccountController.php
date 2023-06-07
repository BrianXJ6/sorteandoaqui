<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Requests\Admin\Account\{
    UpdateAccessDataRequest,
    UpdatePersonalDataRequest,
};

use Illuminate\Http\JsonResponse;
use App\Services\Admin\AdminService;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    /**
     * Create a new Account Controller instance
     *
     * @param \App\Services\Admin\AdminService $service
     */
    public function __construct(private AdminService $service)
    {
        //
    }

    /**
     * Update the logged-in admin's data.
     *
     * @param \App\Http\Requests\Admin\Account\UpdatePersonalDataRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePersonalData(UpdatePersonalDataRequest $request): JsonResponse
    {
        $admin = $this->service->updatePersonalData($request->data());

        return new JsonResponse(['admin' => $admin, 'message' => __('messages.update')]);
    }

    /**
     * Update the logged-in admin's access data.
     *
     * @param \App\Http\Requests\Admin\Account\UpdateAccessDataRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccessData(UpdateAccessDataRequest $request)
    {
        $this->service->update($request->safe()->only('password'), auth()->user());

        return new JsonResponse(['message' => __('messages.update')]);
    }
}
