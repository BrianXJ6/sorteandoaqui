<?php

namespace App\Data\User;

use Spatie\LaravelData\Attributes\{
    MapName,
    MapOutputName,
};

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
class SignUpData extends Data
{
    /**
     * Create a new SignUp Data instance
     *
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $password
     * @param null|string $referralCode
     */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password,
        public readonly ?string $referralCode,
    ) {
        //
    }
}
