<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    
    protected function success($data = null, string $message = 'Operation successful', int $status = 200): JsonResponse
{
    return response()->json([
        'status' => true,
        'message' => $message,
        'data' => $data,
    ], $status);
}
    public function successWithToken($data = null, $message = 'success', $code = 200, $token = null)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'token' => $token,
        ], $code);
    }

    public function error($message = null, $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $code);
    }

    public function errors($errors = [], $message = null, $code = 500){

        return response()->json([
            'status' => false,
            'message' => $message,
            'data'  => $errors
        ], $code);
    }
}
