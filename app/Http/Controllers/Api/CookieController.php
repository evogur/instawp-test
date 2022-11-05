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
    public function buyCookie(Request $request){
        try{
            // Defining a static variable (In actual needs to get from DB)
            $PRICE_PER_COOKIE = 1;
            // -- Validation
            $validateUser = Validator::make($request->all(), [
                'no_of_cookies' => 'required|integer|min:1'
            ]);
            if($validateUser->fails()){
                return $this->error(
                    'Validation error', 
                    401, 
                    $validateUser->errors()
                );
            }

            // -- Check if wallet is empty
            $wallet = User::where('id', '=', auth()->user()->id)->pluck('wallet');
            if(empty($wallet[0])){
                return $this->error(
                    'Sorry!! Your wallet is empty. Go ahead and add some money.', 
                    400
                ); 
            }

            // -- Check if requested cookies are more then wallet
            if($PRICE_PER_COOKIE*$request->no_of_cookies > $wallet[0]){
                return $this->error(
                    'Sorry!! You does not have sufficient money in wallet', 
                    400,
                    [
                        'requested_cookies' => $request->no_of_cookies,
                        'required_in_wallet' => $PRICE_PER_COOKIE*$request->no_of_cookies,
                        'currently_in_wallet' => $wallet[0],
                    ]
                ); 
            }

            User::where('id', '=', auth()->user()->id)
            ->update([
                'wallet' => auth()->user()->wallet - ($PRICE_PER_COOKIE*$request->no_of_cookies)
            ]);

            $remainingWallet = User::where('id', '=', auth()->user()->id)->pluck('wallet');
            return $this->success(
                'Yeah! You have successfully bought the ' . $request->no_of_cookies . ' cookies',
                200,
                [ 
                    'pending_amount' => $remainingWallet[0], 
                    'cookies_bought' => $request->no_of_cookies
                ]
            );
        }
        catch (\Throwable $th) {
            Log::error('Error in BUY_COOKIE______START');
            Log::error($th);
            return $this->error('Oops.. Error occour while inserting money in your wallet. Please try again. If issue persist then contact Site Admin', 401);
        }
    }
}
