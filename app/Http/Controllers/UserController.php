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
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class UserController extends Controller
{
    protected $apiResponse;

    public function __construct(ApiResponse $apiResponse)
    {
        $this->apiResponse = $apiResponse;
    }

    // Get all users with pagination
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10); // Default to 10 users per page
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

    // Get the current authenticated user
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

    // Get a specific user by ID
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }
        return new UserResource($user);
    }

    // Store a new user
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,kelas,pengguna',
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $profilePhotoName = null;
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $profilePhotoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profile_photos'), $profilePhotoName);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'is_active' => $request->is_active ?? true,
            'profile_photo' => $profilePhotoName,
        ]);

        return new UserResource($user);
    }

    // Update a specific user
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
            'is_active' => 'sometimes|boolean',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->has('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        // Handle profile photo
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $profilePhotoName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profile_photos'), $profilePhotoName);

            // Delete old profile photo
            if ($user->profile_photo && file_exists(public_path('uploads/profile_photos/' . $user->profile_photo))) {
                unlink(public_path('uploads/profile_photos/' . $user->profile_photo));
            }

            $user->profile_photo = $profilePhotoName;
        }

        $user->update($request->except('profile_photo'));

        return new UserResource($user);
    }

    // Delete a specific user
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Delete profile photo if exists
        if ($user->profile_photo && file_exists(public_path('uploads/profile_photos/' . $user->profile_photo))) {
            unlink(public_path('uploads/profile_photos/' . $user->profile_photo));
        }

        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], Response::HTTP_OK);
    }

    // Update user profile (address, phone number, profile photo)
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
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/profile_photos'), $fileName);

            // Delete old photo
            if ($user->profile_photo && file_exists(public_path('uploads/profile_photos/' . $user->profile_photo))) {
                unlink(public_path('uploads/profile_photos/' . $user->profile_photo));
            }

            $user->profile_photo = $fileName;
        }

        $user->save();

        return new UserResource($user);
    }

    // Update username of current user
    public function setUsername(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
        ]);

        $user->update([
            'username' => $request->username,
        ]);

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

        // Generate 6 digit random OTP
        $otp = rand(100000, 999999);

        // Simpan ke database
        $user->otp_code = $otp;
        $user->otp_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Kirim email (pakai Mail)
        Mail::raw("Your OTP code is: {$otp}", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Forgot Password OTP Code');
        });

        return response()->json([
            'message' => 'OTP has been sent to your email.'
        ], Response::HTTP_OK);
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

        // OTP valid — lanjut reset password
        return response()->json([
            'message' => 'OTP verified successfully.'
        ], Response::HTTP_OK);
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

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ], Response::HTTP_OK);
    }


}
