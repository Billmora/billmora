<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pp_proxmox_ip_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ip_pool_id')->constrained('pp_proxmox_ip_pools')->cascadeOnDelete();
            $table->string('ip_address');
            $table->enum('status', ['free', 'used', 'reserved'])->default('free');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->timestamps();
            
            $table->unique(['ip_pool_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_proxmox_ip_addresses');
    }
};
