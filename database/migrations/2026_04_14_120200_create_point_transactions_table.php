<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained('referrals')->nullOnDelete();
            $table->foreignId('created_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['referral_bonus','referee_bonus','redeem','admin_add','admin_deduct','expire','adjustment']);
            $table->integer('points');
            $table->unsignedInteger('balance_after');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
