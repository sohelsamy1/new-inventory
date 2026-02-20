<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function userRegistrationPage(){
        return view('pages.auth.registration-page');
    }

     public function userLoginPage(){
        return view('pages.auth.login-page');
    }

     public function resetPasswordPage(){
        return view('pages.auth.reset-pass-page');
    }

    public function sendOtpPage(){
        return view('pages.auth.send-otp-page');
    }

      public function verifyOtpPage(){
        return view('pages.auth.verify-otp-page');
    }

      function profilePage(){
        return view('pages.dashboard.profile-page');
    }

      // Registration
    public function userRegistration(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:3',
                'mobile' => 'required|string|max:15'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            User::create([
                'first_name' => $request->firstName,
                'last_name' => $request->lastName,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'mobile' => $request->mobile,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User Registration Successful'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User Registration Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     // Login
    public function userLogin(Request $request)
    {
         try {
            // Step 1: Validate input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:3'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Step 2: Check user existence
            $user = User::where('email', $request->email)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                // Step 3: Generate token
                $token = JWTToken::createToken($user->email, $user->id);

                return response()->json([
                    'status' => 'success',
                    'message' => 'User Login successful'
                ], 200)->cookie('token', $token, 60 * 24); // 1 day
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid email or password'
                ], 401);
            }

            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'error' => app()->environment('production') ? 'Server Error' : $e->getMessage()
                ], 500);
            }
    }

    // Logout
    public function logout()
    {
        return redirect('/userLogin')
        ->withCookie(cookie('token', null, -1));
    }

    // Send OTP
    public function sendOTP(Request $request)
    {
        try {
            // Step 1: Validate email field
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Step 2: Generate OTP and send mail
            $otp = rand(1000, 9999);
            $email = $request->email;

            Mail::to($email)->send(new OTPMail($otp));

            User::where('email', $email)->update(['otp' => $otp]);

            return response()->json([
                'status' => 'success',
                'message' => 'OTP sent successfully'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => app()->environment('production') ? 'Server Error' : $e->getMessage()
            ], 500);
        }
    }

}
