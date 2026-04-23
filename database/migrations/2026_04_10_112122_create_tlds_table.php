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
        Schema::create('tlds', function (Blueprint $table) {
            $table->id();
            $table->string('tld')->unique();
            $table->foreignId('plugin_id')->nullable()->constrained('plugins')->nullOnDelete();
            
            $table->integer('min_years')->default(1);
            $table->integer('max_years')->default(10);
            $table->integer('grace_period_days')->default(0);
            $table->integer('redemption_period_days')->default(0);
            
            $table->boolean('whois_privacy')->default(false);
            
            $table->enum('status', ['visible', 'hidden'])->default('visible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tlds');
    }
};
