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
        Schema::create('mapel_guru', function (Blueprint $table) {
            $table->id('id_mapel_guru');
            $table->unsignedBiginteger('id_mata_pelajaran');
            $table->unsignedBiginteger('id_user');
            $table->timestamps();

            
            $table->foreign('id_mata_pelajaran')->references('id_mata_pelajaran')
                ->on('mata_pelajaran')->onDelete('cascade');
            $table->foreign('id_user')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapel_gurus');
    }
};
