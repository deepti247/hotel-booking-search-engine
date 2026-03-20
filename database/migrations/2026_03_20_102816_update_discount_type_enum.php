<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */

        public function up()
        {
            DB::statement("
                ALTER TABLE discounts 
                MODIFY type ENUM('long_stay','last_minute','early_bird') NOT NULL
            ");
        }



        public function down()
        {
            DB::statement("
                ALTER TABLE discounts 
                MODIFY type ENUM('long_stay','last_minute') NOT NULL
            ");
        }
    

    
};
