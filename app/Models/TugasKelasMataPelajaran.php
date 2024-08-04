<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasKelasMataPelajaran extends Model
{
    use HasFactory;
    protected $table = 'tugas_kelas_mata_pelajaran';

    protected $primaryKey = 'id_tugas_kelas_mata_pelajaran';

    protected $fillable = [
        'id_tugas',
        'id_kelas_mata_pelajaran',
        'id_user',
        'status',
        'berkas',
        'nilai_tugas'
    ];

    protected $casts = [
        'id_tugas' => 'integer',
        'id_kelas_mata_pelajaran' => 'integer',
        'id_user' => 'integer',
        'id_tugas_kelas_mata_pelajaran' => 'integer',
        'nilai_tugas' => 'integer'
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class, 'id_tugas');
    }

    public function kelasMataPelajaran()
    {
        return $this->belongsTo(KelasMataPelajaran::class, 'id_kelas_mata_pelajaran');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
