<?php

namespace App\Http\Responses;

class ApiResponse
{
    public static function success($data = null, $message = null, int $status = 200)
    {
        $response = [];
        if ($message) {
            $response['message'] = $message;
        }
        if ($data !== null) {
            $response['data'] = $data;
        }
        return response()->json($response, $status);
    }

    public static function error(string $error, string $message = null, $details = null, int $status = 400)
    {
        $response = [
            'error' => $error,
            'message' => $message ?? $error,
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        return response()->json($response, $status);
    }
}