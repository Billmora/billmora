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
        Schema::table('packages', function (Blueprint $table) {
            // Prorata day: fixed day-of-month (1–28) for pro-rata billing anchor.
            // NULL = no pro-rata, billing starts from activation date as usual.
            $table->unsignedTinyInteger('prorata_day')->nullable()->default(null)->after('allow_cancellation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('prorata_day');
        });
    }
};
