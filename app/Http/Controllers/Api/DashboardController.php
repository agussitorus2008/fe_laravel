<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Menambahkan middleware JWT untuk memastikan pengguna terautentikasi
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        // Mendapatkan pengguna yang sedang login
        $user = Auth::user();

        // Mengecek apakah user sedang login
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401); // Jika tidak ada user (belum login)
        }

        // Mengembalikan data pengguna sebagai JSON
        return response()->json([
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }
}
