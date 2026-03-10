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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            
            $table->string('item_type');
            $table->unsignedBigInteger('item_id')->nullable();
            
            $table->string('name'); 
            $table->integer('quantity')->default(1);
            
            $table->enum('billing_type', ['free', 'onetime', 'recurring'])->default('free');
            $table->integer('billing_interval')->nullable();
            $table->enum('billing_period', ['daily', 'weekly', 'monthly', 'yearly'])->nullable();
            
            $table->decimal('price', 15, 2);
            $table->decimal('setup_fee', 15, 2)->default(0);
            
            $table->json('config_options')->nullable();
            $table->json('variant_selections')->nullable();
            
            $table->timestamps();

            $table->index(['item_type', 'item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
