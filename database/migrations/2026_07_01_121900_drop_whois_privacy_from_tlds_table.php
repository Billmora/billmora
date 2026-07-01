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
        Schema::table('tlds', function (Blueprint $table) {
            $table->dropColumn('whois_privacy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tlds', function (Blueprint $table) {
            $table->boolean('whois_privacy')->default(false)->after('max_years');
        });
    }
};

