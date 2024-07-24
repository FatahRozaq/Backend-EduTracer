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
        Schema::create('jadwal_pengajar', function (Blueprint $table) {
            $table->id('id_jadwal_pengajar');
            $table->unsignedBiginteger('id_jadwal');
            $table->unsignedBiginteger('id_user');
            $table->timestamps();

            
            $table->foreign('id_jadwal')->references('id_jadwal')
                ->on('jadwal')->onDelete('cascade');
            $table->foreign('id_user')->references('id')
                ->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pengajars');
    }
};
