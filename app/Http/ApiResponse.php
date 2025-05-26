<?php

namespace App\Http;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Standardized success response.
     *
     * @param  mixed   $data
     * @param  string  $message
     * @param  int     $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data = null, string $message = "", int $status = 200): JsonResponse
    {
        return response()->json([
            "success" => true,
            "message" => $message,
            "data"    => $data,
        ], $status);
    }

    /**
     * Standardized error response.
     *
     * @param  string  $message
     * @param  int     $status
     * @param  mixed   $data
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error(string $message = "", int $status = 400, $data = null): JsonResponse
    {
        return response()->json([
            "success" => false,
            "message" => $message,
            "data"    => $data,
        ], $status);
    }

    /**
     * No content response (204).
     * 
     * @return \Illuminate\Http\JsonResponse
    */
    public static function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }
}