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
        Schema::table('tugas', function (Blueprint $table) {
            $table->unsignedBiginteger('id_kelas_mata_pelajaran');

            $table->foreign('id_kelas_mata_pelajaran')->references('id_kelas_mata_pelajaran')
                ->on('kelas_mata_pelajaran')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn('id_kelas_mata_pelajaran');
        });
    }
};
