<?php

namespace App\Services\User;

use Illuminate\Auth\Events\{
    Verified,
    Registered,
    PasswordReset,
};

use App\Models\User;
use App\Data\User\SignUpData;
use Illuminate\Auth\AuthManager;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Passwords\PasswordBrokerManager;

class AuthService
{
    /**
     * Create a new User Auth Service instance
     *
     * @param \Illuminate\Auth\AuthManager $auth
     * @param \App\Repositories\UserRepository $userRepository
     * @param \Illuminate\Auth\Passwords\PasswordBrokerManager $password,
     */
    public function __construct(
        private AuthManager $auth,
        private UserRepository $userRepository,
        private PasswordBrokerManager $password,
    ) {
        $this->auth->setDefaultDriver('user');
        $this->password->setDefaultDriver('users');
    }

    /**
     * Handle login via web application
     *
     * @param array $credentials
     *
     * @return \App\Models\User
     */
    public function signinWeb(array $credentials): User
    {
        $user = $this->signin($credentials);
        $this->sessionRegenerate();
        $this->confirmPassword();

        return $user;
    }

    /**
     * Handle login via api application
     *
     * @param array $credentials
     *
     * @return array
     */
    public function signinApi(array $credentials): array
    {
        $user = $this->signin($credentials);
        $user->tokens()->delete();

        return ['user' => $user, 'token' => $user->createToken('api', ['user'])->plainTextToken];
    }

    /**
     * Handle the common process for login
     *
     * @param array $credentials
     *
     * @return \App\Models\User
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function signin(array $credentials): User
    {
        if (!$this->auth->attempt($credentials))
            throw ValidationException::withMessages(['signin' => [__('auth.failed')]]);

        $user = $this->auth->user();
        $this->userRepository->setLastLogin($user);

        return $user;
    }

    /**
     * Register new customer to the database via web
     *
     * @param \App\Data\User\SignUpData $data
     *
     * @return void
     */
    public function signUpWeb(SignUpData $data): void
    {
        $this->signup($data);
        $this->sessionRegenerate();
        $this->confirmPassword();
    }

    /**
     * Register new customer to the database via api
     *
     * @param \App\Data\User\SignUpData $data
     *
     * @return array
     */
    public function signUpApi(SignUpData $data): array
    {
        $user = $this->signup($data);
        $user->tokens()->delete();

        return ['user' => $user, 'token' => $user->createToken('api', ['user'])->plainTextToken];
    }

    /**
     * Handle the common process for User SignUp
     *
     * @param \App\Data\User\SignUpData $data
     *
     * @return \App\Models\User
     */
    protected function signup(SignUpData $data): User
    {
        $userData = $data->except('referralCode')->all();

        if (!empty($data->referralCode)) {
            $referred = $this->userRepository->getUserByReferralCode($data->referralCode);
            $userData = array_merge($data->except('referralCode')->all(), ['referred_id' => $referred->id]);
        }

        $user = $this->userRepository->signup($userData);
        event(new Registered($user));
        $this->auth->login($user);

        return $user;
    }

    /**
     * Handle sigOut for web application
     *
     * @return void
     */
    public function signOutWeb(): void
    {
        $this->auth->logout();
        $this->sessionRegenerate();
        request()->session()->forget('auth.user');
    }

    /**
     * Handle sigOut for api application
     *
     * @return void
     */
    public function signOutApi(): void
    {
        $this->auth->user()->currentAccessToken()->delete();
    }

    /**
     * Handle user request to send reset link via email
     *
     * @param array $credentials
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendResetLinkEmail(array $credentials): void
    {
        $response = $this->password->broker()->sendResetLink($credentials);

        throw_if($response != Password::RESET_LINK_SENT, ValidationException::withMessages(['message' => [__($response)]]));
    }

    /**
     * Handle request to update lost password
     *
     * @param array $credentials
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function passwordUpdate(array $credentials): void
    {
        $response = $this->password->broker()->reset($credentials, function ($user, $password) {
            $this->userRepository->updatePassword($password, $user);
            event(new PasswordReset($user));
            $this->auth->login($user);
        });

        throw_if($response != Password::PASSWORD_RESET, ValidationException::withMessages(['message' => [__($response)]]));
    }

    /**
     * Handle request for a new verification email
     *
     * @return void
     */
    public function emailVerifyResend(): void
    {
        /** @var \App\Models\User */
        $user = $this->auth->user();
        throw_if($user->hasVerifiedEmail(), ValidationException::withMessages(['message' => [__('messages.user.email.verify.resend_error')]]));
        $user->sendEmailVerificationNotification();
    }

    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param \App\Models\User $user
     *
     * @return void
     */
    public function emailVerify(User $user): void
    {
        if ($user->markEmailAsVerified()) event(new Verified($user));
    }

    /**
     * Check if session exists
     *
     * @return bool
     */
    protected function hasSession(): bool
    {
        return request()->hasSession();
    }

    /**
     * Handle regeneration sessions
     *
     * @return void
     */
    protected function sessionRegenerate(): void
    {
        session()->regenerate();
        session()->regenerateToken();
    }

    /**
     * Handle confirm the password
     *
     * @return void
     */
    public function confirmPassword(): void
    {
        session()->put('auth.user.password_confirmed_at', time());
    }
}
