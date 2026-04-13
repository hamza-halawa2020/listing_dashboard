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
        Schema::create('chat_conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_admin')->default(false);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['chat_conversation_id', 'user_id'], 'chat_conversation_user_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversation_participants');
    }
};
