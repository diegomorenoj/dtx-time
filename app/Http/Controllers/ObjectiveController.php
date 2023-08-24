<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;
use App\Models\Objective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObjectiveController extends Controller
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
        $data = DB::table('objectives AS o')
            ->select(
                'o.*'
                , DB::raw('DATE_FORMAT(o.start_date,"%b %d de %Y") AS _start_date')
                , DB::raw('DATE_FORMAT(o.end_date,"%b %d de %Y") AS _end_date')
                , DB::raw('0 AS users')
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
        $user_id = $input['user_id'] === null ? '%%' : $this->user->rol_id === 1 ? '%%' : $input['user_id']; // SOLO AL ADMIN SE LE CARGAN TODAS
        $area = $input['area'] === null ? '%%' : $input['area'];
        $position = $input['position'] === null ? '%%' : $input['position'];

        if($input['range'] !== null)
        {
            $data = DB::table('objectives AS o')
                ->select(
                    'o.*'
                    , DB::raw('DATE_FORMAT(o.start_date,"%b %d de %Y") AS _start_date')
                    , DB::raw('DATE_FORMAT(o.end_date,"%b %d de %Y") AS _end_date')
                    , DB::raw('null AS users')
                    , DB::raw('0 AS users_count')
                )
                ->whereRaw("DATE_FORMAT(o.created_at,'%Y-%m-%d') BETWEEN '".$input['range'][0]."' AND '".$input['range'][1]."'")
                ->whereRaw('UPPER(o.area) LIKE UPPER("'.$area.'")')
                ->whereRaw('UPPER(o.position) LIKE UPPER("'.$position.'")')
                ->orderBy('o.created_at','desc')
                ->get();
        }
        else
        {
            $data = DB::table('objectives AS o')
                ->select(
                    'o.*'
                    , DB::raw('DATE_FORMAT(o.start_date,"%b %d de %Y") AS _start_date')
                    , DB::raw('DATE_FORMAT(o.end_date,"%b %d de %Y") AS _end_date')
                    , DB::raw('null AS users')
                    , DB::raw('0 AS users_count')
                )
                ->whereRaw('UPPER(o.area) LIKE UPPER("'.$area.'")')
                ->whereRaw('UPPER(o.position) LIKE UPPER("'.$position.'")')
                ->orderBy('o.created_at','desc')
                ->get();
        }
        
        foreach ($data as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS
            // --------------------------------------------------------------------------------------
            $users = DB::table('users AS u')
                ->select('u.*')
                ->whereRaw('u.area = "'.$item->area.'"')
                ->whereRaw('u.position = "'.$item->position.'"')
                ->get();
            
            $item->users_count = $users->count();
            $item->users = $users;
        }

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Training Requests Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Get list Objectives in storage, by Filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByFilterGeneral(Request $request)
    {
        $input = $request->all();      
        $user_email = $this->user->rol_id === 1 ? '%%' : $this->user->email; // SOLO AL ADMIN SE LE CARGAN TODAS
        $area = $input['area'] === null ? '%%' : $input['area'];
        $position = $input['position'] === null ? '%%' : $input['position'];
        $cycle_id = $input['cycle_id'] === null ? '%%' : $input['cycle_id'];

        $objectives = DB::select("
            SELECT obj.* FROM (
                SELECT 
                u.lastname AS user_name
                , u.area
                , u.position
                , o.hours AS objective_hours
                , 'GENERAL' AS type_name
                , u.email
                , null AS specialty_id
                , o.start_date
                , o.end_date
                , null AS courses
                , 0 AS user_hours
                , 0 AS course_hours
                , 0 AS missing_hours
                , '0%' AS objective_progress
            FROM 
                users u INNER JOIN 
                objectives o ON o.area = u.area 
                AND o.position = u.position 
                AND o.level = u.level
            UNION ALL
            SELECT 
                u.lastname AS user_name
                , u.area
                , u.position
                , o.hours AS objective_hours
                , s.name AS type_name
                , u.email
                , us.specialty_id
                , cy.start_date
                , cy.end_date
                , null AS courses
                , 0 AS user_hours
                , 0 AS course_hours
                , 0 AS missing_hours
                , '0%' AS objective_progress
            FROM 
                users u INNER JOIN 
                user_specialties us ON us.user_id = u.id INNER JOIN
                specialties s ON s.id = us.specialty_id INNER JOIN
                objective_specialties o ON o.specialty_id = s.id INNER JOIN
                cycles cy ON cy.id = o.cycle_id
            ) AS obj
            WHERE 
                obj.email LIKE ?
                AND obj.area LIKE ?
                AND obj.position LIKE ?
            ", array($user_email, $area, $position));

        // CURSOS
        foreach ($objectives as $item) {

            // CURSOS POR ESPCIALIDAD, USUARIO, Y CICLO
            if($item->specialty_id != null) {
                $courses = DB::select("
                    SELECT 
                        c.id
                        , c.name
                        , e.id AS specialty_id
                        , e.name AS specialty_name
                        , u.email
                        , uc.hours AS user_hours
                        , c.hours AS course_hours
                    FROM courses c INNER JOIN
                        objective_specialties os ON os.specialty_id = c.specialty_id INNER JOIN
                        user_specialties us ON us.specialty_id = c.specialty_id INNER JOIN
                        specialties e ON e.id = os.specialty_id INNER JOIN
                        users u ON u.id = us.user_id LEFT JOIN user_courses uc ON uc.course_id = c.id
                    WHERE 
                        c.start_date BETWEEN ? AND ?
                        AND u.email = ?
                        AND os.specialty_id = ?
                    ", array($item->start_date, $item->end_date, $item->email, $item->specialty_id));

                // CONTAR LAS HORAS DE CADA CURSO
                $course_hours = 0;
                $user_hours = 0;
                foreach ($courses as $cur) {
                    $course_hours += $cur->course_hours;
                    $user_hours += $cur->user_hours;
                }
                
                $item->course_hours = $course_hours;
                $item->user_hours = $user_hours;
                $item->courses = $courses;
            }
            // OBJETIVO GENERAL, POR FECHA Y USUARIO
            else
            {
                $courses = DB::select("
                    SELECT 
                        c.id
                        , c.start_date
                        , c.name
                        , null AS specialty_id
                        , 'GENERAL' AS specialty_name
                        , u.email
                        , uc.hours AS user_hours
                        , c.hours AS course_hours
                    FROM 
                        user_courses uc INNER JOIN
                        courses c ON c.id = uc.course_id INNER JOIN
                        users u ON u.id = uc.user_id
                    WHERE u.email = ?
                        AND c.start_date BETWEEN ? AND ?
                    ", array($item->email, $item->start_date, $item->end_date));
                
                // CONTAR LAS HORAS DE CADA CURSO
                $course_hours = 0;
                $user_hours = 0;
                foreach ($courses as $cur) {
                    $course_hours += $cur->course_hours;
                    $user_hours += $cur->user_hours;
                }
                
                $item->course_hours = $course_hours;
                $item->user_hours = $user_hours;
                $item->courses = $courses;
            }

        }

        // HORAS FALTANTES
        foreach ($objectives as $item) {
            $item->missing_hours = $item->course_hours - $item->user_hours;
            $item->objective_progress = round((($item->user_hours * 100) / $item->objective_hours), 2) . '%';
        }

        $response = [
            'success' => true,
            'data' => $objectives,
            'message' => 'List Objectives Report Successfully'
        ];
        
        return response()->json($response, 200);
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
            'start_date' => 'required',
            'end_date' => 'required',
            'area' => 'required',
            'hours' => 'required',
        ];

        $messages = [
            'start_date.required' => 'La fecha inicio es obligatoria.',
            'end_date.required' => 'La fecha fin es obligatoria.',
            'area.required' => 'El área es obligatoria.',
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

        $objective = new Objective;
        $objective->start_date = $input['start_date'];
        $objective->end_date = $input['end_date'];
        $objective->area = $input['area'];
        $objective->position = $input['position'];
        $objective->hours = $input['hours'];
        // GUARDAR
        $objective->save();

        $response = [
            'success' => true,
            'data' => $objective,
            'message' => 'Objectivo guardado correctamente.'
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
        $objective = Objective::find($id);
        $input = $request->all();

        $rules = [
            'start_date' => 'required',
            'end_date' => 'required',
            'area' => 'required',
            'hours' => 'required',
        ];

        $messages = [
            'start_date.required' => 'La fecha inicio es obligatoria.',
            'end_date.required' => 'La fecha fin es obligatoria.',
            'area.required' => 'El área es obligatoria.',
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

        $objective->start_date = $input['start_date'];
        $objective->end_date = $input['end_date'];
        $objective->area = $input['area'];
        $objective->position = $input['position'];
        $objective->hours = $input['hours'];
        // GUARDAR
        $objective->save();

        $response = [
            'success' => true,
            'data' => $objective,
            'message' => 'Objectivo actualizado correctamente.'
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
        $objective = Objective::find($id);
        $objective->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Objectivo eliminado correctamente.'
        ];

        return response()->json($response, 200);
    }
}
