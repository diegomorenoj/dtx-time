<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    /**
     * Display a listing of the Rols.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Rol::all();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List roles Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Store a newly created Rols in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
        ];

        // VALIDACIONES
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, 500);
        }

        $rol = new Rol;
        $rol->name = $input['name'];
        $rol->save();

        $response = [
            'success' => true,
            'data' => $rol,
            'message' => 'Rol Stored Successfully'
        ];

        return response()->json($response, 200);

    }

    /**
     * Update the specified Rols in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Rol  $rol
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Rol $rol)
    {
        $input = $request->all();

        $response = [
            'success' => true,
            'data' => $rol,
            'message' => 'Rol update Successfully'
        ];

        return response()->json($response, 200);

        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
        ];

        // VALIDACIONES
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, 500);
        }

        $rol->name = $input['name'];
        $rol->save();

        $response = [
            'success' => true,
            'data' => $rol,
            'message' => 'Rol update Successfully'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified Rols from storage.
     *
     * @param  \App\Models\Rol  $rol
     * @return \Illuminate\Http\Response
     */
    public function destroy(Rol $rol)
    {
        $rol->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Rol deleted Successfully.'
        ];

        return response()->json($response, 200);
    }
}
