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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->string('payment_reference')->nullable();
            $table->enum('payment_method', ['cash', 'credit_card', 'bank_transfer', 'fawry', 'vodafone_cash', 'online'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
