<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\Constancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\File as Fil;

class ConstancyController extends Controller
{
    protected $table;

    public function __construct()
    {
        $this->table = 'constancies';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $input = $request->all();
            $file = $request->file('file'); // OBTENER EL DOCUMENTO

            $rules = [
                'training_request_id' => 'required'
            ];

            $messages = [
                'training_request_id.required' => 'La capacitación externa es requerida.',
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

            DB::beginTransaction();

            // CUANDO EXISTE LA CONSTANCIA NO EXISTE SE DEBE CREA, DE LO CONTRARIO EDITAR
            $constancy = Constancy::where('training_request_id', $input['training_request_id'])->first();

            if($constancy == null) 
            {
                $constancy = new Constancy;
                $constancy->training_request_id = $input['training_request_id'];
                $constancy->comments = null;

                // GUARDAR
                $constancy->save();

                // GUARDAR EL ARCHIVO
                if($file)
                {
                    $table = $this->table;
                    $table_id = $constancy->training_request_id;
                    $data = null;
                
                    // GUARDAR EL DOCUMENTO
                    FileController::privateStore($file, $table, $table_id, $data);
                }
            }
            else
            {
                $constancy->training_request_id = $input['training_request_id'];
                $constancy->save();

                if($file){

                    // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
                    $lstFiles = FileController::privateGetByTableAndId($this->table, $constancy->training_request_id);
                    if($lstFiles->count() > 0) {
                        foreach($lstFiles as $item) {
                            $item->delete();
                            Fil::delete($item->path);
                        }
                    }
    
                    $table = $this->table;
                    $table_id = $constancy->training_request_id;
                    $data = null;
                   
                    // GUARDAR EL DOCUMENTO
                    FileController::privateStore($file, $table, $table_id, $data);
                }
            }

            DB::commit();

            $response = [
                'success' => true,
                'data' => $constancy,
                'message' => 'Constancy Stored Successfully'
            ];

            return response()->json($response, 200);

        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Constancy  $constancy
     * @return \Illuminate\Http\Response
     */
    public function show(Constancy $constancy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Constancy  $constancy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $input = json_decode($data['data'], true); // CONVERTIR A JSON LOS DATOS DEL FORMULARIO
            $file = $request->file('file'); // OBTENER EL DOCUMENTO
            $constancy = Constancy::find($id);

            $rules = [
                'training_request_id' => 'required',
            ];

            $messages = [
                'training_request_id.required' => 'La capacitación externa es requerida.',
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

            DB::beginTransaction();

            $constancy->training_request_id = $input['training_request_id'];
            $constancy->comments = $input['comments'];

            // GUARDAR
            $constancy->save();

            // GUARDAR EL ARCHIVO
            if($file){

                // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
                $lstFiles = FileController::privateGetByTableAndId($this->table, $constancy->training_request_id);
                if($lstFiles->count() > 0) {
                    foreach($lstFiles as $item) {
                        $item->delete();
                        Fil::delete($item->path);
                    }
                }

                $table = $this->table;
                $table_id = $constancy->id;
                $data = null;
               
                // GUARDAR EL DOCUMENTO
                FileController::privateStore($file, $table, $table_id, $data);
            }

            DB::commit();

            $response = [
                'success' => true,
                'data' => $constancy,
                'message' => 'Constancy Updated Successfully'
            ];

            return response()->json($response, 200);

        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Constancy  $constancy
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $constancy = Constancy::find($id);
        // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
        $lstFiles = FileController::privateGetByTableAndId($this->table, $constancy->training_request_id);
        if($lstFiles->count() > 0) {
            foreach($lstFiles as $item) {
                $item->delete();
                Fil::delete($item->path);
            }
        }

        $constancy->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Constancia eliminada con éxito..'
        ];

        return response()->json($response, 200);
    }
}
