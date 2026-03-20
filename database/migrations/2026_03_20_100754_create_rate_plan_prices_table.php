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
            Schema::create('rate_plan_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rate_plan_id');
            $table->integer('occupancy'); // 1,2,3,4
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->foreign('rate_plan_id')->references('id')->on('rate_plans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_plan_prices');
    }
};
