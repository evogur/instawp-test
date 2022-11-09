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
    public function createUser(Request $request)
    {
        try {
            // -- Validation
            $validations = Validator::make(
                $request->all(),
                [
                    'name' => 'required|max:20',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:5'
                ]
            );

            if (!$validations->fails()) {
                // -- Create User & send Success response
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password)
                ]);
                $responseMessage = 'User created successfully';
                $resopnseCode = 201;
                $responseData =  ['token' => $user->createToken('API Token')->plainTextToken];
            } else {
                $responseMessage = 'Validation error';
                $resopnseCode = 422;
                $responseData = $validations->errors();
            }

            if ($resopnseCode == 201) {
                return $this->success($responseMessage, $resopnseCode, $responseData);
            } else {
                return $this->error($responseMessage, $resopnseCode, $responseData);
            }
        } catch (\Throwable $th) {
            Log::error('ERROR_IN_CREATE_USER');
            Log::error($th);
            return $this->error('Oops.. Error occour while registration. Please try again.', 500);
        }
    }


    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $responseData = [];
            // -- Validation
            $validations = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:5'
            ]);

            if (!$validations->fails()) {
                // -- Check if credentials are correct
                if (!Auth::attempt($request->only(['email', 'password']))) {
                    $responseMessage = 'These credentials doesn\'t exist';
                    $resopnseCode = 401;
                } else {
                    // -- Login and create token
                    $user = User::where('email', $request->email)->first();
                    $responseMessage = 'User Logged In Successfully';
                    $resopnseCode = 200;
                    $responseData = ['token' => $user->createToken("API TOKEN")->plainTextToken];
                }
            } else {
                $responseMessage = 'Validation error';
                $resopnseCode = 422;
                $responseData = $validations->errors();
            }

            if ($resopnseCode == 200) {
                return $this->success($responseMessage, $resopnseCode, $responseData);
            } else {
                return $this->error($responseMessage, $resopnseCode, $responseData);
            }
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
    public function logout()
    {
        try {
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
