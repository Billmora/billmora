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
        Schema::table('service_scalings', function (Blueprint $table) {
            $table->json('configuration')->nullable()->after('variant_selections');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_scalings', function (Blueprint $table) {
            $table->dropColumn('configuration');
        });
    }
};
