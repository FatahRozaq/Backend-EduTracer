<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapelGuru extends Model
{
    use HasFactory;

    protected $table = 'mapel_guru';
    protected $primaryKey = 'id_mapel_guru';

    protected $fillable = [
        'id_mata_pelajaran',
        'id_user'
    ];

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
