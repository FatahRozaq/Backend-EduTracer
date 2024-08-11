<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';
    protected $primaryKey = 'id_mata_pelajaran';

    protected $fillable = [
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'id_user'
    ];


    protected $casts = [
        'id_mata_pelajaran' => 'integer',
        'id_user' => 'integer',
    ];
     
    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'kelas_mata_pelajaran', 'id_mata_pelajaran', 'id_kelas')
            ->withTimestamps()
            ->withPivot('id_kelas_mata_pelajaran');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function pengajar()
    {
        return $this->hasMany(PengajarMapel::class, 'id_mata_pelajaran');
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class, 'id_mata_pelajaran');
    }
}
