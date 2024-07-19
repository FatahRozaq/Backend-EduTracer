<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kelas extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'nama_kelas', 'deskripsi', 'enrollment_key'
    ];

    public function kelasUsers()
    {
        return $this->hasMany(KelasUser::class, 'id_kelas');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, KelasUser::class, 'id_kelas', 'id_user', 'id_kelas', 'id_user');
    }

    public function kelasMataPelajaran()
    {
        return $this->hasMany(KelasMataPelajaran::class, 'id_kelas');
    }
}
