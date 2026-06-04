<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function success(
        array $data,
        string $msg,
        int $status = 200,
    ): JsonResponse {
        $response = [
            "success" => true,
            "status" => $status,
            "message" => $msg,
            "data" => $data,
        ];

        return response()->json($response, $status);
    }

    public function error($error, $errorMsg, $status = 500)
    {
        $response = [
            "success" => false,
            "status" => $status,
            "message" => $errorMsg,
        ];

        if (!empty($errorMsg)) {
            $response["error"] = $error;
        }

        return response()->json($response, $status);
    }
}
