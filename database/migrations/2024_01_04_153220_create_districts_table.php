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
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('en_name');
            $table->string('ar_name');
            $table->enum('category', ['gove', 'cent'])->default('gove');
            
            $table->boolean('is_active')->default(true);
            
            $table->string('geo_lat')->nullable();
            $table->string('geo_lng')->nullable();

            $table->unsignedBigInteger('district_id')->nullable();
            $table->foreign('district_id')->references('id')->on('districts')
            ->onUpdate('CASCADE')->onDelete('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('districts');
    }
};
