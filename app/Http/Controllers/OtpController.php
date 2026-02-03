<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OtpController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function show()
    {
        return view('auth.verify-otp');
    }

    /**
     * Send OTP to a user's email.
     *
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendOtp(User $user): \Illuminate\Http\JsonResponse
    {
       
        $otp = rand(100000, 999999);

        // Store or update OTP in database
        UserOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'otp' => $otp,
                'email' => $user->email,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        // Send OTP email
        Mail::raw("Your OTP is: $otp", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Login OTP');
        });

        return response()->json([
            'message' => 'OTP sent to your email (check laravel.log)'
        ]);
    }

    /**
     * Verify the OTP and log in the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = Auth::id();

        $record = UserOtp::where('user_id', $userId)
            ->where('otp', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

        if (!$record) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP']);
        }

     

        // Delete the OTP after successful login
        $record->delete();

        return redirect()->intended('/dashboard');
                         
    }
}