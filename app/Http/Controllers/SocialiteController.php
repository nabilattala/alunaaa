<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

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
            $token = $this->generateAuthToken($user);

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token,
                'first_login' => $firstLogin
            ]);
        } catch (Exception $e) {
            Log::error('Error occured while handling google login callback: ' . $e->getMessage(), [
                'trace' => $e->getTrace()
            ]);
            return response()->json(['error' => 'Gagal login'], 500);
        }
    }

    private function generateAuthToken($user)
    {
        try {
            $accessToken = JWTAuth::fromUser($user);

            return $accessToken;
        } catch (Exception $e) {
            Log::error('Error occured: ' . $e->getMessage(), [
                'trace' => $e->getTrace()
            ]);

            throw $e;
        }
    }

}
