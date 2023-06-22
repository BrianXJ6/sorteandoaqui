<?php

namespace App\Http\Controllers\Auth\Admin;

use App\Http\Requests\Admin\Auth\{
    SignInRequest,
    SignUpRequest,
    PasswordUpdateRequest,
    SendResetLinkEmailRequest,
};

use Illuminate\Http\JsonResponse;
use App\Services\Admin\AuthService;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new Admin Auth Controller instance
     *
     * @param \App\Services\Admin\AuthService $AuthService
     */
    public function __construct(private AuthService $AuthService)
    {
        //
    }

    /**
     * Handle signIn via web application
     *
     * @param \App\Http\Requests\Admin\SignInRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signInWeb(SignInRequest $request): JsonResponse
    {
        $this->AuthService->signInWeb($request->credentials());

        return new JsonResponse([
            'message' => __('messages.admin.signin'),
            'redirect' => redirect()->intended(route('web.admin.dashboard.home'))->getTargetUrl(),
        ]);
    }

    /**
     * Handle signIn via api application
     *
     * @param \App\Http\Requests\Admin\SignInRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signInApi(SignInRequest $request): JsonResponse
    {
        $result = $this->AuthService->signInApi($request->credentials());

        return new JsonResponse([
            'message' => __('messages.admin.signin'),
            'token' => $result['token'],
            'admin' => $result['admin'],
        ]);
    }

    /**
     * Register new admin to the database via web
     *
     * @param \App\Http\Requests\Admin\SignUpRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUpWeb(SignUpRequest $request): JsonResponse
    {
        $this->AuthService->signUpWeb($request->data());

        return new JsonResponse([
            'message' => __('messages.admin.signup'),
            'redirect' => route('web.admin.dashboard.home')
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Register new admin to the database via api
     *
     * @param \App\Http\Requests\Admin\SignUpRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUpApi(SignUpRequest $request): JsonResponse
    {
        $result = $this->AuthService->signUpApi($request->data());

        return new JsonResponse([
            'message' => __('messages.admin.signup'),
            'token' => $result['token'],
            'admin' => $result['admin'],
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Handle logout for web application
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOutWeb(): JsonResponse
    {
        $this->AuthService->signOutWeb();

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Handle logout for api application
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOutApi(): JsonResponse
    {
        $this->AuthService->signOutApi();

        return new JsonResponse(status: JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Handle admin request to send reset link via email
     *
     * @param \App\Http\Requests\Admin\SendResetLinkEmailRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(SendResetLinkEmailRequest $request): JsonResponse
    {
        $this->AuthService->sendResetLinkEmail($request->safe()->only('email'));
        $response = ['message' => __('passwords.sent')];

        return new JsonResponse($request->hasSession() ? array_merge($response, ['redirect' => route('web.admin.signIn')]) : $response);
    }

    /**
     * Handle request to update lost password
     *
     * @param \App\Http\Requests\Admin\PasswordUpdateRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordUpdate(PasswordUpdateRequest $request): JsonResponse
    {
        $this->AuthService->passwordUpdate($request->safe()->all());

        return new JsonResponse(['message'  => __('passwords.reset'), 'redirect' => route('web.admin.signIn')]);
    }
}
