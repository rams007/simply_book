<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function ChargebeeCallback (Request $request){

        print_r($request->all());
        return response()->json([]);
    }
}
