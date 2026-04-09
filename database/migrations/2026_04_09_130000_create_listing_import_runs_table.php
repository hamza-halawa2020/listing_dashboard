<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_import_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('status')->default('pending');
            $table->integer('summary_created')->default(0);
            $table->integer('summary_updated')->default(0);
            $table->integer('summary_skipped')->default(0);
            $table->json('summary_errors')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_import_runs');
    }
};
