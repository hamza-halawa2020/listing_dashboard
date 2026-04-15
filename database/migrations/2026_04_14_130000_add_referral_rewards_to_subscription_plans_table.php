<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->unsignedInteger('referrer_reward_points')->default(0)->after('max_family_members');
            $table->string('referee_reward_type')->default('none')->after('referrer_reward_points');
            $table->decimal('referee_reward_value', 10, 2)->default(0)->after('referee_reward_type');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['referrer_reward_points', 'referee_reward_type', 'referee_reward_value']);
        });
    }
};
