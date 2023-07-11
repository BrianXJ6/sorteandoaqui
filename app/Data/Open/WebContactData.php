<?php

namespace App\Data\Open;

use Spatie\LaravelData\Attributes\{
    MapName,
    MapOutputName,
};

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
class WebContactData extends Data
{
    /**
     * Create a new Web Contact Data instance
     *
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $subject
     * @param string $message
     */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $subject,
        public readonly string $message,
    ) {
        //
    }
}
