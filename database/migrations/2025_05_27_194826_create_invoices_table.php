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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->bigInteger('user_id');
            $table->decimal('amount_paid', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer'])->nullable();
            $table->date('payment_date');
            $table->text('note')->nullable();
            $table->enum('status', ['approved', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
