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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('plate_number')->unique();
            $table->string('model');
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid', 'cng'])->default('petrol');
            $table->enum('status', ['active', 'inactive'])->default('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
