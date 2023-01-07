<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->integer('nim')->unsigned();
            $table->unsignedBigInteger('matkul_id');

            $table->foreign('nim')->references('nim')->on('mahasiswa');
            $table->foreign('matkul_id')->references('id')->on('mata_kuliah');

            $table->primary(['nim', 'matkul_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kelas');
    }
}
