<?php

namespace App\Http\Controllers;

use App\Models\Cycle;
use Illuminate\Http\Request;

class CycleController extends Controller
{
    /**
     * Display a listing of the Rols.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Cycle::all();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Cycles Successfully'
        ];
        
        return response()->json($response, 200);
    }

}
