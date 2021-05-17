<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliableDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliable_dates', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('service_id')->index();
            $table->date('avaliable_date');
            $table->string('avaliable_time_start');
            $table->unique(['service_id','avaliable_date','avaliable_time_start'],'uniq');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliable_dates');
    }
}
