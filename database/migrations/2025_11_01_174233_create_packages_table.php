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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained('catalogs')->onDelete('restrict'); 
            $table->string('name');
            $table->string('slug');
            $table->text('description');
            $table->string('icon')->nullable();
            $table->integer('stock')->default(-1);
            $table->integer('per_user_limit')->default(-1);
            $table->boolean('allow_cancellation')->default(true);
            $table->enum('status', ['visible', 'hidden', 'inactive'])->default('visible');
            $table->timestamps();

            $table->unique(['catalog_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
