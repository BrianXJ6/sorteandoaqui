<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::factory()->create();
        Admin::factory()->hasUsers(4)->create();
        Admin::factory()->count(3)->trashed()->create();
        Admin::factory()->count(3)->hasUsers(2)->create();
        Admin::factory()->count(2)->hasUsers(1, [
            'email_verified_at' => null,
            'phone_verified_at' => null,
        ])->create();
    }
}
