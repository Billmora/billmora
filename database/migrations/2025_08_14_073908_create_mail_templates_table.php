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
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('active')->default(true);
            $table->json('cc')->nullable();
            $table->json('bcc')->nullable();
            $table->json('placeholder')->nullable();
            $table->timestamps();
        });

        Schema::create('mail_template_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_template_id')->constrained()->onDelete('cascade');
            $table->string('lang', 5)->index();
            $table->string('subject');
            $table->text('body');
            $table->timestamps();

            $table->unique(['mail_template_id', 'lang']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_template_translations');
        Schema::dropIfExists('mail_templates');
    }
};
