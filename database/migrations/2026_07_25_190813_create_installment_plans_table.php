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
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            // unique() means a booking can have at most one plan -- the
            // database enforces the same "one plan per booking" rule that
            // the hasOne relationship expresses in PHP.
            $table->foreignId('booking_id')->unique()->constrained()->restrictOnDelete();
            $table->string('plan_type');
            $table->unsignedInteger('number_of_installments');
            $table->date('start_date');
            $table->decimal('total_amount', 12, 2);
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
