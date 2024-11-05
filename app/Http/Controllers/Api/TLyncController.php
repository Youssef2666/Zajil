<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Plutu\Services\PlutuTlync;

class TLyncController extends Controller
{

    public function initiatePayment(Request $request)
{

    $amount = (float)$request->amount; // Cast to float
    $invoiceNo = 'inv-' . uniqid(); // Unique invoice number
    $returnUrl = route('payment.callback'); // Update this to your actual callback route
    $callbackUrl = route('payment.callback'); // Adjust as necessary for your flow
    $mobile = '0913632323'; // Ensure this is a valid mobile number

    // Log the values for debugging
    info('Initiating payment with the following details:', [
        'amount' => $amount,
        'invoiceNo' => $invoiceNo,
        'mobile' => $mobile,
        'returnUrl' => $returnUrl,
        'callbackUrl' => $callbackUrl,
    ]);

    try {
        $api = new PlutuTlync();
        $api->setCredentials(
            env('PLUTU_API_KEY'),
            env('PLUTU_ACCESS_TOKEN'),
            env('PLUTU_SECRET_KEY')
        );

        $apiResponse = $api->confirm($mobile, $amount, $invoiceNo, $returnUrl, $callbackUrl);

        if ($apiResponse->getOriginalResponse()->isSuccessful()) {
            $redirectUrl = $apiResponse->getRedirectUrl();

            return response()->json([
                'redirectUrl' => $redirectUrl,
                'invoiceNo' => $invoiceNo,
            ]);
        } elseif ($apiResponse->getOriginalResponse()->hasError()) {
            info('Payment API Error:', [
                'errorCode' => $apiResponse->getOriginalResponse()->getErrorCode(),
                'errorMessage' => $apiResponse->getOriginalResponse()->getErrorMessage(),
                'fullResponse' => $apiResponse->getOriginalResponse(), // Log the full response
            ]);
            $errorCode = $apiResponse->getOriginalResponse()->getErrorCode();
            $errorMessage = $apiResponse->getOriginalResponse()->getErrorMessage();

            return response()->json([
                'message' => 'Payment failed',
                'errorCode' => $errorCode,
                'errorMessage' => $errorMessage,
            ]);
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'An error occurred',
            'error' => $e->getMessage(),
        ]);
    }
}


    public function handleCallback(Request $request)
    {
        $parameters = $request->all();
        info('Callback parameters:', $request->all());

        try {
            $api = new PlutuTlync();
            $api->setSecretKey(env('PLUTU_SECRET_KEY'));
            $callback = $api->callbackHandler($parameters);

            if ($callback->isApprovedTransaction()) {
                $transactionId = $callback->getParameter('transaction_id');

                return response()->json([
                    'message' => 'Payment successful',
                    'transactionId' => $transactionId,
                ]);
            } elseif ($callback->isCanceledTransaction()) {
                return response()->json([
                    'message' => 'Payment canceled',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Payment failed',
                'error' => $e->getMessage(),
            ]);
        }
    }

}
