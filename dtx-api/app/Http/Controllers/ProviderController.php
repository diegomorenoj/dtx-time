<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    /**
     * Display a listing of the Providers.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('providers AS p')
            ->select(
                'p.*'
                , DB::raw('CASE p.type WHEN 1 THEN "XLS" WHEN 2 THEN "Moodle" WHEN 3 THEN "Sistema" END AS type_name')	
            )
            ->orderBy('p.id','asc')
            ->get();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List providers Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Store a newly created Providers in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
            'type' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'type.required' => 'El tipo es obligatorio.',
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

        $provider = new Provider;
        $provider->name = $input['name'];
        $provider->type = $input['type'];
        $provider->save();

        $response = [
            'success' => true,
            'data' => $provider,
            'message' => 'Plataforma creada correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified Providers in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Provider $provider)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
            'type' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
            'type.required' => 'El tipo es obligatorio.',
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

        $provider->name = $input['name'];
        $provider->type = $input['type'];
        $provider->save();

        $response = [
            'success' => true,
            'data' => $provider,
            'message' => 'Plataforma actualizada correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified Providers from storage.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Provider $provider)
    {
        $provider->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Plataforma eliminada correctamente.'
        ];

        return response()->json($response, 200);
    }
}
