<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("description")->nullable();
            $table->timestamp("start")->nullable()->default(null);;
            $table->timestamp("end")->nullable()->default(null);;
            $table->enum("result", ['HOME', 'DRAW', 'A​WAY'])->nullable();
            $table->string("streaming_link")->nullable();
            $table->boolean("paid")->default(false);
            $table->boolean("sent")->default(false);
            $table->string("event_image")->nullable();

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
        Schema::dropIfExists('events');
    }
}
