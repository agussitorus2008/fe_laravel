<?php

namespace App\Http\Controllers\Api;

use App\Models\UnitKerja;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UnitKerjaController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:admin');
    }


    public function index()
    {
        $unitKerja = UnitKerja::all();
        return response()->json($unitKerja);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Ambil pengguna yang sedang login
        $user = auth()->user();

        // Memastikan hanya admin yang dapat menambah unit kerja
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can add unit kerja.'], 403);
        }

        // Validasi inputan dari request
        $request->validate([
            'nama_unit_kerja' => 'required|unique:unit_kerja,nama_unit_kerja',  // Pastikan nama unit kerja unik
            'alamat_unit_kerja' => 'required',  // Pastikan alamat unit kerja ada
        ]);

        // Menyimpan unit kerja baru
        $unitKerja = UnitKerja::create([
            'nama_unit_kerja' => $request->nama_unit_kerja,
            'alamat_unit_kerja' => $request->alamat_unit_kerja,
        ]);

        // Mencatat aktivitas dalam tabel log_activity
        LogActivity::create([
            'user_id' => $user->id,  // ID pengguna yang sedang login
            'activity_type' => 'Menambah Bagian Baru dalam Unit Kerja Yaitu ' . $unitKerja->nama_unit_kerja,
        ]);

        // Mengembalikan response dengan unit kerja yang baru dibuat
        return response()->json($unitKerja, 201);
    }


    // Menampilkan unit kerja berdasarkan ID
    public function show($id)
    {
        $unitKerja = UnitKerja::findOrFail($id);
        return response()->json($unitKerja);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        // Memastikan hanya admin yang dapat menambah unit kerja
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can add unit kerja.'], 403);
        }

        $request->validate([
            'nama_unit_kerja' => 'required|unique:unit_kerja,nama_unit_kerja,' . $id,
            'alamat_unit_kerja' => 'required',
        ]);

        $unitKerja = UnitKerja::findOrFail($id);

        $unitKerja->update([
            'nama_unit_kerja' => $request->nama_unit_kerja,
            'alamat_unit_kerja' => $request->alamat_unit_kerja,
        ]);

        LogActivity::create([
            'user_id' => auth()->user()->id,  // ID pengguna yang sedang login
            'activity_type' => 'Memperbarui Unit Kerja Yaitu ' . $unitKerja->nama_unit_kerja,  // Jenis aktivitas yang dilakukan
        ]);

        return response()->json($unitKerja);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        $user = auth()->user();

        // Memastikan hanya admin yang dapat menambah unit kerja
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can add unit kerja.'], 403);
        }

        $unitKerja = UnitKerja::findOrFail($id);

        LogActivity::create([
            'user_id' => $user->id,  // ID pengguna yang sedang login
            'activity_type' => 'Menghapus Unit Kerja Yaitu ' . $unitKerja->nama_unit_kerja,  // Jenis aktivitas yang dilakukan
        ]);

        $unitKerja->delete();

        // Mengembalikan response JSON dengan pesan sukses
        return response()->json([
            'message' => 'Unit Kerja berhasil dihapus'
        ], 200);
    }

}
