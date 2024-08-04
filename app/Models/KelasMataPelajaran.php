<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasMataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'kelas_mata_pelajaran';

    protected $primaryKey = 'id_kelas_mata_pelajaran';

    protected $fillable = ['id_kelas', 'id_mata_pelajaran'];

    protected $casts = [
        'id_kelas' => 'integer',
        'id_mata_pelajaran' => 'integer',
        'id_kelas_mata_pelajaran' => 'integer',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }
}
