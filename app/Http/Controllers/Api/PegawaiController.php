<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Pegawai;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{

    /**
     * Display a listing of the resource.
     */

     public function index(Request $request)
        {
            $search = $request->input('search');
            $unitKerjaId = $request->input('unit_kerja_id');
            $limit = $request->input('limit', 2);

            $pegawai = Pegawai::with(['unitKerja', 'user']);
            if ($search) {
                $pegawai->where(function ($query) use ($search) {
                    $query->where('nama', 'LIKE', "%$search%")
                        ->orWhere('nip', 'LIKE', "%$search%");
                });
            }
            if ($unitKerjaId) {
                $pegawai->where('unit_kerja_id', $unitKerjaId);
            }
            $result = $pegawai->paginate($limit);  
            return response()->json($result);
        }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
        $userFromDb = User::find($user->id);

        if ($userFromDb && $userFromDb->role === 'admin') {
            $request->validate([
                'nip' => 'required|unique:pegawai,nip', 
                'nama' => 'required|string',
                'tempat_lahir' => 'required|string',
                'alamat' => 'required|string',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|string',
                'golongan' => 'required|string',
                'eselon' => 'required|string',
                'jabatan' => 'required|string',
                'agama' => 'required|string',
                'no_hp' => 'required|string',
                'npwp' => 'nullable|string', 
                'image' => 'nullable|image', 
                'user_id' => 'required|exists:users,id', 
                'unit_kerja_id' => 'required|exists:unit_kerja,id', 
            ]);
        } else {
            $request->validate([
                'nip' => 'required|unique:pegawai,nip', 
                'nama' => 'required|string',
                'tempat_lahir' => 'required|string',
                'alamat' => 'required|string',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|string',
                'golongan' => 'required|string',
                'eselon' => 'required|string',
                'jabatan' => 'required|string',
                'agama' => 'required|string',
                'no_hp' => 'required|string',
                'npwp' => 'nullable|string', 
                'image' => 'nullable|image', 
                'unit_kerja_id' => 'required|exists:unit_kerja,id', 
            ]);
        }

        $fotoPath = $request->hasFile('image') ? $request->file('image')->store('photos', 'public') : null;
        $user_id = $userFromDb->role === 'admin' ? $request->user_id : $user->id;

        $pegawai = Pegawai::create([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'alamat' => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'golongan' => $request->golongan,
            'eselon' => $request->eselon,
            'jabatan' => $request->jabatan,
            'agama' => $request->agama,
            'no_hp' => $request->no_hp,
            'npwp' => $request->npwp,
            'image' => $fotoPath, 
            'unit_kerja_id' => $request->unit_kerja_id,
            'user_id' => $user_id, 
        ]);

        LogActivity::create([
            'user_id' => $user->id, 
            'activity_type' => ($userFromDb->role === 'admin' ? 'Admin ' : 'User ')  . ' Menambahkan Data Diri Pegawai',
        ]);
    
        return response()->json(['message' => 'Data diri pegawai berhasil ditambahkan'], 201);
    }
    
    
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pegawai = Pegawai::with(['user', 'unitKerja'])->findOrFail($id);
        return response()->json($pegawai);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $pegawai = Pegawai::findOrFail($id);

        $userFromDb = User::find($user->id);
    
        if ($userFromDb && $userFromDb->role === 'admin') {
            $request->validate([
                'nip' => 'required|unique:pegawai,nip,' . $id,
                'nama' => 'required|string',
                'tempat_lahir' => 'required|string',
                'alamat' => 'required|string',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|string',
                'golongan' => 'required|string',
                'eselon' => 'required|string',
                'jabatan' => 'required|string',
                'agama' => 'required|string',
                'no_hp' => 'required|string',
                'npwp' => 'nullable|string',
                'image' => 'nullable|image', 
                'user_id' => 'required|exists:users,id', 
                'unit_kerja_id' => 'required|exists:unit_kerja,id', 
            ]);
        } else {
            if ($pegawai->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403); 
            }
    
            $request->validate([
                'nip' => 'required|unique:pegawai,nip,' . $id, 
                'nama' => 'required|string',
                'tempat_lahir' => 'required|string',
                'alamat' => 'required|string',
                'tanggal_lahir' => 'required|date',
                'jenis_kelamin' => 'required|string',
                'golongan' => 'required|string',
                'eselon' => 'required|string',
                'jabatan' => 'required|string',
                'agama' => 'required|string',
                'no_hp' => 'required|string',
                'npwp' => 'nullable|string',
                'image' => 'nullable|image',
                'unit_kerja_id' => 'required|exists:unit_kerja,id', 
            ]);
        }
    
        $fotoPath = $request->hasFile('image') 
        ? $request->file('image')->store('photos', 'public') 
        : $pegawai->image;
    
        $pegawai->update([
            'nip' => $request->nip,
            'nama' => $request->nama,
            'tempat_lahir' => $request->tempat_lahir,
            'alamat' => $request->alamat,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'golongan' => $request->golongan,
            'eselon' => $request->eselon,
            'jabatan' => $request->jabatan,
            'agama' => $request->agama,
            'no_hp' => $request->no_hp,
            'npwp' => $request->npwp,
            'image' => $fotoPath, 
            'unit_kerja_id' => $request->unit_kerja_id,
            'user_id' => $userFromDb->role === 'admin' ? $request->user_id : $user->id, 
        ]);

        LogActivity::create([
            'user_id' => $user->id, 
            'activity_type' => ($userFromDb->role === 'admin' ? 'Admin' : 'User') . ' Memperbarui Data Pegawai
             ' ,
        ]);
    
        return response()->json(['message' => 'Data pegawai berhasil diperbarui'], 200);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $user = auth()->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can delete pegawai data.'], 403);
        }


        if ($pegawai->foto) {
            Storage::delete('public/' . $pegawai->foto);
        }


        $pegawai->delete();

        LogActivity::create([
            'user_id' => $user->id,  
            'activity_type' => 'Admin Menghapus Data Pegawai ',
        ]);


        return response()->json([
            'message' => 'Data pegawai berhasil dihapus.'
        ], 200); 
    }
}
