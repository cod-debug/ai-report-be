<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    //

    public function login(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'username_email' => 'required',
                    'password' => 'required',
                ],
                [
                    'username_email.required' => 'Username / email is required.',
                    'password.required' => 'Password is required.'
                ]
            );
    
            if ($validator->fails()) {
                // Handle validation failure
                return $this->validationError($validator->errors());
            }
    
            $user = null;
    
            // check if auth email and password match
            if(Auth::attempt([
                'email' => $request->username_email,
                'password' => $request->password
            ])){
                // select specific user
                $user = User::where('email', $request->username_email)->first();
                // remove existing tokens to invalidate other logins
                $user->tokens()->delete();
            }
    
            // check if auth username and password match
            if(Auth::attempt([
                'username' => $request->username_email,
                'password' => $request->password
            ])){
                // select specific user
                $user = User::where('username', $request->username_email)->first();
                // remove existing tokens to invalidate other logins
                $user->tokens()->delete();
            }

            if(!$user){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials.',
                ], 400);
            }
    
            if($user->user_role === 2 && $user->status != 'active'){
                // response with token
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kindly wait for admin approval of your account.',
                ], 400);
            } elseif ($user->status != 'active') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid account. Kindly contact system administrator',
                ], 400);
            }

            // response with token
            return response()->json([
                'status' => 'success',
                'message' => 'User Logged In Successfully',
                'data' => [
                    "user_id" => $user->id,
                    "first_name" => $user->first_name,
                    "middle_name" => $user->middle_name,
                    "last_name" => $user->last_name,
                    "role" => $user->user_role,
                ],
                'token' => $user->createToken($user->email)->plainTextToken
            ], 200);
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }

    public function forgotPassword(Request $request){
        try {
            $validator = Validator::make(
                $request->all(),
                [
                    'email' => 'required',
                ],
            );
    
            if ($validator->fails()) {
                // Handle validation failure
                return $this->validationError($validator->errors());
            }

            $message = 'You will receive a temporary password via email.';

            $email = $request->email;

            $user = User::where('email', $email)->orWhere('username', $email)->first();

            if(!$user){
                return response()->json(['message' => $message]);
            }
            
            $random_password = $this->generateRandomString(8);

            $data = [
                'first_name' => $user->first_name,
                'password' => $random_password
            ];

            $user->password = Hash::make($random_password);
            $user->save();

            Mail::to($user->email)->send(new ForgotPassword($data));
            
            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return $this->serverError($e);
        }
    }
    
    public function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
