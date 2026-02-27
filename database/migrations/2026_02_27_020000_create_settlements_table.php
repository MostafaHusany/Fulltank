<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('reference_no')->unique();
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check']);
            $table->text('transaction_details')->nullable();
            $table->string('receipt_image')->nullable();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');

            $table->index(['station_id', 'created_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
