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
            // Mengambil parameter pencarian, unit kerja, dan limit halaman dari request
            $search = $request->input('search');
            $unitKerjaId = $request->input('unit_kerja_id');
            $limit = $request->input('limit', 2);  // Default to 10 per page if not provided

            // Query data pegawai dengan relasi unitKerja dan user
            $pegawai = Pegawai::with(['unitKerja', 'user']);

            // Filter berdasarkan pencarian (nama atau NIP)
            if ($search) {
                $pegawai->where(function ($query) use ($search) {
                    $query->where('nama', 'LIKE', "%$search%")
                        ->orWhere('nip', 'LIKE', "%$search%");
                });
            }

            // Filter berdasarkan unit kerja jika diberikan
            if ($unitKerjaId) {
                $pegawai->where('unit_kerja_id', $unitKerjaId);
            }

            // Mendapatkan hasil dengan pagination
            $result = $pegawai->paginate($limit);  // Use paginate() for pagination

            // Mengembalikan hasil dalam format JSON
            return response()->json($result);
        }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Ambil user yang sedang login
        $user = auth()->user();
    
        // Pastikan user ada (misalnya user yang tidak terautentikasi)
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    
        // Ambil data user dari database untuk memastikan role yang benar
        $userFromDb = User::find($user->id);
    
        // Pastikan user dari database ada dan role-nya adalah 'admin'
        if ($userFromDb && $userFromDb->role === 'admin') {
            // Admin dapat menambah data pegawai untuk user lain
            $request->validate([
                'nip' => 'required|unique:pegawai,nip', // Pastikan NIP unik
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
                'npwp' => 'nullable|string', // NPWP boleh kosong
                'image' => 'nullable|image', // Jika ada gambar, pastikan formatnya valid
                'user_id' => 'required|exists:users,id', // Pastikan user_id valid
                'unit_kerja_id' => 'required|exists:unit_kerja,id', // unit_kerja_id harus valid
            ]);
        } else {
            // Jika user bukan admin, maka hanya bisa menambah data dirinya sendiri
            $request->validate([
                'nip' => 'required|unique:pegawai,nip', // Pastikan NIP unik
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
                'npwp' => 'nullable|string', // NPWP boleh kosong
                'image' => 'nullable|image', // Jika ada gambar, pastikan formatnya valid
                'unit_kerja_id' => 'required|exists:unit_kerja,id', // unit_kerja_id harus valid
            ]);
        }
    
        // Menyimpan foto jika ada
        $fotoPath = $request->hasFile('image') ? $request->file('image')->store('photos', 'public') : null;
    
        // Jika admin, gunakan user_id yang dikirim dari request
        // Jika user biasa, gunakan user_id yang sedang login
        $user_id = $userFromDb->role === 'admin' ? $request->user_id : $user->id;
    
        // Membuat pegawai baru dan menambahkan data ke database
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
            'image' => $fotoPath, // Simpan foto jika ada
            'unit_kerja_id' => $request->unit_kerja_id,
            'user_id' => $user_id, // Gunakan user_id dari request atau dari user yang sedang login
            // 'foto' => $fotoPath, // Jika foto ada, simpan foto
        ]);

        LogActivity::create([
            'user_id' => $user->id, // ID pengguna yang menambah data pegawai
            'activity_type' => ($userFromDb->role === 'admin' ? 'Admin ' : 'User ')  . ' Menambahkan Data Diri Pegawai',
        ]);
    
        // Menanggapi dengan pesan sukses
        return response()->json(['message' => 'Data diri pegawai berhasil ditambahkan'], 201);
    }
    
    
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Mengambil pegawai dengan relasi user dan unitKerja
        $pegawai = Pegawai::with(['user', 'unitKerja'])->findOrFail($id);

        // Mengembalikan response dalam format JSON
        return response()->json($pegawai);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Ambil user yang sedang login
        $user = auth()->user();
    
        // Pastikan user ada (misalnya user yang tidak terautentikasi)
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }
    
        // Ambil data pegawai yang akan diupdate
        $pegawai = Pegawai::findOrFail($id);
    
        // Ambil data user dari database untuk memastikan role yang benar
        $userFromDb = User::find($user->id);
    
        // Pastikan user dari database ada dan role-nya adalah 'admin' jika admin yang mengubah data pegawai lain
        if ($userFromDb && $userFromDb->role === 'admin') {
            // Admin dapat mengupdate data pegawai untuk user lain
            $request->validate([
                'nip' => 'required|unique:pegawai,nip,' . $id, // Pastikan NIP unik, kecuali untuk data yang sedang diupdate
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
                'image' => 'nullable|image', // Validasi foto
                'user_id' => 'required|exists:users,id', // Pastikan user_id valid
                'unit_kerja_id' => 'required|exists:unit_kerja,id', // unit_kerja_id harus valid
            ]);
        } else {
            // Jika user bukan admin, maka hanya bisa mengupdate data dirinya sendiri
            // Pastikan user_id pada data pegawai yang akan diupdate adalah milik user yang sedang login
            if ($pegawai->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403); // Tidak boleh mengubah data orang lain
            }
    
            $request->validate([
                'nip' => 'required|unique:pegawai,nip,' . $id, // Pastikan NIP unik
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
                'image' => 'nullable|image', // Validasi foto
                'unit_kerja_id' => 'required|exists:unit_kerja,id', // unit_kerja_id harus valid
            ]);
        }
    
        // Menyimpan foto baru jika ada
        $fotoPath = $request->hasFile('image') 
        ? $request->file('image')->store('photos', 'public') 
        : $pegawai->image;
    
        // Update data pegawai
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
            'image' => $fotoPath, // Simpan foto jika ada
            'unit_kerja_id' => $request->unit_kerja_id,
            'user_id' => $userFromDb->role === 'admin' ? $request->user_id : $user->id, 
        ]);

        LogActivity::create([
            'user_id' => $user->id, // ID pengguna yang melakukan update
            'activity_type' => ($userFromDb->role === 'admin' ? 'Admin' : 'User') . ' Memperbarui Data Pegawai
             ' ,
        ]);
    
        // Menanggapi dengan pesan sukses
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

        // Hapus foto jika ada
        if ($pegawai->foto) {
            Storage::delete('public/' . $pegawai->foto);
        }

        // Hapus data pegawai
        $pegawai->delete();

        LogActivity::create([
            'user_id' => $user->id,  // ID pengguna yang menghapus data
            'activity_type' => 'Admin Menghapus Data Pegawai ',
        ]);

        // Kembalikan response dengan pesan sukses
        return response()->json([
            'message' => 'Data pegawai berhasil dihapus.'
        ], 200); // Menggunakan status 200 OK
    }
}
