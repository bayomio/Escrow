<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_players', function (Blueprint $table) {
            $table->id();
            $table->integer("event_id");
            $table->integer("user_id");
            $table->integer("score")->nullable();
            $table->enum("position", ["HOME", "AWAY"]);
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
        Schema::dropIfExists('event_players');
    }
}
