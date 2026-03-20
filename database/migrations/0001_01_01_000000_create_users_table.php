<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('email')->nullable()->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('language');
            $table->rememberToken();
            $table->timestamps();

            $table->nestedSet();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
