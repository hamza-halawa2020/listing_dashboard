<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_cards', function (Blueprint $table) {
            $table->id();

            // Card identity
            $table->string('card_number')->unique();

            // Linked plan (NOT a subscription — that gets created on approval)
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');

            // Card validity window
            $table->date('starts_at');
            $table->date('ends_at');

            // Activation lifecycle
            $table->enum('status', [
                'unassigned',         // issued by admin, not yet claimed
                'pending_activation', // user submitted national_id, awaiting admin
                'active',             // admin approved → subscription created
                'rejected',           // admin rejected
            ])->default('unassigned');

            // User link (nullable until activation is approved)
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            // National ID the user entered when requesting activation
            $table->string('national_id')->nullable();

            // Activation timestamps
            $table->timestamp('activation_requested_at')->nullable();
            $table->timestamp('activation_approved_at')->nullable();

            // Admin notes (rejection reason, approval notes, etc.)
            $table->text('notes')->nullable();

            // Which admin issued the card
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            // Subscription created on approval (optional link)
            $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_cards');
    }
};
