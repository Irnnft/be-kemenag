<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanBulanan extends Model
{
    use HasFactory;

    protected $table = 'laporan_bulanan';
    protected $primaryKey = 'id_laporan';

    protected $fillable = [
        'id_madrasah',
        'bulan_tahun',
        'status_laporan',
        'catatan_revisi',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'bulan_tahun' => 'date'
    ];

    public function madrasah()
    {
        return $this->belongsTo(Madrasah::class, 'id_madrasah', 'id_madrasah');
    }

    public function siswa()
    {
        return $this->hasMany(DataSiswa::class, 'id_laporan', 'id_laporan');
    }

    public function rekapPersonal()
    {
        return $this->hasMany(DataRekapPersonal::class, 'id_laporan', 'id_laporan');
    }

    public function guru()
    {
        return $this->hasMany(DataGuru::class, 'id_laporan', 'id_laporan');
    }

    public function sarpras()
    {
        return $this->hasMany(DataSarpras::class, 'id_laporan', 'id_laporan');
    }

    public function mobiler()
    {
        return $this->hasMany(DataMobiler::class, 'id_laporan', 'id_laporan');
    }

    public function keuangan()
    {
        return $this->hasMany(DataKeuangan::class, 'id_laporan', 'id_laporan');
    }
}
