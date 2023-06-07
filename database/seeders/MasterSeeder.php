<?php

namespace Database\Seeders;

use App\Models\{
    User,
    Admin,
};

use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // User master
        User::factory()->hasReferrals(5)->state([
            'cpf' => '00000000000',
            'name' => 'Brian Barros',
            'email' => 'brian@gmail.com',
            'phone' => '00000000000',
            'email_verified_at' => now(),
            'phone_verified_at' => now(),
            'referral_code' => 'BrianBilbo',
        ])->create();

        // Admin master
        Admin::factory()->hasUsers(3)->state([
            'name' => 'Brian Barros',
            'email' => 'brian@gmail.com',
            'phone' => '00000000000',
        ])->create();
    }
}
