<?php

namespace App\Http\Controllers\Api;

use App\Entity\Setting;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Class SettingController
 *
 * @package App\Http\Controllers\Api
 */
class BaseController extends Controller
{
    public function json($success, $message, $data= null)
    {
        $array =[
            'success' => $success,
            'message' => $message,
            'data' => $data,
        ];
        if($success){
            return response()->json($array, Response::HTTP_OK);
        }else{
            return response()->json($array, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
