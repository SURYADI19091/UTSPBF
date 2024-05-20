<?php

namespace App\Http\Controllers\Auth\Socialite;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;


class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        try {
            return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to redirect to Google: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();

            // Check if user already exists in your database (optional)
            $existingUser = User::where('email', $user->email)->first();

            if (!$existingUser) {
                // Create a new user record if it doesn't exist
                $existingUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    // ... other user fields
                ]);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($existingUser);

            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => $existingUser, // Optional: Include user information
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage(),
            ], 401);
        }
    }
}
