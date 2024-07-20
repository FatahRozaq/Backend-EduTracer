<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id('id_absen');
            $table->unsignedBiginteger('id_kelas_mata_pelajaran');
            $table->unsignedBiginteger('id_jadwal');
            $table->unsignedBiginteger('id_user');

            $table->foreign('id_kelas_mata_pelajaran')->references('id_kelas_mata_pelajaran')
                ->on('kelas_mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_jadwal')->references('id_jadwal')
                ->on('jadwal')->onDelete('cascade');
            $table->foreign('id_user')->references('id')
                ->on('users')->onDelete('cascade');
            $table->string('status_kehadiran');
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
