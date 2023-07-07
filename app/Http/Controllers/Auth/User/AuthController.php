<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Requests\User\Auth\{
    SignInRequest,
    SignUpRequest,
    PasswordUpdateRequest,
    MarkEmailVerifyRequest,
    SendResetLinkEmailRequest,
};

use Illuminate\Http\JsonResponse;
use App\Services\User\AuthService;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new User Auth Controller instance
     *
     * @param \App\Services\User\AuthService $AuthService
     */
    public function __construct(private AuthService $AuthService)
    {
        //
    }

    /**
     * Handle signIn via web application
     *
     * @param \App\Http\Requests\User\SignInRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signInWeb(SignInRequest $request): JsonResponse
    {
        $user = $this->AuthService->signInWeb($request->credentials());

        return new JsonResponse([
            'message' => __('messages.user.signin', ['Nick' => $user->nick]),
            'redirect' => redirect()->intended(route('web.user.dashboard.home'))->getTargetUrl(),
        ]);
    }

    /**
     * Handle signIn via api application
     *
     * @param \App\Http\Requests\User\SignInRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signInApi(SignInRequest $request): JsonResponse
    {
        $result = $this->AuthService->signInApi($request->credentials());

        return new JsonResponse([
            'message' => __('messages.user.signin', ['Nick' => $result['user']->nick]),
            'token' => $result['token'],
            'user' => $result['user'],
        ]);
    }

    /**
     * Register new customer to the database via web
     *
     * @param \App\Http\Requests\User\SignUpRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUpWeb(SignUpRequest $request): JsonResponse
    {
        $this->AuthService->signUpWeb($request->data());

        return new JsonResponse(['message' => __('messages.user.signup'), 'redirect' => route('web.user.dashboard.home')], JsonResponse::HTTP_CREATED);
    }

    /**
     * Register new customer to the database via api
     *
     * @param \App\Http\Requests\User\SignUpRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function signUpApi(SignUpRequest $request): JsonResponse
    {
        $result = $this->AuthService->signUpApi($request->data());

        return new JsonResponse([
            'message' => __('messages.user.signup'),
            'token' => $result['token'],
            'user' => $result['user'],
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
     * Handle user request to send reset link via email
     *
     * @param \App\Http\Requests\User\SendResetLinkEmailRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(SendResetLinkEmailRequest $request): JsonResponse
    {
        $this->AuthService->sendResetLinkEmail($request->safe()->only('email'));
        $response = ['message' => __('passwords.sent')];

        return new JsonResponse($request->hasSession() ? array_merge($response, ['redirect' => route('web.user.signIn')]) : $response);
    }

    /**
     * Handle request to update lost password
     *
     * @param \App\Http\Requests\User\PasswordUpdateRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordUpdate(PasswordUpdateRequest $request): JsonResponse
    {
        $this->AuthService->passwordUpdate($request->credentials());

        return new JsonResponse(['message'  => __('passwords.reset'), 'redirect' => route('web.user.signIn')]);
    }

    /**
     * Handle request for a new verification email
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailVerifyResend(): JsonResponse
    {
        $this->AuthService->emailVerifyResend();

        return new JsonResponse(['message' => __('messages.user.email.verify.resend_success')]);
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param \App\Http\Requests\User\MarkEmailVerifyRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function emailVerify(MarkEmailVerifyRequest $request): JsonResponse
    {
        $this->AuthService->emailVerify($request->user());

        return new JsonResponse(['message' => __('messages.user.email.verify.success')]);
    }
}
