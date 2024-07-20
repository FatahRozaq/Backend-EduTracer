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
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id('id_jadwal');
            $table->unsignedBiginteger('id_kelas');
            $table->unsignedBiginteger('id_mata_pelajaran');
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_akhir')->nullable();
            $table->timestamps();

            
            $table->foreign('id_kelas')->references('id_kelas')
                ->on('kelas')->onDelete('cascade');
            $table->foreign('id_mata_pelajaran')->references('id_mata_pelajaran')
                ->on('mata_pelajaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};
