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
        Schema::table('chat_conversation_participants', function (Blueprint $table) {
            $table->index(
                ['user_id', 'chat_conversation_id'],
                'chat_conversation_participants_user_lookup_index',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_conversation_participants', function (Blueprint $table) {
            $table->dropIndex('chat_conversation_participants_user_lookup_index');
        });
    }
};
