<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'nama_kelas',
        'deskripsi',
        'enrollment_key'
    ];

    public function mataPelajaran()
    {
        return $this->belongsToMany(MataPelajaran::class, 'kelas_mata_pelajaran', 'id_kelas', 'id_mata_pelajaran')
            ->using(KelasMataPelajaran::class)
            ->withTimestamps()
            ->withPivot('id_kelas_mata_pelajaran');
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }
   
}
