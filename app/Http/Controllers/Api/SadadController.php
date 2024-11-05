<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Plutu\Services\PlutuSadad;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SadadController extends Controller
{
    public function sadad(Request $request){
        $mobileNumber = '0913632323';
        $birthYear = '2001'; // Birth year
        $amount = $request->amount; 

        try {
            $api = new PlutuSadad;
            $api->setCredentials(env('PLUTU_API_KEY'), env('PLUTU_ACCESS_TOKEN'));
            $apiResponse = $api->verify($mobileNumber, $birthYear, $amount);

            if ($apiResponse->getOriginalResponse()->isSuccessful()) {

                $processId = $apiResponse->getProcessId();
                return response()->json([
                    'message' => 'Payment successful',
                    'data' => $apiResponse,
                    'processId' => $processId,
                    'amount' => $amount
                ]);

            } elseif ($apiResponse->getOriginalResponse()->hasError()) {

                // Possible errors from Plutu API
                // @see https://docs.plutu.ly/api-documentation/errors Plutu API Error Documentation
                $errorCode = $apiResponse->getOriginalResponse()->getErrorCode();
                $errorMessage = $apiResponse->getOriginalResponse()->getErrorMessage();
                $statusCode = $apiResponse->getOriginalResponse()->getStatusCode();
                $responseData = $apiResponse->getOriginalResponse()->getBody();

                return response()->json([
                    'message' => 'Payment failed',
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'statusCode' => $statusCode,
                    'responseData' => $responseData
                ]);

            }
        } catch (\Exception $e) {
            $exception = $e->getMessage();
            return response()->json([
                'message' => 'Payment failed',
                'exception' => $exception
            ]);
        }

    }

    public function confirmPayment(Request $request)
    {
        $processId = $request->process_id;
        $code = '111111'; // OTP
        $amount = $request->amount; // amount in float format
        $invoiceNo = 'inv-12345';

        try {
            $api = new PlutuSadad;
            $api->setCredentials(env('PLUTU_API_KEY'), env('PLUTU_ACCESS_TOKEN'));
            $apiResponse = $api->confirm($processId, $code, $amount, $invoiceNo);

            if ($apiResponse->getOriginalResponse()->isSuccessful()) {
                $transactionId = $apiResponse->getTransactionId();
                $data = $apiResponse->getOriginalResponse()->getBody();

                $user = Auth::user();
                
                $wallet = $user->wallet ?? $user->wallet()->create(['balance' => 0]);
                
                $transaction = $wallet->transactions()->create([
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => 'Payment via Sadad',
                ]);

                $wallet->balance += $amount;
                $wallet->save();

                return response()->json([
                    'message' => 'Payment successful',
                    'data' => $apiResponse,
                    'transactionId' => $transactionId,
                    'data' => $data,
                    'invoiceNo' => $invoiceNo,
                    'wallet_balance' => $wallet->balance
                ]);

            } elseif ($apiResponse->getOriginalResponse()->hasError()) {
                $errorCode = $apiResponse->getOriginalResponse()->getErrorCode();
                $errorMessage = $apiResponse->getOriginalResponse()->getErrorMessage();
                $statusCode = $apiResponse->getOriginalResponse()->getStatusCode();
                $responseData = $apiResponse->getOriginalResponse()->getBody();

                return response()->json([
                    'message' => 'Payment failed',
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                    'statusCode' => $statusCode,
                    'responseData' => $responseData
                ]);
            }
        } catch (\Exception $e) {
            $exception = $e->getMessage();
            return response()->json([
                'message' => 'Payment failed',
                'exception' => $exception
            ]);
        }
    }
}
