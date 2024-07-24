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
        Schema::create('pengajar_mapel', function (Blueprint $table) {
            $table->id('id_pengajar_mapel');
            $table->unsignedBiginteger('id_mata_pelajaran');
            $table->unsignedBiginteger('id_user');
            $table->unsignedBiginteger('id_kelas');
            $table->timestamps();

            
            $table->foreign('id_mata_pelajaran')->references('id_mata_pelajaran')
                ->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_user')->references('id')
                ->on('users')->onDelete('cascade');
            $table->foreign('id_kelas')->references('id_kelas')
                ->on('kelas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajar_mapels');
    }
};
