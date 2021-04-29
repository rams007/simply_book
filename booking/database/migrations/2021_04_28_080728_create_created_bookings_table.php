<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreatedBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('created_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('booking_id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('unit_id');
            $table->unsignedInteger('client_id');
            $table->string('client_hash');
            $table->string('start_date_time');
            $table->string('end_date_time');
            $table->smallInteger('time_offset');
            $table->unsignedTinyInteger('is_confirmed');
            $table->boolean('require_payment');
            $table->string('code');
            $table->string('hash');
            $table->string('subscription_id')->nullable();
            $table->enum('status',['new', 'active', 'canceled'])->default('new');
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
        Schema::dropIfExists('created_bookings');
    }
}
