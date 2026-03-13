<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::table('bookings', function (Blueprint $table) {
            $table->string('document_image')->nullable();
        });

        // Copy data from cnic_image to document_image
        DB::statement('UPDATE bookings SET document_image = cnic_image');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('cnic_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('cnic_image')->nullable();
        });

        // Copy data back if needed
        DB::statement('UPDATE bookings SET cnic_image = document_image');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('document_image');
        });
    }
};
