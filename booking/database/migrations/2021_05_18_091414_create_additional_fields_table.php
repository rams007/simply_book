<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdditionalFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('event_id');
            $table->unsignedSmallInteger('field_id');
            $table->string('name');
            $table->string('title');
            $table->string('type');
            $table->string('length')->nullable();
            $table->text('values')->nullable();
            $table->string('default')->nullable();
            $table->unsignedTinyInteger('is_null')->nullable();
            $table->unsignedTinyInteger('is_visible');
            $table->unsignedTinyInteger('pos');
            $table->unsignedTinyInteger('show_for_all_events');
            $table->string('value')->nullable();
            $table->string('plugin_event_field_value_id')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'field_id'], 'uniq');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('additional_fields');
    }
}
