<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSarpras extends Model
{
    use HasFactory;

    protected $table = 'data_sarpras';

    protected $fillable = [
        'id_laporan',
        'jenis_aset',
        'luas',
        'kondisi_baik',
        'kondisi_rusak_ringan',
        'kondisi_rusak_berat',
        'kekurangan',
        'perlu_rehab',
        'keterangan'
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'id_laporan', 'id_laporan');
    }
}
