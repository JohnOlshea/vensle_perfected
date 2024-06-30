<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        try {
            $request->validate(['email' => 'required|email']);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['email' => 'User not found'], 422);
            }

            // Generate a password reset token
            $token = \Illuminate\Support\Str::random(60);

            // Save the token in the password_resets table
            PasswordResetToken::updateOrCreate(
                ['email' => $user->email],
                ['token' => $token, 'created_at' => now()]
            );

            // Return a response with the generated token
            return response()->json(['message' => 'Password reset token generated successfully', 'token' => $token]);
        } catch (\Exception $e) {
            //return response()->json(['error' => 'Failed to generate password reset token.'], 500);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reset(Request $request)
    {
        try {
            $request->validate(
                [
                'email' => 'required|email',
                'token' => 'required|string',
                'password' => 'required|string|confirmed',
                ]
            );



            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill(
                        [
                        'password' => bcrypt($password),
                        'remember_token' => \Illuminate\Support\Str::random(60),
                        ]
                    )->save();
                }
            );

            return $status == Password::PASSWORD_RESET
                ? response()->json(['status' => __($status)])
                : response()->json(['email' => __($status)], 422);
        } catch (\Exception $e) {
            //return response()->json(['error' => 'Password could not be reset.'], 500);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
