<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_member_id')->constrained('pm_affiliate_members')->cascadeOnDelete();
            $table->foreignId('referral_id')->constrained('pm_affiliate_referrals')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency');
            $table->string('status')->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_affiliate_commissions');
    }
};
