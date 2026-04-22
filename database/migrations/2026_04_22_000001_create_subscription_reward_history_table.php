<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_reward_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->integer('old_points');
            $table->integer('new_points');
            $table->string('reason')->nullable();
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_reward_history');
    }
};
