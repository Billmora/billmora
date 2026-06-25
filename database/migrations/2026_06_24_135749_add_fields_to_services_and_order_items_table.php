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
        Schema::table('services', function (Blueprint $table) {
            $table->json('fields')->nullable()->after('configuration');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->json('fields')->nullable()->after('config_options');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('fields');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('fields');
        });
    }
};
