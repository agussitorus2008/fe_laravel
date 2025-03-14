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
        $users = User::where('role', '!=', 'admin')->get();
        return response()->json($users);
    }
    
    
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            LogActivity::create([
                'user_id' => $user->id,  
                'activity_type' => $user->username . ' Telah Login',  
            ]);


            return response()->json([
                'token' => $user->createToken('YourAppName')->plainTextToken, 
                'user'  => $user, 
            ]);
        }

       
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|string|email|unique:users,email', 
            'password' => 'required|string|min:6', 
        ]);


        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
        ]);

        LogActivity::create([
            'user_id' => $user->id,  
            'activity_type' => 'Pengguna Baru',
            'activity_type' => $user->username . ' Pengguna Baru Telah Mendaftar',
        ]);

 
        return response()->json([
            'token' => $user->createToken('YourAppName')->plainTextToken,
            'user'  => $user, 
        ]);
    }

    public function logout(Request $request)
    {

        $user = $request->user();


        if (!$user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }


        LogActivity::create([
            'user_id' => $user->id, 
            'activity_type' => $user->username . ' Telah Logout',  
        ]);

        $request->user()->tokens->each(function ($token) {
            $token->delete(); 
        });

        return response()->json([
            'message' => 'Logged out successfully' 
        ]);
    }
}
