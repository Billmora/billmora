<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pm_affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_member_id')->constrained('pm_affiliate_members')->cascadeOnDelete();
            $table->foreignId('referred_user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('converted')->default(false);
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();

            $table->unique('referred_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pm_affiliate_referrals');
    }
};
