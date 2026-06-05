<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\ResetPasswordCode;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset code to the given email address.
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $email = $request->email;
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($code),
            'created_at' => Carbon::now()
        ]);

        // Send email
        Mail::to($email)->send(new ResetPasswordCode($code));

        // Tulis OTP ke file log terpisah agar mudah untuk demonstrasi
        \Illuminate\Support\Facades\File::append(
            storage_path('logs/otp.log'), 
            "[" . now()->format('Y-m-d H:i:s') . "] Email: {$email} | Kode OTP: {$code}\n"
        );

        return response()->json(['message' => 'Kode verifikasi telah dikirim ke email Anda.']);
    }

    /**
     * Verify the 6-digit code.
     */
    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6'
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return response()->json(['message' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa.'], 400);
        }

        // Check expiry (e.g., 15 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Kode verifikasi sudah kedaluwarsa.'], 400);
        }

        if (!Hash::check($request->code, $record->token)) {
            return response()->json(['message' => 'Kode verifikasi salah.'], 400);
        }

        return response()->json(['message' => 'Kode verifikasi valid.']);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->code, $record->token)) {
            return response()->json(['message' => 'Kode verifikasi tidak valid.'], 400);
        }

        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Kode verifikasi sudah kedaluwarsa.'], 400);
        }

        // Update password
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Kata sandi berhasil diperbarui.']);
    }
}
