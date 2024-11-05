<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Plutu\Services\PlutuAdfali;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Plutu\Services\PlutuLocalBankCards;

class AdfaliController extends Controller
{
    public function adfali(Request $request)
    {
        $mobileNumber = '0913632323';
        $amount = $request->amount;
        try {

            $api = new PlutuAdfali;
            $api->setCredentials(env('PLUTU_API_KEY'), env('PLUTU_ACCESS_TOKEN'), env('PLUTU_SECRET_KEY'));
            $apiResponse = $api->verify($mobileNumber, $amount);

            if ($apiResponse->getOriginalResponse()->isSuccessful()) {
                $processId = $apiResponse->getProcessId();
            } elseif ($apiResponse->getOriginalResponse()->hasError()) {
                $errorCode = $apiResponse->getOriginalResponse()->getErrorCode();
                $errorMessage = $apiResponse->getOriginalResponse()->getErrorMessage();
                return response()->json([
                    'message' => 'Payment failed',
                    'errorCode' => $errorCode,
                    'errorMessage' => $errorMessage,
                ]);
            }
            return response()->json([
                'message' => 'Payment successful',
                'data' => $apiResponse,
                'processId' => $processId,
                'amount' => $amount,
            ]);

        } catch (\Exception $e) {
            $exception = $e->getMessage();
        }
    }

    public function confirmPayment(Request $request)
    {
        $processId = $request->process_id; // Process ID from verification step
        $code = '1111'; // OTP
        $amount = $request->amount; // Amount in float format
        $invoiceNo = 'inv-12345';

        try {
            $api = new PlutuAdfali;
            $api->setCredentials(env('PLUTU_API_KEY'), env('PLUTU_ACCESS_TOKEN'));

            $apiResponse = $api->confirm($processId, $code, $amount, $invoiceNo);

            if ($apiResponse->getOriginalResponse()->isSuccessful()) {
                $transactionId = $apiResponse->getTransactionId();
                $data = $apiResponse->getOriginalResponse()->getBody();

                $user = Auth::user();
                $wallet = $user->wallet ?? $user->wallet()->create(['balance' => 0]);

                $wallet->transactions()->create([
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => 'Payment via Adfali',
                ]);

                $wallet->balance += $amount;
                $wallet->save();

                return response()->json([
                    'message' => 'Payment successful',
                    'data' => $apiResponse,
                    'transactionId' => $transactionId,
                    'data' => $data,
                    'invoiceNo' => $invoiceNo,
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
                    'responseData' => $responseData,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment failed',
                'exception' => $e->getMessage(),
            ]);
        }
    }

    

}
