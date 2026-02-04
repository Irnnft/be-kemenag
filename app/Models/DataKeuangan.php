<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKeuangan extends Model
{
    use HasFactory;

    protected $table = 'data_keuangan';

    protected $fillable = [
        'id_laporan',
        'uraian_kegiatan',
        'volume',
        'satuan',
        'harga_satuan'
    ];

    // total_harga is calculated or handled in DB

    public function laporan()
    {
        return $this->belongsTo(LaporanBulanan::class, 'id_laporan', 'id_laporan');
    }
}
