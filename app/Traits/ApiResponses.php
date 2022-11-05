<?php

namespace App\Traits;

/*
|--------------------------------------------------------------------------
| Api Responses Trait
|--------------------------------------------------------------------------
|
| This common trait will be used for sending response to clients.
|
*/

trait ApiResponses
{
	/**
     * Return a success JSON response.
     *
     * @param  string  $message
     * @param  int|null  $code
     * @param  array|string  $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function success(string $message = null, int $code, $data)
	{
		return response()->json([
			'status' => 'Success',
			'message' => $message,
			'data' => $data
		], $code);
	}

	/**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function error(string $message = null, int $code, $data = null)
	{
		return response()->json([
			'status' => 'Error',
			'message' => $message,
			'data' => $data
		], $code);
	}

}