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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->foreignId('booking_id')->constrained()->restrictOnDelete();
            // Only set when the admin chose "pay a specific installment" (Mode A).
            // For "auto apply" (Mode B) this stays null, because one payment can
            // end up spread across several installments.
            $table->foreignId('installment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method');
            $table->string('payment_type');
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
