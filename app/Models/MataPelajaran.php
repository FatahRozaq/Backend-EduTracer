<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'id_user'
    ];
     
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mata_pelajaran', 'id_mata_pelajaran', 'id_kelas')
            // ->using(KelasMataPelajaran::class)
            ->withTimestamps()
            ->withPivot('id_kelas_mata_pelajaran');
    }
}
