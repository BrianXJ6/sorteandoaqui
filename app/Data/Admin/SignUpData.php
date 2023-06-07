<?php

namespace App\Data\Admin;

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
     */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $password,
    ) {
        //
    }
}
