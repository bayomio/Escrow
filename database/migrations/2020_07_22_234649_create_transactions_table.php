<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer("user_id");
            $table->integer("event_id")->nullable();
            $table->double("amount", 0);
            $table->string("narration")->nullable();
            $table->enum("request_type", ["DEPOSIT", "WITHDRAW", "EVENT"]);
            $table->timestamp("value_date")->nullable()->default(null);
            $table->enum('status', ['new', 'rejected', 'approved']);

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
        Schema::dropIfExists('transactions');
    }
}
