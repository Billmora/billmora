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
        Schema::create('coupon_tld', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('tld_id')->constrained('tlds')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['coupon_id', 'tld_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_tld');
    }
};
