<?php

namespace App\Services\Admin;

use App\Models\Admin;
use App\Data\Admin\SignUpData;
use Illuminate\Auth\AuthManager;
use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Passwords\PasswordBrokerManager;

class AuthService
{
    /**
     * Create a new Admin Auth Service instance
     *
     * @param \Illuminate\Auth\AuthManager $auth
     * @param \App\Repositories\AdminRepository $adminRepository
     * @param \Illuminate\Auth\Passwords\PasswordBrokerManager $password,
     */
    public function __construct(
        private AuthManager $auth,
        private AdminRepository $adminRepository,
        private PasswordBrokerManager $password,
    ) {
        $this->auth->setDefaultDriver('admin');
        $this->password->setDefaultDriver('admins');
    }

    /**
     * Handle login via web application
     *
     * @param array $credentials
     *
     * @return void
     */
    public function signinWeb(array $credentials): void
    {
        $this->signin($credentials);
        $this->sessionRegenerate();
        $this->confirmPassword();
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
        $admin = $this->signin($credentials);
        $admin->tokens()->delete();

        return ['admin' => $admin, 'token' => $admin->createToken('api', ['admin'])->plainTextToken];
    }

    /**
     * Handle the common process for login
     *
     * @param array $credentials
     *
     * @return \App\Models\Admin
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function signin(array $credentials): Admin
    {
        if (!$this->auth->attempt($credentials))
            throw ValidationException::withMessages(['signin' => [__('auth.failed')]]);

        $admin = $this->auth->user();
        $this->adminRepository->setLastLogin($admin);

        return $admin;
    }

    /**
     * Register new admin to the database via web
     *
     * @param \App\Data\Admin\SignUpData $data
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
     * Register new admin to the database via api
     *
     * @param \App\Data\Admin\SignUpData $data
     *
     * @return array
     */
    public function signUpApi(SignUpData $data): array
    {
        $admin = $this->signup($data);
        $admin->tokens()->delete();

        return ['admin' => $admin, 'token' => $admin->createToken('api', ['admin'])->plainTextToken];
    }

    /**
     * Handle the common process for Admin SignUp
     *
     * @param \App\Data\Admin\SignUpData $data
     *
     * @return \App\Models\Admin
     */
    protected function signup(SignUpData $data): Admin
    {
        $admin = $this->adminRepository->create($data->all());
        $this->auth->login($admin);

        return $admin;
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
        request()->session()->forget('auth.admin');
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
     * Handle admin request to send reset link via email
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
        $response = $this->password->broker()->reset($credentials, function ($admin, $password) {
            $this->adminRepository->updatePassword($password, $admin);
            $this->auth->login($admin);
        });

        throw_if($response != Password::PASSWORD_RESET, ValidationException::withMessages(['message' => [__($response)]]));
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
        session()->put('auth.admin.password_confirmed_at', time());
    }
}
