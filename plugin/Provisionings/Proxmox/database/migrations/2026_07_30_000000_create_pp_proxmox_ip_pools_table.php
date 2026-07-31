<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pp_proxmox_ip_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plugin_id')->constrained('plugins')->cascadeOnDelete();
            $table->string('name');
            $table->enum('ip_version', ['ipv4', 'ipv6'])->default('ipv4');
            $table->string('subnet')->nullable();
            $table->string('gateway')->nullable();
            $table->string('netmask')->nullable();
            $table->integer('prefix_length')->nullable();
            $table->text('nameservers')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pp_proxmox_ip_pools');
    }
};
