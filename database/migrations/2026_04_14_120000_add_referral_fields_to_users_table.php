<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code')->nullable()->unique()->after('phone');
            $table->foreignId('referred_by_user_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('points_balance')->default(0)->after('referred_by_user_id');
        });

        DB::table('users')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    DB::table('users')->where('id', $user->id)->update(['referral_code' => $this->generateUniqueReferralCode()]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by_user_id']);
            $table->dropUnique(['referral_code']);
            $table->dropColumn(['referral_code','referred_by_user_id','points_balance']);
        });
    }

    private function generateUniqueReferralCode(): string
    {
        do {
            $code = 'REF' . Str::upper(Str::random(8));
        } while (DB::table('users')->where('referral_code', $code)->exists());

        return $code;
    }
};
