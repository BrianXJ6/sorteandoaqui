<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->forAdmin()->forReferred()->count(3)->create();
        User::factory()->forAdmin()->forReferred()->count(3)->unverifiedMail()->create();
        User::factory()->forAdmin()->forReferred()->count(2)->unverifiedPhone()->create();
        User::factory()->forAdmin()->forReferred()->count(5)->unverifiedMail()->unverifiedPhone()->create();
        User::factory()->count(3)->trashed()->unverifiedMail()->unverifiedPhone()->create();
    }
}
