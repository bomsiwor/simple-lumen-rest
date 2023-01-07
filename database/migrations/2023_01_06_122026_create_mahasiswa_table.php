<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMahasiswaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->integer('nim', false)->primary();
            $table->unsignedBigInteger('user_id');
            $table->string('nama', 100);
            $table->date('tl');
            $table->unsignedBigInteger('jurusan', false);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('jurusan')->references('id')->on('jurusan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mahasiswa');
    }
}
