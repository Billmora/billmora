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
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('provider');
            $table->string('name');
            $table->enum('type', ['admin', 'client', 'portal', 'email', 'invoice']);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_core')->default(false); // true only for built-in theme (moraine)
            $table->json('config')->nullable();
            $table->timestamps();
            
            $table->unique(['type', 'provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('themes');
    }
};
