<?php

namespace App\Http\Controllers;

use App\Models\Zoom;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Log;

class ZoomController extends Controller
{
    /**
     * Obtiene el listado de cursos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{

            log::info($request);

            $course_id = $request->input('course_id', null);
            $email = $request->input('email', null);
            $zoom = Zoom::getZoom($email, $course_id);

            log::info($zoom);

            $response = [
                'success' => true,
                'data' => $zoom,
                'message' => 'Listado asistente zoom'
            ];

            return response()->json($response, 200);
        
        } catch (\Exception $e) {
            // Manejar excepción y retornar respuesta de error
            return response()->json(['success' => false, 'message' => 'Error al obtener los datos de Zoom', 'error' => $e->getMessage()], 500);
        }
        
    }


}