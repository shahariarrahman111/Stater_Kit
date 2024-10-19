<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use App\Mail\OTPMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{ 
    function UserRegistration(Request $request)
    {
       try {
            $request->validate([
                'firstName' => 'required|string|max:50',
                'lastName' => 'required|string|max:50',
                'email' => 'required|string|email|max:50|unique:users,email',
                'mobile' => 'required|string|max:50',
                'password' => 'required|string|min:3'
            ]);
    
            User::create([
                'firstName'=> $request->input('firstName'),
                'lastName'=> $request->input('lastName'),
                'email'=> $request->input('email'),
                'mobile'=> $request->input('mobile'),
                'password'=> Hash::make($request->input('password')),
            ]);
    
            return response()->json([
                'status'=> 'success',
                'message'=> 'User Registration Successful'
            ]);
         
        }catch (Exception $e) {

        return response()->json([
            'status'=> 'error',
                'message'=> $e->getMessage()
        ]);

       }
    }



    


    function UserLogin(Request $request)
    {
      try {

        $request->validate([
            'email'=> 'required|string|max:50',
           'password'=> 'required|string|min:4'
       ]);

       $user = User::where('email', $request->input('email'))->first();

       if (!$user || !Hash::check($request->input('password'), $user->password))
       {
           return response()->json([
               'status'=> 'Failed',
               'message'=> 'Invalid User'
           ]);
       }else {

           $token = $user->createToken('authToken')->plainTextToken;

           return response()->json([
               'status'=> 'success',
               'message' => 'Loging Successful',
               'token'=> $token,
           ]);
       }



      }catch (Exception $e){

        return response()->json([
            'status'=> 'error',
            'message'=> $e->getMessage()
        ]) ;
      }
         
        
    }


    function  SendOTPCode (Request $request)
    {
        try{
            $request->validate([
                'email'=> 'required|string|max:50',
            ]);

            $email = $request->input('email');
            $otp  = rand(10000, 99999);
            $count = User::where('email', '=', $email)->count();

            if ($count == 1){
                Mail::to( $email)->send(new OTPMail($otp));
                User::where('email', '=', $email)->update(['otp'=>  $otp]);
                
                return response()->json([
                    'status'=> 'success',
                    'message'=> '5 Digit OTP Code Send Your Email'
                ]);
               
            }else{
                return response()->json([
                    'status'=> 'error',
                    'message'=> 'Invalid Email Address'
                ]);
            }
        }catch (Exception $e){
            return response()->json([
                'status'=> 'error',
                    'message'=> $e->getMessage()
            ]) ;
        }
    }


    function VerifyOTP(Request $request)
    {
        try{
            $request->validate([
                'email'=> 'required|string|max:50',
                'otp' => 'required|string|min: 5'
            ]);

            $email = $request->input('email');
            $otp = $request->input('otp');

            $user =User::where('email', '=', $email)->where('otp', '=', $otp)->first();

            if (!$user) {
                return response()->json([
                    'status'=> 'error',
                        'message'=> 'Invalid OTP'
                ]);
            }else{
                User::where('email', '=', $email)->update(['otp'=> '0']);

                $token = $user->createToken('authToken')->plainTextToken;

                return response()->json([
                    'status'=> 'success',
                    'message'=> 'OTP Verification Successful',
                    'token'=> $token
                ]);
            }

        }catch (Exception $e){
            return response()->json([
                'status'=> 'error',
                'message'=> $e->getMessage()
            ]);
        }
    }



    function ResetPasswrod(Request $request){
        try{

            $request->validate([
                'password'=> 'validate|string|min:4'
            ]);

            $id = Auth::id();
            $password = $request->input('password');

            User::where('id', '=', $id)->update(['password'=> Hash::make($password)]);

            return response()->json([
                'status'=> 'success',
                'message'=> 'ResetSuccessful'
            ]);

        }catch (Exception $e){
            return response()->json([
                'status'=> 'error',
                    'message'=> $e->getMessage()
            ]);
        }
    }


    function UserLogout (Request $request)
    {
        $request->user()->tokens()->delete();

        return redirect('/login');
    }


    function UserProfile (Request $request)
    {
        return Auth::user();
    }


    function UpdateProfile(Request $request)
    {
        try{

            $request->validate([
                'firstName'=> 'required|string|max:50',
                'lastName'=> 'required|string|max:50',
                'mobile'=> 'required|string|max:50'
            ]);
    
            User::where('id', '=', Auth::id())->update([
                'firstName'=> $request->input('firstName'),
                'lastName'=> $request->input('lastName'),
                'mobile'=> $request->input('mobile')
            ]);
    
            return response()->json([
                'status'=> 'success',
                'message'=> 'Profile Updated Successful'
            ]);

        }catch (Exception $e){

            return response()->json([
                'status'=> 'error',
                    'message'=> $e->getMessage()
            ]) ;
        }
    }



}
