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
            $table->timestamps();
            
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('picture')->nullable();
            $table->string('password');
            $table->enum('category', [
                'admin',           // Super-Admin, bypasses all permission checks
                'technical',       // Uses Roles & Permissions (Laratrust)
                'client',          // Fleet owner: drivers, vehicles, balance
                'station_manager', // Manages a fuel station and its workers
                'worker',          // Station employee; fueling via QR codes
                'driver',          // End-user who consumes the balance
            ])->default('driver');
            $table->boolean('is_active')->default(false);

            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('users')
            ->onUpdate('CASCADE')->onDelete('SET NULL');

            $table->rememberToken();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
