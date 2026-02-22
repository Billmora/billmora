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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('plugin_id')->nullable()->constrained('plugins')->nullOnDelete();
            
            $table->string('transaction_id')->nullable()->index();
            $table->string('description');
            
            $table->string('currency', 3);
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
