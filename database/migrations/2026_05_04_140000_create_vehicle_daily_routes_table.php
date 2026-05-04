<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_daily_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->date('route_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('point_count')->default(0);
            $table->decimal('distance_km', 12, 3)->nullable();
            $table->timestamps();

            $table->unique(['vehicle_id', 'route_date']);
            $table->index(['route_date']);
        });

        Schema::table('vehicle_locations', function (Blueprint $table) {
            $table->foreignId('vehicle_daily_route_id')->nullable()->after('vehicle_id')->constrained('vehicle_daily_routes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_locations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_daily_route_id');
        });

        Schema::dropIfExists('vehicle_daily_routes');
    }
};
