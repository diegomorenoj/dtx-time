<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TrainingRequestsComments;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\File as Fil;

class TrainingRequestsCommentsController extends Controller
{
    /**
     * @var
     */
    protected $user;
    protected $table;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $this->table = 'training_requests_comments';
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
            $data = $request->all();
            $input = json_decode($data['data'], true); // CONVERTIR A JSON LOS DATOS DEL FORMULARIO
            $file = $request->file('file'); // OBTENER EL DOCUMENTO

            $rules = [
                'training_request_id' => 'required',
                'comments' => 'required',
            ];

            $messages = [
                'training_request_id.required' => 'La capacitación externa es requerida.',
                'comments.required' => 'El comentario es obligatorio.',
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

            $trainingRequestsComments = new TrainingRequestsComments;
            $trainingRequestsComments->training_request_id = $input['training_request_id'];
            $trainingRequestsComments->comments = $input['comments'];
            $trainingRequestsComments->user_id = $this->user->id;

            // GUARDAR
            $trainingRequestsComments->save();

            // GUARDAR EL ARCHIVO
            if ($file){
                $table = $this->table;
                $table_id = $trainingRequestsComments->id;
                $data = null;
               
                // GUARDAR EL DOCUMENTO
                FileController::privateStore($file, $table, $table_id, $data);
            }

            DB::commit();

            $response = [
                'success' => true,
                'data' => $trainingRequestsComments,
                'message' => 'Comentario agregado con éxito.'
            ];

            return response()->json($response, 200);

        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => 'Lo sentimos, hubo un error, por favor intente nuevamente: ' . $e->getMessage()
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TrainingRequestsComments  $trainingRequestsComments
     * @return \Illuminate\Http\Response
     */
    public function show(TrainingRequestsComments $trainingRequestsComments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $input = json_decode($data['data'], true); // CONVERTIR A JSON LOS DATOS DEL FORMULARIO
            $file = $request->file('file'); // OBTENER EL DOCUMENTO
            $trainingRequestsComments = TrainingRequestsComments::find($id);

            $rules = [
                'training_request_id' => 'required',
                'comments' => 'required',
            ];

            $messages = [
                'training_request_id.required' => 'La capacitación externa es requerida.',
                'comments.required' => 'El comentario es obligatorio.',
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

            $trainingRequestsComments->training_request_id = $input['training_request_id'];
            $trainingRequestsComments->comments = $input['comments'];
            $trainingRequestsComments->user_id = $this->user->id;

            // GUARDAR
            $trainingRequestsComments->save();

            // GUARDAR EL ARCHIVO
            if($file){

                // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
                $lstFiles = FileController::privateGetByTableAndId($this->table, $trainingRequestsComments->id);
                if($lstFiles->count() > 0) {
                    foreach($lstFiles as $item) {
                        $item->delete();
                        Fil::delete($item->path);
                    }
                }

                $table = $this->table;
                $table_id = $trainingRequestsComments->id;
                $data = null;
               
                // GUARDAR EL DOCUMENTO
                FileController::privateStore($file, $table, $table_id, $data);
            }

            DB::commit();

            $response = [
                'success' => true,
                'data' => $trainingRequestsComments,
                'message' => 'Comentario actualizado con éxito.'
            ];

            return response()->json($response, 200);

        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => 'Lo sentimos, hubo un error, por favor intente nuevamente: ' . $e->getMessage()
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trainingRequestsComments = TrainingRequestsComments::find($id);

        // BORRAR EL FILE
        // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
        $lstFiles = FileController::privateGetByTableAndId($this->table, $trainingRequestsComments->id);
        if($lstFiles->count() > 0) {
            foreach($lstFiles as $item) {
                $item->delete();
                Fil::delete($item->path);
            }
        }

        $trainingRequestsComments->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Comentario eliminado con éxito..'
        ];

        return response()->json($response, 200);
    }
}
