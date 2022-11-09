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
    public function addMoney(Request $request)
    {
        try {
            $responseData = [];
            // -- Validation
            $validations = Validator::make(
                $request->all(),
                [
                    'amount' => 'required|numeric|regex:/^-?[0-9]+(?:\.[0-9]{1,2})?$/|between:3.00,100.00'
                ],
                ['amount.regex' => 'Amount is accepted upto two decimal places']
            );

            if (!$validations->fails()) {
                // -- Save and update wallet
                $wallet = number_format(auth()->user()->wallet + $request->amount, 2);
                User::where('id', '=', auth()->user()->id)
                    ->update([
                        'wallet' => $wallet
                    ]);

                $responseMessage = 'Amount inserted in wallet successfully';
                $resopnseCode = 201;
                $responseData = [
                    'wallet' => $wallet
                ];
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
            Log::error('ERROR_IN_ADD_MONEY_IN_WALLET');
            Log::error($th);
            return $this->error('Oops.. Error occour while inserting money in your wallet.
             Please try again. If issue persist then contact Site Admin', 500);
        }
    }
}
