<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLogin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    public function login(Request $request){
//        dd($request->getClientIp());
        if ($request->isMethod('post')) {
            $data = $request->all();
            $rules = [
                "email" => "required|email|exists:users",
                "password" => "required"
            ];
            $customMessage = [
                "email.required" => "Email is required",
                "email.email" => "Email must be valid",
                "email.exists" => "Email does not exists",
                "password.required" => "Password is required"
            ];
            $validator = validator::make($data, $rules, $customMessage);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                $user = User::where('email', $data['email'])->first();
                $access_token = $user->createToken($data['email'])->accessToken;
                User::where('email', $data['email'])->update(['access_token' => $access_token]);

                UserLogin::create([
                    'user_id' => \auth()->id(),
                    'login_datetime' => Carbon::now(),
                    'ip_address' => $request->ip(),
                    'device' => $request->userAgent(),
                ]);

                $message = "User successfully login";
                return response()->json(['message' => $message, "access_token" => $access_token], 201);
            } else {
                $message = "Invalid email or password";
                return response()->json(['message', $message], 422);
            }
        }
    }

    public function logout(Request $request){

        $user = UserLogin::where('user_id',\auth()->id())->where('ip_address',$request->ip())->first();
        $start = Carbon::parse($user->login_datetime);
        $end = Carbon::parse(Carbon::now());
        $user->update([
            'user_id' => \auth()->id(),
            'logout_datetime' => Carbon::now(),
            'duration' => $end->diff($start)->format('%d days %h hours %i minutes %s seconds'),
        ]);

        $token = $request->user()->token();
        $token->revoke();
        $message = "User successfully logout";
        return response()->json(['message' => $message], 200);
    }
}
