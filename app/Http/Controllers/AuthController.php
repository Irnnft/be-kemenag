<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial salah.'],
            ]);
        }

        // Cek Status Aktif Madrasah (Khusus Operator)
        if ($user->role === 'operator' && $user->id_madrasah) {
            $madrasah = \App\Models\Madrasah::find($user->id_madrasah);
            if ($madrasah && $madrasah->status_aktif == 0) {
                throw ValidationException::withMessages([
                    'username' => ['Akun madrasah dinonaktifkan oleh Admin.'],
                ]);
            }
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user->load('madrasah')
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        return $request->user()->load('madrasah');
    }
}
