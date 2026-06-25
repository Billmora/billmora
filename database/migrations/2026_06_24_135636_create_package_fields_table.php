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
        Schema::create('package_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('name');
            $table->string('label');
            $table->string('helper')->nullable();
            $table->string('default')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('visible_on_order')->default(true);
            $table->boolean('visible_on_invoice')->default(false);
            $table->json('options')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_fields');
    }
};
