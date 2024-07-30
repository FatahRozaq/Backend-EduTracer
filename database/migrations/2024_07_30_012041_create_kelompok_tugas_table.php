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
        Schema::create('kelompok_tugas', function (Blueprint $table) {
            $table->id('id_kelompok_tugas');
            $table->string('nama_kelompok_tugas');
            $table->string('deskripsi');
            $table->string('file')->nullable();
            $table->unsignedBiginteger('id_mata_pelajaran');
            $table->unsignedBigInteger('id_guru');

            $table->foreign('id_mata_pelajaran')->references('id_mata_pelajaran')
                ->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_guru')->references('id')
                ->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelompok_tugas');
    }
};
