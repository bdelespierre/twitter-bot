<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournauxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journaux', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->enum('level', App\Journal::LEVELS)->default('info');
            $table->text('message');
            $table->json('context')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('journaux');
    }
}
