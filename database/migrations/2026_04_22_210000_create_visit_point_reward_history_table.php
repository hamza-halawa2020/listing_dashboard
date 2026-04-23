<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_point_reward_history', function (Blueprint $table) {
            $table->id();
            $table->integer('old_points');
            $table->integer('new_points');
            $table->string('reason')->nullable();
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_point_reward_history');
    }
};
