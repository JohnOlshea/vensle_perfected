<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CustomPasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomPasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email not found'], 404);
        }

        $token = Str::random(60);

        CustomPasswordReset::updateOrCreate(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()]
        );

        return response()->json(['message' => 'Password reset token generated successfully', 'token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate(
            [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
            ]
        );

        $passwordReset = CustomPasswordReset::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['error' => 'Invalid email or token'], 422);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user's password
        $user->update(['password' => bcrypt($request->password)]);

        // Delete the password reset record
        $passwordReset->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
