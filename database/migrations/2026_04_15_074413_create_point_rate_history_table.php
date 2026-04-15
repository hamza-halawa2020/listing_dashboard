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
        Schema::create('point_rate_history', function (Blueprint $table) {
            $table->id();
            $table->decimal('old_rate', 8, 4)->comment('Previous point rate in EGP');
            $table->decimal('new_rate', 8, 4)->comment('New point rate in EGP');
            $table->text('reason')->nullable()->comment('Reason for rate change');
            $table->foreignId('changed_by_admin_id')->nullable()->constrained('users')
                ->comment('Admin who made the change');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('point_rate_history');
    }
};
