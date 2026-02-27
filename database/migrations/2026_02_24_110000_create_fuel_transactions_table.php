<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('reference_no')->unique();
            $table->string('qr_token')->nullable()->unique();

            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->foreignId('worker_id')->nullable()->constrained('station_workers')->onDelete('set null');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');

            $table->foreignId('fuel_type_id')->constrained('fuel_types')->onDelete('cascade');
            $table->decimal('price_per_liter', 10, 2);
            $table->decimal('actual_liters', 10, 3)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->decimal('max_allowed_amount', 12, 2);

            $table->string('meter_image')->nullable();

            $table->enum('status', ['pending', 'completed', 'refunded', 'cancelled'])->default('pending');
            $table->enum('type', ['qr_based', 'manual_admin'])->default('qr_based');

            $table->text('refund_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->index(['client_id', 'status']);
            $table->index(['station_id', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_transactions');
    }
};
