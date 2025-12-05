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
        Schema::create('package_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['free', 'onetime', 'recurring'])->default('free');
            $table->integer('time_interval')->nullable();
            $table->enum('billing_period', ['hourly', 'daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->json('rates')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_prices');
    }
};
