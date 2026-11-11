<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            // Status of the physical card activation lifecycle
            $table->enum('card_activation_status', [
                'unassigned',        // card number issued by admin, not yet claimed
                'pending_activation',// user submitted national_id, awaiting admin approval
                'active',            // admin approved → card is live
                'rejected',          // admin rejected the activation request
            ])->default('unassigned')->after('is_card_issued');

            // National ID the user entered when requesting activation
            $table->string('card_activation_national_id')->nullable()->after('card_activation_status');

            // Timestamps
            $table->timestamp('card_activation_requested_at')->nullable()->after('card_activation_national_id');
            $table->timestamp('card_activation_approved_at')->nullable()->after('card_activation_requested_at');

            // Admin notes (reason for rejection, etc.)
            $table->text('card_activation_notes')->nullable()->after('card_activation_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'card_activation_status',
                'card_activation_national_id',
                'card_activation_requested_at',
                'card_activation_approved_at',
                'card_activation_notes',
            ]);
        });
    }
};
