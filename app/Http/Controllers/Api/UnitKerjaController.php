<?php

namespace App\Http\Controllers\Api;

use App\Models\UnitKerja;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UnitKerjaController extends Controller
{



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
        
        $user = auth()->user();


        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can add unit kerja.'], 403);
        }


        $request->validate([
            'nama_unit_kerja' => 'required|unique:unit_kerja,nama_unit_kerja',  // Pastikan nama unit kerja unik
            'alamat_unit_kerja' => 'required',  
        ]);


        $unitKerja = UnitKerja::create([
            'nama_unit_kerja' => $request->nama_unit_kerja,
            'alamat_unit_kerja' => $request->alamat_unit_kerja,
        ]);


        LogActivity::create([
            'user_id' => $user->id,  
            'activity_type' => 'Menambah Bagian Baru dalam Unit Kerja Yaitu ' . $unitKerja->nama_unit_kerja,
        ]);


        return response()->json($unitKerja, 201);
    }



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
            'user_id' => auth()->user()->id,  
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
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can add unit kerja.'], 403);
        }

        $unitKerja = UnitKerja::findOrFail($id);

        LogActivity::create([
            'user_id' => $user->id,  
            'activity_type' => 'Menghapus Unit Kerja Yaitu ' . $unitKerja->nama_unit_kerja,  // Jenis aktivitas yang dilakukan
        ]);

        $unitKerja->delete();
        return response()->json([
            'message' => 'Unit Kerja berhasil dihapus'
        ], 200);
    }

}
