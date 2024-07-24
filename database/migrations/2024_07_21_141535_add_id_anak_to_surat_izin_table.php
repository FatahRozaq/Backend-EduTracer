<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdAnakToSuratIzinTable extends Migration
{
    public function up()
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->unsignedBigInteger('id_anak')->nullable()->after('id_kelas');

            // Assuming you have a users table where anak (children) are also stored
            $table->foreign('id_anak')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('surat_izin', function (Blueprint $table) {
            $table->dropForeign(['id_anak']);
            $table->dropColumn('id_anak');
        });
    }
}
