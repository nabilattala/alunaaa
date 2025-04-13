<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'data' => $data
            'status' => 'success',
            'message' => $message,
        ], $code);
    }

    public static function error($message = 'Error', $code = 400, $data = null)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    public static function validation($errors, $message = 'Validation Error', $code = 422)
    {
        return response()->json([
            'status' => 'fail',
            'message' => $message,
            'errors' => $errors
        ], $code);
    }
}
