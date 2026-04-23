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
        Schema::create('tld_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tld_id')->constrained('tlds')->cascadeOnDelete();
            $table->string('currency', 3);
            
            $table->decimal('register_price', 15, 2);
            $table->decimal('transfer_price', 15, 2);
            $table->decimal('renew_price', 15, 2);

            $table->timestamps();
            
            $table->unique(['tld_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tld_prices');
    }
};
