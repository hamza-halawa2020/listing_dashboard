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
        if (!\Illuminate\Support\Facades\Schema::hasColumn('categories', 'icon')) {
            \Illuminate\Support\Facades\Schema::table('categories', function (Blueprint $table) {
                $table->string('icon')->nullable()->after('color');
            });
        }
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('icon');
        });
    }
};
