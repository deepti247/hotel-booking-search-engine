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
        Schema::create('rooms', function (Blueprint $table) {
        $table->id();

        $table->foreignId('hotel_id')->constrained()->onDelete('cascade');

        $table->enum('room_type', ['standard', 'deluxe']);

        $table->integer('max_person');

        $table->decimal('price_1_person', 10, 2);
        $table->decimal('price_2_person', 10, 2);
        $table->decimal('price_3_person', 10, 2);

        $table->decimal('breakfast_price', 10, 2)->default(0);

        $table->integer('total_rooms')->default(0);

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }

    
};
