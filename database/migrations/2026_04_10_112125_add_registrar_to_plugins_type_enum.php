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
        DB::statement("ALTER TABLE plugins MODIFY type ENUM('provisioning', 'gateway', 'module', 'registrar') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {


        DB::statement("DELETE FROM plugins WHERE type = 'registrar'");
        DB::statement("ALTER TABLE plugins MODIFY type ENUM('provisioning', 'gateway', 'module') NOT NULL");
    }
};
