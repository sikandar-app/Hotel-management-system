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
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('building')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('floor')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('building')->nullable(false)->change();
            $table->string('address')->nullable(false)->change();
            $table->string('floor')->nullable(false)->change();
        });
    }
};
