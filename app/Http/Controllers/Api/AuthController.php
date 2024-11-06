<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Notifications\EmailVerificationNotification;


class AuthController extends Controller
{
    use ResponseTrait;

    public function __construct(private Otp $otp)
    {
        $this->otp = $otp;
    }
    public function register(RegisterRequest $request)
    {
        try {
            // Create the user
            $user = User::create(
                $request->validated(),
            );

            // $user->notify(new EmailVerificationNotification($user->email, $this->otp));
            $token = $user->createToken('auth_token')->plainTextToken;
            $user->sendEmailVerificationNotification();
            return $this->successWithToken(message: 'We sent you a LINK, check your email', code: 201, token: $token);
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500);
        }

    }



    public function otp(OtpVerifyRequest $request)
    {
        try {
            $email = $request->input('email');
            $otpCode = $request->input('otp');

            $otpValidation = $this->otp->validate($email, $otpCode);

            if (!$otpValidation->status) {
                return $this->error('Invalid or expired OTP.', 400);
            }

            $user = User::where('email', $email)->first();
            Log::info('User: ' . $user->email_verified_at);
            $user->email_verified_at = now();
            $user->save();

            return $this->success('OTP verified successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    public function verifyOtp(OtpVerifyRequest $request)
    {
        try {
            $email = $request->input('email');
            $otpCode = $request->input('otp');

            $otpValidation = $this->otp->validate($email, $otpCode);

            if (!$otpValidation->status) {
                return $this->error('Invalid or expired OTP.', 400);
            }

            $user = User::where('email', $email)->first();
            $user->email_verified_at = now();
            $user->save();

            return $this->success('OTP verified successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    

    public function login(LoginRequest $request)
    {
        try {
            $request->validated($request->all());

            $user = null;

            if ($request->filled('google_id')) {
                $user = User::firstOrCreate(
                    ['google_id' => $request->google_id], // Check by google_id
                    [
                        'name' => $request->name,
                        'email' => $request->email, 
                    ]
                );
            }

            elseif ($request->filled('facebook_id')) {
                $user = User::firstOrCreate(
                    ['facebook_id' => $request->facebook_id], // Check by facebook_id
                    [
                        'name' => $request->name,
                        'email' => $request->email,
                    ]
                );
            }

            // If no social login is present, fall back to email/password login
            elseif (!Auth::attempt($request->only('email', 'password'))) {
                return $this->error('Credentials do not match', 401);
            }

            // If user was found/created via Google/Facebook, proceed with token creation
            if ($user === null) {
                $user = User::where('email', $request->email)->firstOrFail();
            }

            // Create a new token for the user
            $token = $user->createToken('auth_token')->plainTextToken;

            return $this->successWithToken($user, token: $token);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            $message = 'Invalid verification link';
            return view('email', compact('message'));
        }

        if ($user->hasVerifiedEmail()) {
            $message = 'Email already verified';
            return view('email', compact('message'));
        }

        // Mark the user as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        $message = 'Email verified successfully';
        return view('email', compact('message'));
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->success('Logged out successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

}
