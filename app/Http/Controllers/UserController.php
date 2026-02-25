<?php

namespace App\Http\Controllers;

use App\Helper\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Exception;
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

    // Verify OTP
    public function verifyOTP(Request $request)
    {
        try {
            // Step 1: Validate input
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
                'otp' => 'required|digits:4'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Step 2: Find user with correct OTP
            $user = User::where([
                'email' => $request->email,
                'otp' => $request->otp
            ])->first();

            if ($user) {
                // OTP matched, reset OTP and generate token
                User::where('email', $request->email)->update(['otp' => 0]);

                $token = JWTToken::createTokenForResetPassword($request->email);

                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP verified successfully',
                ], 200)->cookie('token', $token, 60 * 5); // 24 hours
            } else {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Invalid OTP or email'
                ], 401);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => app()->environment('production') ? 'Server Error' : $e->getMessage()
            ], 500);
        }
    }


    // Reset Password
    public function resetPassword(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:8|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Validation Error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $email = $request->header('email');
            $password = $request->password;

            User::where('email', $email)->update([
                'password' => Hash::make($password)
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password reset successfully'
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to reset password',
                'error' => app()->environment('production') ? 'Server Error' : $e->getMessage()
            ], 500);
        }
    }


     //Get user profile
    public function userProfile(Request $request){
        $email = $request->header('email');
        $user = User::where('email', $email)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'User Profile',
            'data' => $user
        ], 200);
    }


     // User profile update
    public function updateUserProfile(Request $request){
       try{
            $email = $request->header('email');
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $mobile = $request->mobile;
            $password = $request->password;

            User::where('email', $email)->update([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'mobile' => $mobile,
                'password' => $password
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User Profile updated successfully'
            ], 200);
       }catch(Exception $e){
            return response()->json([
                'status' => 'failed',
                'message' => 'Unable to update user profile'
            ], 200);
       }
    }
}
