<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth,Validator, Password, DB, Hash;
use App\Mail\ForgetPasswordMail;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function randomKey($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
      
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
      
        return $randomString;
    }

    public function login(Request $request)
    {
        $rules = array(
            'password'  => "required|min:2|max:30",
            'email' => "required|email",
        );
        
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
       
        $credentials = $request->only(['email', 'password']);
        
    	if (Auth::attempt($credentials)) {
			$user = Auth::user()->load('role');
			$user->token = $user->createToken('MySecretAppHashToken')->accessToken;
            return response()->json([
                'success' => true,
                'message' => 'User register successfully.',
                'data' => $user
            ], 200);
		}
		else {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => null
            ], 401);
			// return response()->json(['error' => 'Unauthorized'], 401);
		}
    }
   
    public function register(Request $request)
    {
        $rules = array(
            'name' => 'required',
			'email' => 'required|email|unique:users,email',
			'password' => 'required',
			'c_password' => 'required|same:password',
        );
        
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }

    	$user = User::create([
    		'name' => $request->name,
    		'email' => $request->email,
    		'password' => \Hash::make($request->password),
            'role_id' => $request->has('role_id') ? $request->role_id : 2
        ]);

        $user = User::with('role')->where('id',$user->id)->first();
        
    	$user->token = $user->createToken('MySecretAppHashToken')->accessToken;
    	return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'data' => $user
        ], 200);
    }

    public function forgetPassword(Request $request){
        $rules = array(
            'email' => 'required|email',
        );
        
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
        $email = $request->email;

        if (User::where('email',$email)->doesntExist()) {
            return response()->json([
                'success' => false,
                'message' => 'This email not exist in record.',
                'data' => null
            ], 401);
        }

        $token = $this->randomKey(50);

        try{
            $user = User::where('email', $request->email)->first();
            $up = User::find($user->id);
            $up->reset_token = $token;
            $up->save();

            // Mail Send to User 
            Mail::to($email)->send(new ForgetPasswordMail($token));

            return response()->json([
                'success' => true,
                'message' => 'Reset Password Mail send on your email.',
                'data' => null
            ], 200);

        }catch(Exception $exception){

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
                'data' => null
            ], 400);
        }
    } // end method

    public function ResetPassword(Request $request){

        $rules = array(
            'token' => 'required|min:38',
            'password' => 'required|min:6',
            'confirm_password' => 'required|same:password'
        );
        
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }

        $user = User::where('reset_token', '!=', null)->where('reset_token',$request->token)->first();
        if(!$user){
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
                'data' => null
            ], 400);
        }

        $password = Hash::make($request->password);

         $up = User::find($user->id);
         $up->password = $password;
         $up->reset_token = null;
         $up->save();

         return response()->json([
            'success' => true,
            'message' => 'Password Change Successfully',
            'data' => null
        ], 200);
    

    }// end method 
   
	public function adminLogin(Request $request)
	{

        $rules = array(
            'email' => 'required|email',
    		'password' => 'required',
        );
        
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }

        $credentials = $request->only(['email', 'password']);
        
		if (Auth::attempt($credentials)) {
			
			$user = Auth::user()->load('role');
			$user->token = $user->createToken('MySecretAppHashToken', ['*'])->accessToken;
			return response()->json([
                'success' => true,
                'message' => 'Logged in successfully.',
                'data' => $user
            ], 200);
		}
		else {
			return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
                'data' => null
            ], 401);
		}
	}
	
	public function adminRegister(Request $request)
	{
        $rules = array(
            'name' => 'required|min:2',
            'password'  => "required|min:2|max:30",
            'email' => "required|email|unique:users,email",
            'password' => 'required',
			'c_password' => 'required|same:password',
        );
        
        $validator = Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
                'data' => null
            ], 400);
        }
		$user = User::create([
			'name' => $request->name,
			'email' => $request->email,
			'password' => bcrypt($request->password),
            'role_id' => 1
		]);
        $user = User::with('role')->where('id', $user->id)->first();
		$user->token = $user->createToken('MySecretAppHashToken', ['*'])->accessToken;
		return response()->json([
            'success' => true,
            'message' => 'Admin created successfully.',
            'data' => $user
        ], 200);
	}
}
