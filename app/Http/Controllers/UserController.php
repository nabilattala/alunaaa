<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Menampilkan seluruh data pengguna
    public function index()
    {
        // Mengambil semua data pengguna dan mengembalikannya sebagai resource
        return UserResource::collection(User::all());
    }

    // Menampilkan data pengguna berdasarkan ID
    public function show($id)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($id);
        
        // Jika pengguna tidak ditemukan, mengembalikan respon error
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Mengembalikan data pengguna yang ditemukan dalam bentuk resource
        return new UserResource($user);
    }

    // Menyimpan data pengguna baru
    public function store(Request $request)
    {
        // Validasi input yang diterima
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',  // Nama pengguna wajib diisi
            'email' => 'required|string|email|max:255|unique:users',  // Email wajib unik
            'password' => 'required|string|min:8',  // Password minimal 8 karakter
            'role' => 'required|in:admin,kelas,pengguna'  // Role harus salah satu dari admin, kelas, atau pengguna
        ]);

        // Jika validasi gagal, mengembalikan error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Membuat pengguna baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),  // Password harus dienkripsi
            'role' => $request->role,  // Menetapkan role pengguna
        ]);

        // Mengembalikan data pengguna yang baru dibuat sebagai resource
        return new UserResource($user);
    }

    // Mengupdate data pengguna berdasarkan ID
    public function update(Request $request, $id)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($id);
        
        // Jika pengguna tidak ditemukan, mengembalikan respon error
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Mengupdate data pengguna dengan data yang diterima dari request
        $user->update($request->all());

        // Mengembalikan data pengguna yang telah diperbarui sebagai resource
        return new UserResource($user);
    }

    // Menghapus pengguna berdasarkan ID
    public function destroy($id)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($id);
        
        // Jika pengguna tidak ditemukan, mengembalikan respon error
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Menghapus pengguna
        $user->delete();
        
        // Mengembalikan respon bahwa pengguna telah berhasil dihapus
        return response()->json(['message' => 'User deleted successfully']);
    }
}
