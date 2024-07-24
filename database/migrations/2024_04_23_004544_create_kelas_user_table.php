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
        Schema::create('kelas_user', function (Blueprint $table) {
            $table->id('id_kelas_user');
            $table->unsignedBiginteger('id_user');
            $table->unsignedBiginteger('id_kelas');
            $table->string('status');
            $table->timestamps();

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
        Schema::dropIfExists('kelas_user');
    }
};
