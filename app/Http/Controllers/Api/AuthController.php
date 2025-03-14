<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\LogActivity;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        // Ambil semua user yang tidak memiliki role 'admin'
        $users = User::where('role', '!=', 'admin')->get();
    
        // Kembalikan data user yang bukan admin
        return response()->json($users);
    }
    
    
    public function login(Request $request)
    {
        // Validasi inputan
        $credentials = $request->only('email', 'password');

        // Cek kredensial untuk otentikasi
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            LogActivity::create([
                'user_id' => $user->id,  // ID pengguna yang login
                'activity_type' => $user->username . ' Telah Login',  // Jenis aktivitas
            ]);

            // Generate token setelah login sukses
            return response()->json([
                'token' => $user->createToken('YourAppName')->plainTextToken, // Token untuk otentikasi selanjutnya
                'user'  => $user, // Data user yang login
            ]);
        }

        // Jika login gagal
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    public function register(Request $request)
    {
        // Validasi inputan dari request
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|string|email|unique:users,email', // Pastikan email unik
            'password' => 'required|string|min:6', // Password minimal 6 karakter
        ]);

        // Membuat user baru
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Enkripsi password
        ]);

        LogActivity::create([
            'user_id' => $user->id,  // ID pengguna yang baru saja mendaftar
            'activity_type' => 'Pengguna Baru',
            'activity_type' => $user->username . ' Pengguna Baru Telah Mendaftar',  // Jenis aktivitas
        ]);

        // Mengembalikan token setelah registrasi
        return response()->json([
            'token' => $user->createToken('YourAppName')->plainTextToken, // Token untuk otentikasi
            'user'  => $user, // Data user yang baru dibuat
        ]);
    }

    public function logout(Request $request)
    {
        // Cek apakah user yang terautentikasi ada
        $user = $request->user();

        // Pastikan user yang terautentikasi ada
        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Mencatat aktivitas logout
        LogActivity::create([
            'user_id' => $user->id,  // ID pengguna yang logout
            'activity_type' => $user->username . ' Telah Logout',  // Menyertakan nama pengguna dalam aktivitas logout
        ]);

        // Cek apakah user yang terautentikasi ada
        $request->user()->tokens->each(function ($token) {
            $token->delete(); // Hapus token yang digunakan oleh user
        });

        return response()->json([
            'message' => 'Logged out successfully' // Memberikan konfirmasi logout berhasil
        ]);
    }
}
