<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasUser extends Model
{
    use HasFactory;

    protected $table = 'kelas_user';
    protected $primaryKey = 'id_kelas_user';

    protected $fillable = [
        'id_user',
        'id_kelas',
    ];

    // Tambahkan relasi ke model User
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    // Tambahkan relasi ke model Kelas
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }
}

