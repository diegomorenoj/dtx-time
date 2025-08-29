<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;
use App\Models\ObjectiveSpecialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectiveSpecialtyController extends Controller
{
    /**
     * @var
     */
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the Objectives.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('objective_specialties AS o')
            ->join('cycles AS c', 'c.id', '=', 'o.cycle_id')
            ->join('specialties AS e', 'e.id', '=', 'o.specialty_id')
            ->select(
                'o.*'
                ,'c.name AS cycle_name'
                ,'e.name AS specialty_name'
                , DB::raw('DATE_FORMAT(c.end_date,"%b %d de %Y") AS end_date')
                , DB::raw('0 AS users')
                , DB::raw('0 AS courses')
            )
            ->orderBy('o.created_at','desc')
            ->get();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Objectivos listados correctamente.'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Get list Objectives in storage, by Filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByFilter(Request $request)
    {
        $input = $request->all();
        $specialty_id = $input['specialty_id'] === null ? '%%' : '%' . $input['specialty_id'] . '%';  
        $cycle_id = $input['cycle_id'] === null ? '%%' : $input['cycle_id'];

        /*if($input['range'] !== null)
        {
            $data = DB::table('objective_specialties AS o')
                ->join('cycles AS c', 'c.id', '=', 'o.cycle_id')
                ->join('specialties AS e', 'e.id', '=', 'o.specialty_id')
                ->select(
                    'o.*'
                    ,'c.name AS cycle_name'
                    ,'e.name AS specialty_name'
                    , DB::raw('DATE_FORMAT(c.end_date,"%b %d de %Y") AS end_date')
                    , DB::raw('0 AS users')
                    , DB::raw('0 AS courses')
                )
                //->whereRaw("DATE_FORMAT(o.created_at,'%Y-%m-%d') BETWEEN '".$input['range'][0]."' AND '".$input['range'][1]."'")
                ->whereRaw('o.cycle_id LIKE "'.$cycle_id.'"')
                ->orderBy('o.created_at','desc')
                ->get();
        }
        else
        {
            $data = DB::table('objective_specialties AS o')
                ->join('cycles AS c', 'c.id', '=', 'o.cycle_id')
                ->join('specialties AS e', 'e.id', '=', 'o.specialty_id')
                ->select(
                    'o.*'
                    ,'c.name AS cycle_name'
                    ,'e.name AS specialty_name'
                    , DB::raw('DATE_FORMAT(c.end_date,"%b %d de %Y") AS end_date')
                    , DB::raw('0 AS users')
                    , DB::raw('0 AS courses')
                )
                ->whereRaw('o.cycle_id LIKE "'.$cycle_id.'"')
                ->orderBy('o.created_at','desc')
                ->get();
        }*/

        $data = DB::table('objective_specialties AS o')
            ->join('cycles AS c', 'c.id', '=', 'o.cycle_id')
            ->join('specialties AS e', 'e.id', '=', 'o.specialty_id')
            ->select(
                'o.*'
                ,'c.name AS cycle_name'
                ,'e.name AS specialty_name'
                , DB::raw('DATE_FORMAT(c.end_date,"%b %d de %Y") AS end_date')
                , DB::raw('0 AS users')
                , DB::raw('0 AS courses')
            )
            ->whereRaw('o.cycle_id LIKE "'.$cycle_id.'"')
            ->orderBy('o.created_at','desc')
            ->get();

        foreach ($data as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS, Nota: se debe incluir los usuarios de Moodle
            // --------------------------------------------------------------------------------------
            $users = DB::table('users AS u')
                ->join('user_specialties AS ue', 'u.id', '=', 'ue.user_id')
                ->select('u.*')
                ->whereRaw('ue.specialty_id = '.$item->specialty_id)
                ->get();
            
            $item->users = $users;

            // --------------------------------------------------------------------------------------
            // CURSOS ASOCIADOS, Nota: se debe incluir los cursos de Moodle
            // --------------------------------------------------------------------------------------
            $courses = DB::table('courses AS c')
                ->select('c.name AS course_name', 'c.hours')
                ->whereRaw('c.specialty_id = '.$item->specialty_id)
                ->get();
            
            // CURSOS DE MOODLE
            $courses_moodle = $this->getCoursesMoodleBySpecialty($item->specialty_id);
            $merged = $courses->merge($courses_moodle);
            $results = $merged->all();
            $item->courses = $results;
        }

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Objectives Specialities Successfully'
        ];
        
        return response()->json($response, 200);
    }

     /**
     * Lista los cursos de moodle, conectandose a la otra base de datos, asociados a una espacialidad
     * 
     * @return Array()
     */
    private function getCoursesMoodleBySpecialty($specialty_id)
    {
        $courses_aux = DB::connection('mysql_aux')->select("
            SELECT 
                cur.name as course_name
                , cur.hours
                FROM (
                    SELECT 
                        c.fullname AS name
                        , (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' and cd.instanceid=c.id) AS specialty_id
                    FROM mdl_course c 
                    LEFT JOIN mdl_course_categories cc ON cc.id = c.category
                ) AS cur
            WHERE 
                cur.specialty_id LIKE ?
        ", array($specialty_id));

        return $courses_aux;
    }

    /**
     * Store a newly created Objectives in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'cycle_id' => 'required',
            'specialty_id' => 'required',
            'hours' => 'required',
        ];

        $messages = [
            'cycle_id.required' => 'El ciclo es obligatorio.',
            'specialty_id.required' => 'La especialidad es obligatoria.',
            'hours.required' => 'Las horas son obligatorias.',
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

        $objective = new ObjectiveSpecialty;
        $objective->cycle_id = $input['cycle_id'];
        $objective->specialty_id = $input['specialty_id'];
        $objective->hours = $input['hours'];
        // GUARDAR
        $objective->save();

        $response = [
            'success' => true,
            'data' => $objective,
            'message' => 'Objectivo por espacialidad guardado correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified Objectives in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $objective = ObjectiveSpecialty::find($id);
        $input = $request->all();

        $rules = [
            'cycle_id' => 'required',
            'specialty_id' => 'required',
            'hours' => 'required',
        ];

        $messages = [
            'cycle_id.required' => 'El ciclo es obligatorio.',
            'specialty_id.required' => 'La especialidad es obligatoria.',
            'hours.required' => 'Las horas son obligatorias.',
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

        $objective->cycle_id = $input['cycle_id'];
        $objective->specialty_id = $input['specialty_id'];
        $objective->hours = $input['hours'];
        // GUARDAR
        $objective->save();

        $response = [
            'success' => true,
            'data' => $objective,
            'message' => 'Objectivo por espacialidad actualizado correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified Objectives from storage.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $objective = ObjectiveSpecialty::find($id);
        $objective->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Objectivo por espacialidad eliminado correctamente.'
        ];

        return response()->json($response, 200);
    }
}
