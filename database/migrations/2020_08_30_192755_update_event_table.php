<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean("sent_one_hour")->default(false);
            $table->boolean("sent_fifteen")->default(false);
            $table->boolean("sent_voters")->default(false);
            $table->integer("home_player_score")->nullable();
            $table->integer("away_player_score")->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
