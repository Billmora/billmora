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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('package_price_id')->constrained()->cascadeOnDelete();
            $table->json('variant_selections')->nullable();
            
            $table->string('name');
            $table->enum('status', ['pending', 'active', 'suspended', 'terminated', 'cancelled'])->default('pending');
            
            $table->string('currency', 3);
            $table->enum('billing_type', ['free', 'onetime', 'recurring']);
            $table->integer('billing_interval')->nullable();
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('setup_fee', 15, 2)->default(0);
            
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('next_due_date')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->json('configuration')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('next_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
