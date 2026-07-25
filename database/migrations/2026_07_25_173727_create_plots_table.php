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
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('block_id')->constrained()->restrictOnDelete();
            $table->string('plot_number');
            $table->string('street')->nullable();
            $table->string('plot_size');
            $table->string('category');
            $table->boolean('corner_plot')->default(false);
            $table->boolean('park_facing')->default(false);
            $table->boolean('main_boulevard')->default(false);
            $table->decimal('plot_price', 12, 2);
            $table->decimal('development_charges', 12, 2)->default(0);
            $table->decimal('extra_charges', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2);
            $table->string('status')->default('Available');
            $table->timestamps();

            $table->unique(['block_id', 'plot_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plots');
    }
};
