<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;
    protected $table = 'pegawai';
    protected $fillable = [
        'nip', 
        'nama', 
        'tempat_lahir', 
        'alamat', 
        'tanggal_lahir', 
        'jenis_kelamin', 
        'golongan', 
        'eselon', 
        'jabatan', 
        'agama', 
        'no_hp', 
        'npwp', 
        'image', 
        'unit_kerja_id', 
        'user_id',
    ];
    
    // Relasi ke model UnitKerja
    public function unitKerja()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id'); // Tabel pegawai punya foreign key unit_kerja_id
    }

    // Pegawai.php
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}