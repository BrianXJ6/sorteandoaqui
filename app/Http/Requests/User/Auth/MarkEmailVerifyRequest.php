<?php

namespace App\Http\Requests\User\Auth;

use Illuminate\Foundation\Http\FormRequest;

class MarkEmailVerifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var \MS_User\Models\User */
        $user = auth()->user();

        switch (true) {
            case !hash_equals((string) sha1($user->id), (string) $this->id):
            case !hash_equals((string) sha1($user->getEmailForVerification()), (string) $this->hash):
            case $user->hasVerifiedEmail():
                return false;
                break;

            default:
                return true;
                break;
        }
    }
}
