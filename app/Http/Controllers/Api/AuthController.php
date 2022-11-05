<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    use ApiResponses;

    /**
     * Create User
     * @param Request $request
     * @return User 
     */
    public function createUser(Request $request) {
        try {
            // -- Validation
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required|max:20',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:5'
            ]);
            if($validateUser->fails()){
                return $this->error(
                    'Validation error', 
                    401, 
                    $validateUser->errors()
                );
            }

            // -- Create User & send Success response
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            return $this->success(
                'User created successfully',
                200,
                [ 'token' => $user->createToken('API Token')->plainTextToken ]
            );


        } catch (\Throwable $th) {
            Log::error('Error in CREATE_USER______START');
            Log::error($th);
            return $this->error('Oops.. Error occour while registration. Please try again.', 401);
        }
    }


     /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request) {
        try {
            // -- Validation
            $validateUser = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:5'
            ]);
            if($validateUser->fails()){
                return $this->error(
                    'Validation error', 
                    401, 
                    $validateUser->errors()
                );
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return $this->error('These credentials doesn\'t exist', 401);
            }

            // -- Login and create token
            $user = User::where('email', $request->email)->first();
            return $this->success(
                'User Logged In Successfully',
                200,
                ['token' => $user->createToken("API TOKEN")->plainTextToken]
            );

        } catch (\Throwable $th) {
            Log::error('Error in LOGIN_USER______START');
            Log::error($th);
            return $this->error('Oops.. Error occour while Logging you in. Please try again.', 500);
            
        }
    }

    /**
     * Logout User
     * @param BearerToken
     * @return [] 
     */
    public function logout() {
        try{
            auth()->user()->tokens()->delete();
            return $this->success(
                'User Logout successfully',
                200,
                []
            );
        } catch (\Throwable $th) {
            Log::error('Error in LOGOUT_USER______START');
            Log::error($th);
            return $this->error('Oops.. Error occour while Logging you in. Please try again.', 500);
            
        }
    }
}
