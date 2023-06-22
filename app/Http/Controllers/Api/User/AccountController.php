<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Requests\User\Account\{
    UpdateAccessDataRequest,
    UpdatePersonalDataRequest,
};

use Illuminate\Http\JsonResponse;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;

class AccountController extends Controller
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
     * Update the logged-in user's data.
     *
     * @param \App\Http\Requests\User\Account\UpdatePersonalDataRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePersonalData(UpdatePersonalDataRequest $request): JsonResponse
    {
        $user = $this->service->updatePersonalData($request->data());

        return new JsonResponse(['user' => $user, 'message' => __('messages.update')]);
    }

    /**
     * Update the logged-in user's access data.
     *
     * @param \App\Http\Requests\User\Account\UpdateAccessDataRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAccessData(UpdateAccessDataRequest $request)
    {
        $this->service->update($request->safe()->only('password'), auth()->user());

        return new JsonResponse(['message' => __('messages.update')]);
    }
}
