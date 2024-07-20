<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengajarMapel extends Model
{
    use HasFactory;

    protected $table = 'pengajar_mapel';
    protected $primaryKey = 'id_pengajar_mapel';

    protected $fillable = [
        'id_mata_pelajaran',
        'id_user',
        'id_kelas',
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }
}
