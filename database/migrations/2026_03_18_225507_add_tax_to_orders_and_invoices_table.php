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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('tax', 15, 2)->default(0)->after('setup_fee');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax', 15, 2)->default(0)->after('discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('tax');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('tax');
        });
    }
};
