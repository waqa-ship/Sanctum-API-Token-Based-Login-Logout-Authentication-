<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseCOntroller extends Controller
{
    public function sendResponse($result, $message){
       $response =[

        'succes' => true,
        'data' => $result,
        'message' => $message,


       ];
       return response()->json($response,200);
    }
    public function sendError($error, $errorMessage =[],$code = 404){
        $response =[
 
         'succes' => false,  
         'message' => $error,
        ];       
if(!empty($errorMessage)){

        $response['data'] = $errorMessage;
       
     }
     return response()->json($response,200);
}
}
