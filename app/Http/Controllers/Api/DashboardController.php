<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
       
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401); // Jika tidak ada user (belum login)
        }
        return response()->json([
            'username' => $user->username,
            'email' => $user->email,
        ]);
    }
}
