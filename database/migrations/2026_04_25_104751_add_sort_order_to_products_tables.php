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
        Schema::table('catalogs', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('status');
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('status');
        });
        Schema::table('variants', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('status');
        });
        Schema::table('tlds', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogs', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
        Schema::table('tlds', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
