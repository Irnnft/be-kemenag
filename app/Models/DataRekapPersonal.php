<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRekapPersonal extends Model
{
    use HasFactory;

    protected $table = 'data_rekap_personal';

    protected $fillable = [
        'id_laporan',
        'keadaan',
        'jumlah_lk',
        'jumlah_pr',
        'mutasi_masuk',
        'mutasi_keluar',
        'keterangan'
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'id_laporan', 'id_laporan');
    }
}
