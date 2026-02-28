<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fueling_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('driver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('fuel_type_id')->constrained('fuel_types')->cascadeOnDelete();

            $table->decimal('requested_liters', 10, 2);
            $table->decimal('estimated_cost', 10, 2);
            $table->decimal('fuel_price_at_request', 10, 2);

            $table->string('otp_code', 6);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->enum('status', ['pending', 'completed', 'expired', 'cancelled'])->default('pending');
            $table->timestamp('expires_at');

            $table->foreignId('completed_by_worker_id')->nullable()->constrained('station_workers')->nullOnDelete();
            $table->foreignId('completed_at_station_id')->nullable()->constrained('stations')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['driver_id', 'status']);
            $table->index(['otp_code', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fueling_requests');
    }
};
