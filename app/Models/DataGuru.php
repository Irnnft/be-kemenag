<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataGuru extends Model
{
    use HasFactory;

    protected $table = 'data_guru';

    protected $fillable = [
        'id_laporan',
        'nama_guru',
        'nip_nik',
        'lp',
        'tempat_lahir',
        'tanggal_lahir',
        'status_pegawai',
        'pendidikan_terakhir',
        'jurusan',
        'golongan',
        'tmt_mengajar',
        'tmt_di_madrasah',
        'mata_pelajaran',
        'satminkal',
        'jumlah_jam',
        'jabatan',
        'nama_ibu_kandung',
        'sertifikasi',
        'mutasi_status'
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'id_laporan', 'id_laporan');
    }
}
