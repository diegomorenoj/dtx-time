<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * @var
     */
    protected $folder;

    public function __construct()
    {
        $this->folder = 'files';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public static function getByTableAndId($table, $table_id)
    {
        $results = File::where('table', $table)
            ->where('table_id', $table_id)
            ->get();

        return response()->json(['data'=>$results], 200);
    }

    public static function privateGetByTableAndId($table, $table_id)
    {
        $data = File::where('table', $table)
            ->where('table_id', $table_id)
            ->get();

        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{

            $data = $request->all();
            $input = json_decode($data['data'], true); // CONVERTIR A JSON LOS DATOS DEL FORMULARIO
            $file = $request->file('file'); // OBTENER EL DOCUMENTO
            $file_name = $file->getClientOriginalName();
            $file_type = $file->getClientOriginalExtension();
            $file_path = public_path($this->folder).DIRECTORY_SEPARATOR.$file_name;
            $file->move(public_path($this->folder), $file_name);

            $_file = new File;
            $_file->table = $input['table'];
            $_file->table_id = $input['table_id'];
            $_file->name = $file_name;
            $_file->type = $file_type;
            $_file->path = $file_path;
            $_file->save();

            $response = [
                'success' => true,
                'data' => $_file,
                'message' => 'File stored successfully.'
            ];

            return response()->json($response, 200);
        }catch(\Exception $e){
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => $e->getMessage()
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * storeAux: guarda un documento y sus datos adicionales
     *
     * @param File $file
     * @param String $table
     * @param Integer $table_id
     * @param JSON $data
     *
     * @return void
     */
    public static function privateStore($file, $table, $table_id, $data)
    {
        $file_name = $file->getClientOriginalName();
        $file_type = $file->getClientOriginalExtension();
        $file_path = public_path('files').DIRECTORY_SEPARATOR.$file_name;
        $file->move(public_path('files'), $file_name);

        $_file = new File;
        $_file->table = $table;
        $_file->table_id = $table_id;
        $_file->name = $file_name;
        $_file->type = $file_type;
        $_file->path = $file_path;
        $_file->data = $data;
        $_file->save();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
