<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;

class PasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $response = Password::sendResetLink(
            $request->only('email')
        );

        return response()->json([
            'status' => $response == Password::RESET_LINK_SENT
                ? 'Password reset link sent.'
                : 'Failed to send reset link.',
        ]);
    }
}
