<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSaldosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('user_id')->nullable();
            $table->string('admin_id')->nullable();
            $table->integer('saldo')->nullable();
            $table->integer('jumlah_transfer')->nullable();
            $table->string('no_rek')->nullable();
            $table->string('status')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('saldos');
    }
}
