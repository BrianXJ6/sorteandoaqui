<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->unsignedBigInteger('referred_id')->nullable();
            $table->string('cpf', 11)->unique()->nullable();
            $table->string('nick', 10);
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('phone', 11)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('referral_code', 10)->unique();
            $table->string('password', 64);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // foreign
            $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
            $table->foreign('referred_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
