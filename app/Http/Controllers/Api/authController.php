<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use App\Notifications\ChangePassword;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class authController extends Controller
{
    public function register(Request $request){

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
        ]);

            $token=$user->createToken('token')->plainTextToken;
            $user->token=$token;
            $user->save();

            return response()->json([
                'status'=>201,
                'message'=>'Successfully Registered',
                'data'=>['user_data'=>$user],
            ]);
}

    public function login(Request $request){
            if(! Auth::attempt($request->only('email','password'))){
                return response()->json([
                    'status'=>401,
                    'message'=>'Wrong email or password,please try again',
                    'data'=>[
                        'email' => 'The provided email is incorrect.',
                        'password' => 'The provided password is incorrect.',
                    ]
                ]);
            }
            $user=User::Where('email',$request['email'])->firstOrFail();
            $token=$user->createToken('token')->plainTextToken;
                return response()->json([
                    'status'=>200,
                    'message' => 'Hello '.$user->name.' You have been logged in successfully',
                    'data'=>['user_data'=>$user],
                ]);
    }

    public function logout(Request $request){
        $user=auth()->User();
        $request->User()->currentAccessToken()->delete();
        return response()->json([
            'status'=>200,
            'message' => 'Successfully logged out',
            'data'=>[],
        ]);
    }

    public function forgotPassword(Request $request){
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
        ]);
        $user=User::Where('email',$request->email)->first();

        $verify_code=rand(1000, 9999);
        $user->verify_code=$verify_code;
        $user->expire_at=now()->addMinute(30);
        $user->save();

        Mail::to($user->email)->send(new TestMail($verify_code));

        return response()->json([
            'status'=>200,
            'message'=>'Verification code sent successfully',
            'data'=>['user_data'=>$user],
    ]);
    
    }

    public function resetPassword(Request $request){
        $validated = $request->validate([
            'email' => 'required|email',
            'verify_code'=>'required',
            'password' => 'required|string|min:8|confirmed'
        ]);
        $user =User::where('email',$request->email)->first();
        if (!$user) {
        return response()->json([
            'status' => 422,
            'message' => 'User not found',
            'data' => null,
        ]);
        }
        if($user->verify_code !== $request->verify_code){
            return response()->json([
                'status'=>422,
                'message'=>'Invalid verification code',
                'data'=>['user_data'=>$user],
            ]);
        }
        if ($user->expire_at < now()) {
            return response()->json([
                'status'=>422,
                'message' => 'Verification code has expired',
                'data'=>['user_data'=>$user],
            ]);
        }

        $user->update(['password' => Hash::make($request->password)]);
        Notification::send($user,new ChangePassword());
        return response()->json([
            'status'=>200,
            'message' => 'Password updated Successfully',
            'data'=>['user_data'=>$user],
        ]);
    }

}
