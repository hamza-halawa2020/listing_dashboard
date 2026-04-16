<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Step 1: Send OTP code to user's email
     */
    public function sendCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        // Rate limit: max 3 attempts per phone per 10 minutes
        $key = 'forgot-password:' . $request->phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'phone' => [__('Too many attempts. Please try again in :seconds seconds.', ['seconds' => $seconds])],
            ]);
        }
        RateLimiter::hit($key, 600);

        $user = User::where('phone', $request->phone)
            ->where('role', '!=', 'admin')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => [__('No account found with this phone number.')],
            ]);
        }

        if (! $user->email) {
            throw ValidationException::withMessages([
                'phone' => [__('This account has no email address associated.')],
            ]);
        }

        // Delete any previous codes for this phone
        PasswordResetCode::where('phone', $request->phone)->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetCode::create([
            'phone'      => $request->phone,
            'code'       => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->email)->send(new PasswordResetCodeMail($code, $user->name));

        return response()->json([
            'message' => __('A reset code has been sent to your email address.'),
            'email_hint' => $this->maskEmail($user->email),
        ]);
    }

    /**
     * Step 2: Verify the OTP code
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code'  => 'required|string|size:6',
        ]);

        $record = PasswordResetCode::where('phone', $request->phone)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'code' => [__('Invalid reset code.')],
            ]);
        }

        if ($record->isExpired()) {
            $record->delete();
            throw ValidationException::withMessages([
                'code' => [__('This code has expired. Please request a new one.')],
            ]);
        }

        return response()->json([
            'message' => __('Code verified successfully.'),
            'valid'   => true,
        ]);
    }

    /**
     * Step 3: Reset the password
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone'                 => 'required|string',
            'code'                  => 'required|string|size:6',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $record = PasswordResetCode::where('phone', $request->phone)
            ->where('code', $request->code)
            ->latest()
            ->first();

        if (! $record || $record->isExpired()) {
            throw ValidationException::withMessages([
                'code' => [__('Invalid or expired reset code.')],
            ]);
        }

        $user = User::where('phone', $request->phone)
            ->where('role', '!=', 'admin')
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => [__('No account found with this phone number.')],
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Invalidate all tokens
        $user->tokens()->delete();

        // Clean up the code and rate limiter
        $record->delete();
        RateLimiter::clear('forgot-password:' . $request->phone);

        return response()->json([
            'message' => __('Password has been reset successfully. Please log in.'),
        ]);
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 2, 3));
        return $masked . '@' . $domain;
    }
}
