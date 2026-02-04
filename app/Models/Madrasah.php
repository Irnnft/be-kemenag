<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Madrasah extends Model
{
    use HasFactory;

    protected $table = 'madrasah';
    protected $primaryKey = 'id_madrasah';

    protected $fillable = [
        'npsn',
        'nama_madrasah',
        'alamat',
        'status_aktif'
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'id_madrasah', 'id_madrasah');
    }

    public function laporanBulanan()
    {
        return $this->hasMany(LaporanBulanan::class, 'id_madrasah', 'id_madrasah');
    }
}
