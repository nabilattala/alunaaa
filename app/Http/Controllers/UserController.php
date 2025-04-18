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
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;

class UserController extends Controller
{
    protected $apiResponse;

    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $users = User::paginate($perPage);
            return $this->apiResponse->success(
                UserResource::collection($users),
                'All users retrieved successfully.',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->apiResponse->error(
                "An error occurred while fetching users data.",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function currentUser()
    {
        $user = Auth::user();
        try {
            return $this->apiResponse->success(
                new UserResource($user),
                'Current user data retrieved successfully.',
                Response::HTTP_OK
            );
        } catch (Exception $e) {
            return $this->apiResponse->error(
                "An error occurred while fetching user data.",
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new UserResource($user);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,kelas,pengguna',
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'address' => 'nullable|string|max:500',
            'otp_code' => 'nullable|digits:6',
            'otp_expires_at' => 'nullable|date',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $profilePhotoUrl = null;
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photos'), $fileName);
            $profilePhotoUrl = url('uploads/profile_photos/' . $fileName);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'otp_code' => $request->otp_code,
            'otp_expires_at' => $request->otp_expires_at,
            'created_at' => now(),
            'updated_at' => now(),
            'is_active' => $request->is_active ?? true,
            'profile_photo' => $profilePhotoUrl,
        ]);

        return new UserResource($user);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'sometimes|string|max:255|unique:users,username,' . $user->id,
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|in:admin,kelas,pengguna',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'otp_code' => 'nullable|digits:6',
            'otp_expires_at' => 'nullable|date',
            'is_active' => 'sometimes|boolean',
            'profile_photo' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $request->only([
            'username',
            'email',
            'role',
            'phone_number',
            'address',
            'otp_code',
            'otp_expires_at',
            'is_active'
        ]);

        foreach ($data as $key => $value) {
            $user->{$key} = $value;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photos'), $fileName);

            // Delete old photo if exists
            if ($user->profile_photo && file_exists(public_path('uploads/profile_photos/' . basename($user->profile_photo)))) {
                @unlink(public_path('uploads/profile_photos/' . basename($user->profile_photo)));
            }

            $user->profile_photo = url('uploads/profile_photos/' . $fileName);
        }

        $user->save();

        return new UserResource($user);
    }


    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if ($user->profile_photo && file_exists(public_path('uploads/profile_photos/' . basename($user->profile_photo)))) {
            unlink(public_path('uploads/profile_photos/' . basename($user->profile_photo)));
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = auth()->user();

        if ($request->has('address')) {
            $user->address = $request->address;
        }

        if ($request->has('phone_number')) {
            $user->phone_number = $request->phone_number;
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photos'), $fileName);

            if ($user->profile_photo && file_exists(public_path('uploads/profile_photos/' . basename($user->profile_photo)))) {
                unlink(public_path('uploads/profile_photos/' . basename($user->profile_photo)));
            }

            $user->profile_photo = url('uploads/profile_photos/' . $fileName);
        }

        $user->save();

        return new UserResource($user);
    }

    public function setUsername(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
        ]);

        $user->username = $request->username;
        $user->save();

        return response()->json([
            'message' => 'Username successfully updated',
            'user' => $user,
        ]);
    }

    public function sendOtpForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        Mail::raw("Your OTP code is: {$otp}", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Forgot Password OTP Code');
        });

        return response()->json(['message' => 'OTP has been sent to your email.'], Response::HTTP_OK);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp_code' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Invalid OTP code.'], Response::HTTP_BAD_REQUEST);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP code has expired.'], Response::HTTP_BAD_REQUEST);
        }

        return response()->json(['message' => 'OTP verified successfully. You can now reset your password.'], Response::HTTP_OK);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp_code' => 'required|digits:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || $user->otp_code !== $request->otp_code) {
            return response()->json(['message' => 'Invalid OTP code.'], Response::HTTP_BAD_REQUEST);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return response()->json(['message' => 'OTP code has expired.'], Response::HTTP_BAD_REQUEST);
        }

        $user->password = Hash::make($request->new_password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Password has been reset successfully.'], Response::HTTP_OK);
    }
}
