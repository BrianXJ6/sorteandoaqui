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
class UpdatePersonalData extends Data
{
    /**
     * Create a new Update Data instance
     *
     * @param null|string $cpf,
     * @param null|string $nick,
     * @param null|string $name,
     * @param null|string $email,
     * @param null|string $phone,
     * @param null|string $referral_code,
     */
    public function __construct(
        public readonly ?string $cpf,
        public readonly ?string $nick,
        public readonly ?string $name,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $referralCode,
    ) {
        //
    }
}
