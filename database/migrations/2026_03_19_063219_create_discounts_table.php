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
    Schema::create('discounts', function (Blueprint $table) {
        $table->id();

        $table->enum('type', ['long_stay', 'last_minute']);

        $table->integer('min_nights')->nullable(); // for long stay
        $table->integer('max_days_before_checkin')->nullable(); // for last minute

        $table->decimal('discount_percent', 5, 2);

        $table->boolean('status')->default(1);

        $table->timestamps();
    });
  }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::dropIfExists('discounts');
    }
};
