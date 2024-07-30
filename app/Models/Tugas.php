<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_tugas';

    protected $fillable = [
        'nama_tugas',
        'deskripsi',
        'tenggat_tugas',
        'status',
        'id_kelas_mata_pelajaran',
        'file_tugas'
    ];

    public function KelasMataPelajaran()
    {
        return $this->belongsTo(KelasMataPelajaran::class, 'id_kelas_mata_pelajaran');
    }
}
