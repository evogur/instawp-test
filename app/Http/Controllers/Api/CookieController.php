<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Log;

class CookieController extends Controller
{
    use ApiResponses;

    /**
     * Buy Cookie
     * @param Request $request
     * @return response Message
     */
    public function buyCookie(Request $request) {
        try{
            $responseData = [];
            // Defining a static variable (In actual needs to get from DB)
            $pricePerCookie = 1;
            // -- Validation
            $validations = Validator::make($request->all(), [
                'no_of_cookies' => 'required|integer|min:1'
            ]);
            if(!$validations->fails()){
                 // -- Check if wallet is empty
                $wallet = auth()->user()->wallet;
                if($wallet && $wallet > 0) {
                    $totlaPrice = $pricePerCookie*$request->no_of_cookies;
                    // -- Check if requested cookies are more then wallet
                    if($totlaPrice > $wallet){
                        $responseMessage = 'Sorry!! You does not have sufficient money in wallet';
                        $resopnseCode = 422;
                        $responseData = [
                            'requested_cookies' => $request->no_of_cookies,
                            'required_in_wallet' => $totlaPrice,
                            'currently_in_wallet' => $wallet,
                        ];
                    }else{
                        $user = User::where('id', '=', auth()->user()->id);
                        $updateWallet = number_format($wallet - $totlaPrice, 2);
                        $user->update(['wallet' => $updateWallet]);
                        $responseMessage = `Yeah! You have successfully bought the $request->no_of_cookies  cookies`;
                        $resopnseCode = 201;
                        $responseData = [
                            'pending_amount' => $updateWallet,
                            'cookies_bought' => $request->no_of_cookies
                        ];
                    }
                }else{
                    $responseMessage = 'Sorry!! Your wallet is empty. Go ahead and add some money.';
                    $resopnseCode = 404;
                }
            }else{
                $responseMessage = 'Validation error';
                $resopnseCode = 422;
                $responseData = $validations->errors();
            }
            if($resopnseCode == 201){
                return $this->success($responseMessage,$resopnseCode,$responseData);
            }else{
                return $this->error($responseMessage,$resopnseCode,$responseData);
            }
        }
        catch (\Throwable $th) {
            Log::error('ERROR_IN_BUY_COOKIE');
            Log::error($th);
            return $this->error('Oops.. Error occour while Buying a cookie. Please try again.
             If issue persist then contact Site Admin', 500);
        }
    }
}
