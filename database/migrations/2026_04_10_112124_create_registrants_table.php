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
        Schema::create('registrants', function (Blueprint $table) {
            $table->id();
            $table->string('registrant_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('tld_id')->constrained('tlds');
            $table->foreignId('plugin_id')->nullable()->constrained('plugins')->nullOnDelete();
            
            $table->string('domain');
            $table->enum('status', [
                'pending', 'active', 'expired', 'suspended',
                'pending_transfer', 'transferred_away', 'cancelled',
                'redemption', 'terminated'
            ])->default('pending');
            $table->enum('registration_type', ['register', 'transfer'])->default('register');
            
            $table->integer('years')->default(1);
            $table->string('currency', 3);
            $table->decimal('price', 15, 2);
            
            $table->boolean('auto_renew')->default(true);
            $table->boolean('whois_privacy')->default(false);
            $table->string('epp_code')->nullable();
            $table->json('nameservers')->nullable();
            $table->json('configuration')->nullable();
            
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            
            $table->unique('domain');
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrants');
    }
};
