<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Post;

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
            $table->boolean('status')->default(false);
            $table->foreignIdFor(Post::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_phone', 20)->nullable();
            $table->foreignIdFor(User::class,'created_by')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class,'approved_by')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['post_id', 'status']);

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
