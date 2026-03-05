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
        Schema::create('service_scalings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('old_package_id')->constrained('packages');
            $table->foreignId('new_package_id')->constrained('packages');
            $table->foreignId('old_package_price_id')->constrained('package_prices');
            $table->foreignId('new_package_price_id')->constrained('package_prices');
            $table->json('variant_selections')->nullable();

            $table->string('currency', 3);
            $table->decimal('old_price', 15, 2);
            $table->decimal('new_price', 15, 2);
            $table->decimal('payable_amount', 15, 2);
            $table->integer('prorata_days')->default(0);
            
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_scalings');
    }
};
