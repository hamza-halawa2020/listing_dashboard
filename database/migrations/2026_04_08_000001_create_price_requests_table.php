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
        Schema::create('price_requests', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('contact_person');
            $table->string('email');
            $table->string('phone');
            $table->string('company_type')->nullable(); // individual, company, organization
            $table->integer('employee_count')->nullable();
            $table->text('services_needed');
            $table->text('additional_requirements')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->boolean('status')->default(false); // responded or not
            $table->foreignIdFor(User::class, 'responded_by')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->text('response_notes')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_requests');
    }
};
