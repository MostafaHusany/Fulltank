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
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop the old enum column
            $table->dropColumn('fuel_type');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            // Add new foreign key column
            $table->foreignId('fuel_type_id')
                ->nullable()
                ->after('model')
                ->constrained('fuel_types')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropForeign(['fuel_type_id']);
            $table->dropColumn('fuel_type_id');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->enum('fuel_type', ['petrol', 'diesel', 'electric', 'hybrid', 'cng'])
                ->default('petrol')
                ->after('model');
        });
    }
};
