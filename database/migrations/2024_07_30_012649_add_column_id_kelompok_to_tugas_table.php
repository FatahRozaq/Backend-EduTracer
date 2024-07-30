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
            $table->string('file')->nullable();
            $table->unsignedBigInteger('id_kelompok_tugas')->nullable();

            $table->foreign('id_kelompok_tugas')->references('id_kelompok_tugas')
                ->on('kelompok_tugas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn('file');
            $table->dropColumn('id_kelompok_tugas');
        });
    }
};
