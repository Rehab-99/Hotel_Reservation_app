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
            $table->foreignId('user_id')->nullable();
            $table->foreignId('room_id')->nullable();
            $table->text('room_type')->nullable();
            $table->text('first_name')->nullable();
            $table->text('surname')->nullable();
            $table->text('email')->nullable();
            $table->text('phone')->nullable();
            $table->text('check_in')->nullable();
            $table->text('check_out')->nullable();
            $table->integer('num_of_rooms')->nullable();
            $table->integer('guest')->nullable();
            $table->integer('num_of_nights')->nullable();
            $table->integer('discount')->nullable();
            $table->integer('subtotal')->nullable();
            $table->integer('total')->nullable();
            $table->enum('payment_method',['cash','visa'])->nullable();
            $table->enum('status',['pending','confirmed'])->default('pending');
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
