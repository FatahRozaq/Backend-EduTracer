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
        Schema::create('tugas_kelas_mata_pelajaran', function (Blueprint $table) {
            $table->id('id_tugas_kelas_mata_pelajaran');
            $table->unsignedBiginteger('id_tugas');
            $table->unsignedBiginteger('id_kelas_mata_pelajaran');
            $table->unsignedBiginteger('id_user');
            $table->string('status');
            $table->string('berkas')->nullable();
            $table->double('nilai_tugas', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_tugas')->references('id_tugas')
                 ->on('tugas')->onDelete('cascade');
            $table->foreign('id_kelas_mata_pelajaran')->references('id_kelas_mata_pelajaran')
                 ->on('kelas_mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_user')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_kelas_mata_pelajaran');
    }
};
