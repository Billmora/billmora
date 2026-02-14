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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique();
            $table->string('prefix', 10)->nullable();
            $table->string('suffix', 10)->nullable();
            $table->enum('format', ['1234.56', '1,234.56', '1.234,56', '1,234'])->default('1234.56');
            $table->decimal('base_rate', 24, 12)->default(1.000000000000);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
