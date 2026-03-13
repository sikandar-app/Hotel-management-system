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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number');
            $table->bigInteger('room_id');
            $table->bigInteger('tax_id');
            $table->bigInteger('user_id');
            $table->string('name');
            $table->date('date');
            $table->string('contact_number');
            $table->integer('number_of_guests');
            $table->string('cnic_number');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->integer('number_of_nights');
            $table->string('approach_via')->nullable();
            $table->decimal('price_per_night', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('net_total', 10, 2);
            $table->string('cnic_image')->nullable();
            $table->json('booking_dates')->nullable();            
            $table->enum('status', ['draft', 'canceled', 'confirmed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
