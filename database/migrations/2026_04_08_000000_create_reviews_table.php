<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->text('review');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('status')->default(false);
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->string('guest_email')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'approved_by')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
