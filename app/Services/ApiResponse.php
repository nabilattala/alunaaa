<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'data' => $data,
            'status' => 'success',
            'message' => $message,
        ], $code);
    }

    public static function error($message = 'Error', $code = 400, $data = null)
    {
        return response()->json([
            'data' => $data,
            'status' => 'error',
            'message' => $message,
        ], $code);
    }

    public static function validation($errors, $message = 'Validation Error', $code = 422)
    {
        return response()->json([
            'errors' => $errors,
            'status' => 'fail',
            'message' => $message,
        ], $code);
    }
}
