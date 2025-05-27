<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    function sendResponse($userData= [],$message = ''){

        $response = [
            'success'   => true,
            'data' => $userData,
            'message'=> $message,
        ];

        return response()->json($response,200);
    }

    function sendError($resultData = [], $errorMessages, $code = 400){
        $response = [
            'success'   => false,
            'message'   => $errorMessages,
            'data'      => $resultData,
        ];
        return response()->json($response, $code);
    }
}
