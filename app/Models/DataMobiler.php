<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataMobiler extends Model
{
    use HasFactory;

    protected $table = 'data_mobiler';

    protected $fillable = [
        'id_laporan',
        'nama_barang',
        'jumlah_total',
        'kondisi_baik',
        'kondisi_rusak_ringan',
        'kondisi_rusak_berat',
        'kekurangan',
        'keterangan'
    ];

    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'id_laporan', 'id_laporan');
    }
}
