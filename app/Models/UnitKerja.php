<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory;
    protected $table = 'unit_kerja';
    protected $fillable = [
        'nama_unit_kerja', 'alamat_unit_kerja'
    ];

    // Relasi ke model Pegawai
    public function pegawais()
    {
        return $this->hasMany(Pegawai::class); // Satu unit kerja bisa punya banyak pegawai
    }
}
