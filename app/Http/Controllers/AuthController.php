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
        if ($user->role === 'operator_sekolah' && $user->id_madrasah) {
            $madrasah = \App\Models\Madrasah::find($user->id_madrasah);
            if ($madrasah && $madrasah->status_aktif == 0) {
                throw ValidationException::withMessages([
                    'username' => ['Akun madrasah dinonaktifkan oleh Admin.'],
                ]);
            }
        }

        // Hapus SEMUA token lama user ini supaya tabel tetap bersih
        $user->tokens()->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        \App\Models\ActivityLog::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'action' => 'LOGIN',
            'details' => 'User logged into the system',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user->load('madrasah')
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            \App\Models\ActivityLog::log('LOGOUT', 'User session ended');
            // Hapus SEMUA token user ini (supaya tabel benar-benar bersih)
            $user->tokens()->delete();
        }
        
        return response()->json(['message' => 'Logout berhasil']);
    }

    public function me(Request $request)
    {
        return $request->user()->load('madrasah');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'username' => [
                'required',
                'string',
                'max:50',
                \Illuminate\Validation\Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'username.unique' => 'Username ini sudah digunakan oleh orang lain.',
        ]);

        // Proteksi: Operator tidak boleh ganti username (NPSN)
        $newUsername = $request->username;
        if ($user->role === 'operator_sekolah' && $newUsername !== $user->username) {
            return response()->json(['message' => 'NPSN Madrasah tidak dapat diubah.'], 403);
        }

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'username' => $newUsername,
        ]);

        \App\Models\ActivityLog::log('UPDATE_PROFILE', $user->username, 'Memperbarui data profil mandiri');

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini salah'], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        \App\Models\ActivityLog::log('CHANGE_PASSWORD', $user->username, 'Melakukan penggantian password akun');

        return response()->json(['message' => 'Password berhasil diubah']);
    }
}
