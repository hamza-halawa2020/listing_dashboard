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
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('subject')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
