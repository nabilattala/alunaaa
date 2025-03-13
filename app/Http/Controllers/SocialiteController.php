<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate([
                'email' => $googleUser->getEmail(),
            ], [
                'name' => $googleUser->getName(),
                'password' => bcrypt('password_default'),
            ]);

            // Jika user belum memiliki username, beri tanda bahwa ini login pertama
            $firstLogin = !$user->username;

            // Generate Sanctum Token untuk API
            $token = $user->createToken('google-auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token,
                'first_login' => $firstLogin
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal login'], 500);
        }
    }

}
