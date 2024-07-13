<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuratIzinTable extends Migration
{
    public function up()
    {
        Schema::create('surat_izin', function (Blueprint $table) {
            $table->id('id_surat');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_penerima');
            $table->unsignedBigInteger('id_kelas');
            $table->date('tanggal');
            $table->string('jenis_surat', 5);
            $table->string('deskripsi', 255);
            $table->string('berkas_surat', 255)->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_penerima')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id_kelas')->on('kelas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('surat_izin');
    }
}
