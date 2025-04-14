<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'pengguna',
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Set user is_active = 1 saat login
        $user = auth('api')->user();
        $user->is_active = 1;
        $user->save();

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => new UserResource($user)
        ], 200);
    }


    public function logout()
    {
        // Set user is_active = 0 saat logout
        $user = auth('api')->user();
        if ($user) {
            $user->is_active = 0;
            $user->save();
        }

        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function user()
    {
        $user = auth('api')->user();
        return response()->json(['user' => $user]);
    }
}
