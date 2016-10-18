<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('im__message', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('from_id');
            $table->boolean('is_read');
            $table->string('chat_id');
            $table->string('text');
            $table->timestamp('deleted_at');
            $table->timestamps();
            $table->foreign('from_id')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('im_message');
    }
}