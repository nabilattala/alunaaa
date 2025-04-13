<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;

class UserController extends Controller
{
    // Menampilkan seluruh data pengguna yang aktif

    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function index()
    {
        $user = Auth::user();
        try {
            $userInfo = User::where('id', $user->id)->first();

            return $this->apiResponse->success(
                'User data retrieved successfully.',
                new UserResource($userInfo),
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            $this->logService->saveErrorLog(
                "Error occurred while fetching user data",
                $this->pathController . 'UserController:index',
                $e
            );
            return $this->apiResponse->internalServerError("An error occurred while fetching user data.");
        }
    }

    // Menampilkan data pengguna berdasarkan ID
    public function show($id)
    {
        // Mencari pengguna berdasarkan ID
        $user = User::find($id);

        // Jika pengguna tidak ditemukan, mengembalikan respon error
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
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
            'role' => 'required|in:admin,kelas,pengguna',  // Role harus salah satu dari admin, kelas, atau pengguna
            'is_active' => 'boolean',  // Kolom is_active opsional, default true
        ]);

        // Jika validasi gagal, mengembalikan error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Membuat pengguna baru
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),  // Password harus dienkripsi
            'role' => $request->role,  // Menetapkan role pengguna
            'is_active' => $request->is_active ?? true,  // Default is_active true jika tidak diisi
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
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Validasi input yang diterima
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:admin,kelas,pengguna',
            'is_active' => 'sometimes|boolean',
        ]);

        // Jika validasi gagal, mengembalikan error
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Mengupdate data pengguna dengan data yang diterima dari request
        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }
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
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Menghapus pengguna
        $user->delete();

        // Mengembalikan respon bahwa pengguna telah berhasil dihapus
        return response()->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'address' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profilephoto');
            $filename = time() . '' . $file->getClientOriginalName();
            $file->storeAs('uploads/profile', $filename, 'public');
            $user->profile_photo = 'storage/uploads/profile/' . $filename;
        }

        $user->update($request->except('profile_photo'));

        return new UserResource($user);
    }

    public function setUsername(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
        ]);

        $user->update([
            'username' => $request->username,
        ]);

        return response()->json([
            'message' => 'Username berhasil disimpan',
            'user' => $user,
        ]);
    }
}

