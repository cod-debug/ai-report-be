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
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'date_of_birth' => 'required|date',
            'gender' => 'required',
            'civil_status' => 'required',
            'username' => 'required|unique:users',
            'email' => 'required|unique:users|email',
            'password' => 'required|min:8',
            'department_id' => 'required|integer',
            'course_id' => 'required|integer',
            'permanent_address_region_id' => 'required|integer',
            'permanent_address_province_id' => 'required|integer',
            'permanent_address_city_id' => 'required|integer',
            'permanent_address_barangay_id' => 'required|integer',
            'present_address_region_id' => 'required|integer',
            'present_address_province_id' => 'required|integer',
            'present_address_city_id' => 'required|integer',
            'present_address_barangay_id' => 'required|integer',
        ], [
            'department_id.required' => 'Department is required.',
            'department_id.integer' => 'Department value is invalid.',
            'course_id.required' => 'Course is required.',
            'course_id.integer' => 'Course value is invalid.',
            'permanent_address_region_id.required' => 'Permanent address region is required.',
            'permanent_address_region_id.integer' => 'Permanent address region value is invalid.',
            'permanent_address_province_id.required' => 'Permanent address province is required.',
            'permanent_address_province_id.integer' => 'Permanent address province value is invalid.',
            'permanent_address_city_id.required' => 'Permanent address city is required.',
            'permanent_address_city_id.integer' => 'Permanent address city value is invalid.',
            'permanent_address_barangay_id.required' => 'Permanent address barangay is required.',
            'permanent_address_barangay_id.integer' => 'Permanent address barangay value is invalid.',
            'present_address_region_id.required' => 'Present address region is required.',
            'present_address_region_id.integer' => 'Present address region value is invalid.',
            'present_address_province_id.required' => 'Present address province is required.',
            'present_address_province_id.integer' => 'Present address province value is invalid.',
            'present_address_city_id.required' => 'Present address city is required.',
            'present_address_city_id.integer' => 'Present address city value is invalid.',
            'present_address_barangay_id.required' => 'Present address barangay is required.',
            'present_address_barangay_id.integer' => 'Present address barangay value is invalid.'
        ]);

        if ($validator->fails()) {
            // Handle validation failure
            return $this->validationError($validator->errors());
        }

        User::create([
            ...$request->all(),
            'user_role' => User::USER_ROLE_ALUMNI,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Successfully registered. Kindly wait for admin approval of your account.']);
    }

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
