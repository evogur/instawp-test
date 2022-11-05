<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    use ApiResponses;

    /**
     * Add Money to User Wallet
     * @param Request $request
     * @return Amount 
     */
    public function addMoney(Request $request){
        try{
            // -- Validation
            $validateUser = Validator::make($request->all(), [
                'amount' => 'required|numeric|regex:/^-?[0-9]+(?:\.[0-9]{1,2})?$/|between:3.00,100.00'
            ], 
            ['amount.regex' => 'Amount is accepted upto two decimal places']
            );
            if($validateUser->fails()){
                return $this->error(
                    'Validation error', 
                    401, 
                    $validateUser->errors()
                );
            }

            // -- Save and update wallet
            $wallet = User::where('id', '=', auth()->user()->id)
                        ->update([
                            'wallet' => auth()->user()->wallet + $request->amount
                        ]);
            $amount = User::where('id', '=', auth()->user()->id)->pluck('wallet');
            return $this->success(
                'Amount inserted in wallet successfully',
                200,
                [ 'amount' => $amount[0] ]
            );

        }
        catch (\Throwable $th) {
            Log::error('Error in ADD_MONEY_IN_WALLET______START');
            Log::error($th);
            return $this->error('Oops.. Error occour while inserting money in your wallet. Please try again. If issue persist then contact Site Admin', 401);
        }
    }
}
