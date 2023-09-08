<?php

namespace App\Http\Controllers;

use JWTAuth;
use Validator;

use App\Models\Course;
use App\Models\Provider;
use App\Models\UserCourse;
use App\Models\Specialty;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CoursesImport;


class CourseController extends Controller
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
     * Display a listing of the Courses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Course::all();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Courses Successfully'
        ];

        return response()->json($response, 200);
    }

    public function excelImport(Request $request)
    {

        // Validar que el archivo se ha enviado
        $request->validate([
            'file' => 'required|mimes:xlsx'
        ]);

        // Intentar importar el archivo
        try {
            // Usar la clase CoursesImport para importar los cursos desde el archivo Excel
            Excel::import(new CoursesImport, $request->file('file'));

            // Retornar una respuesta exitosa
            return response()->json(['message' => 'Cursos importados con éxito!'], 200);
        } catch (\Exception $e) {
            // Retornar un error si algo sale mal
            return response()->json(['message' => 'Hubo un error al importar los cursos. ' . $e->getMessage()], 500);
        }
    }

    public function getByFilter(Request $request)
    {

        $input = $request->all();
        $range = $input['range'] === null ? '%%' : $input['range'];
        $name = $input['name'] === null ? '%%' : '%' . $input['name'] . '%';
        $specialty_id = $input['specialty_id'] === null ? '%%' : '%' . $input['specialty_id'] . '%';
        $category = $input['category'] === null ? '%%' : '%' . $input['category'] . '%';
        $status_id = $input['status_id'] === null ? '%%' : $input['status_id'];

        if ($input['range'] !== null) {
            if ($input['specialty_id'] !== null) {
                $courses = DB::table('courses AS c')
                    ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                    ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                    ->select(
                        'c.*',
                        'p.name AS status_name',
                        'pv.name AS provider_name',
                        DB::raw('DATE_FORMAT(c.start_date,"%d/%m/%Y") AS date'),
                        DB::raw('DATE_FORMAT(c.end_date,"%d/%m/%Y") AS end_date'),
                        DB::raw('null AS users'),
                        DB::raw('(SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.id) AS users_count'),
                        DB::raw('"app" AS origin'),
                        DB::raw('null AS specialty_name')
                    )
                    ->whereRaw("DATE_FORMAT(c.start_date,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                    ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                    ->whereRaw('c.specialty_id LIKE "' . $specialty_id . '"')
                    ->whereRaw('c.category LIKE "' . $category . '"')
                    ->whereRaw('c.status_id LIKE "' . $status_id . '"')
                    ->orderBy('c.start_date', 'desc')
                    ->get();
            } else {
                $courses = DB::table('courses AS c')
                    ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                    ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                    ->select(
                        'c.*',
                        'p.name AS status_name',
                        'pv.name AS provider_name',
                        DB::raw('DATE_FORMAT(c.start_date,"%b %d de %Y") AS date'),
                        DB::raw('null AS users'),
                        DB::raw('(SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.id) AS users_count'),
                        DB::raw('"app" AS origin'),
                        DB::raw('null AS specialty_name')
                    )
                    ->whereRaw("DATE_FORMAT(c.start_date,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                    ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                    ->whereRaw('c.category LIKE "' . $category . '"')
                    ->whereRaw('c.status_id LIKE "' . $status_id . '"')
                    ->orderBy('c.start_date', 'desc')
                    ->get();
            }
        } else {
            if ($input['specialty_id'] !== null) {
                $courses = DB::table('courses AS c')
                    ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                    ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                    ->select(
                        'c.*',
                        'p.name AS status_name',
                        'pv.name AS provider_name',
                        DB::raw('DATE_FORMAT(c.start_date,"%d/%m/%Y") AS date'),
                        DB::raw('DATE_FORMAT(c.end_date,"%d/%m/%Y") AS end_date'),
                        DB::raw('null AS users'),
                        DB::raw('(SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.id) AS users_count'),
                        DB::raw('"app" AS origin'),
                        DB::raw('null AS specialty_name')
                    )
                    ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                    ->whereRaw('c.specialty_id LIKE "' . $specialty_id . '"')
                    ->whereRaw('c.category LIKE "' . $category . '"')
                    ->whereRaw('c.status_id LIKE "' . $status_id . '"')
                    ->orderBy('c.start_date', 'desc')
                    ->get();
            } else {
                $courses = DB::table('courses AS c')
                    ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                    ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                    ->select(
                        'c.*',
                        'p.name AS status_name',
                        'pv.name AS provider_name',
                        DB::raw('DATE_FORMAT(c.start_date,"%d/%m/%Y") AS date'),
                        DB::raw('DATE_FORMAT(c.end_date,"%d/%m/%Y") AS end_date'),
                        DB::raw('null AS users'),
                        DB::raw('(SELECT COUNT(*) FROM user_courses uc WHERE uc.course_id = c.id) AS users_count'),
                        DB::raw('"app" AS origin'),
                        DB::raw('null AS specialty_name')
                    )
                    ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                    ->whereRaw('c.specialty_id LIKE "' . $specialty_id . '"')
                    ->whereRaw('c.category LIKE "' . $category . '"')
                    ->whereRaw('c.status_id LIKE "' . $status_id . '"')
                    ->orderBy('c.start_date', 'desc')
                    ->get();
            }
        }

        foreach ($courses as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS
            // --------------------------------------------------------------------------------------
            $users = DB::table('users AS u')
                ->select('u.*')
                ->join('user_courses AS uc', 'uc.user_id', '=', 'u.id')
                ->whereRaw('uc.course_id = "' . $item->id . '"')
                ->get();

            $item->users_count = $users->count();
            $item->users = $users;
        }

        // PROVEEDORES
        $providers = DB::table('providers AS p')
            ->select(
                'p.id',
                'p.name',
                DB::raw('CONVERT((SELECT COUNT(*) FROM courses c WHERE c.provider_id = p.id), CHAR) AS count')
            )
            ->groupBy('p.id', 'p.name')
            ->get();



        // CURSOS DE MOODLE
        $courses_moodle = $this->getCoursesMoodle($input);
        $merged = $courses->merge($courses_moodle);
        $results = $merged->all();

        // RESUMEN
        $summary = new \stdClass();
        $summary->total_courses = ($courses->count() +  count($courses_moodle)) . "";
        $summary->providers = $providers;
        $summary->training_to_approved = "0";

        $data = new \stdClass();
        $data->summary = $summary;
        $data->data = $results;
        $data->courses_moodle = $courses_moodle;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Courses Successfully'
        ];

        return response()->json($response, 200);
    }

    /**
     * Lista los cursos de moodle, conectandose a la otra base de datos
     * 
     * @return Array()
     */
    private function getCoursesMoodle($input)
    {
        $category = $input['category'] === null ? '%%' : '%' . $input['category'] . '%';
        $name = $input['name'] === null ? '%%' : '%' . $input['name'] . '%';
        $status_id = $input['status_id'] === null ? '%%' : $input['status_id'];
        $specialty_id = $input['specialty_id'] === null ? '%%' : $input['specialty_id'];

        // Base de datos principal
        $bd_local = config('app.database_owner');

        if ($input['range'] !== null) {
            if ($input['specialty_id'] !== null) {
                $courses_aux = DB::connection('mysql_aux')->select("
                    SELECT 
                        cur.id
                        , cur.code
                        , cur.name
                        , cur.shortname
                        , cur.category
                        , cur.hours
                        , cur.start_date
                        , cur.end_date
                        , cur.provide_id
                        , cur.specialty_id
                        , cur.training_request_id
                        , cur.status_id
                        , cur.required
                        , cur.created_at
                        , cur.updated_at
                        , cur.status_name
                        , cur.provider_name
                        , cur.date 
                        , cur.users
                        , cur.users_count
                        , cur.origin
                        , s.name AS specialty_name
                        FROM (
                            SELECT 
                                (c.id + 999) AS id
                                , c.id AS code
                                , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS name
                                , c.shortname
                                , cc.name AS category
                                ,(SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                                , DATE(FROM_UNIXTIME(c.startdate)) AS start_date
                                , if((c.enddate=0), NULL, DATE_FORMAT(DATE(FROM_UNIXTIME(c.enddate)),'%d/%m/%Y')) AS end_date
                                , 2 AS provide_id
                                , null AS training_request_id
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 12, 13) AS status_id
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='obligatorio' AND cd.instanceid=c.id) AS required
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timemodified)),'%Y-%m-%d %H:%m:%s') updated_at
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 'En curso', 'Finalizado') AS status_name
                                , 'DTX' AS provider_name
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y') AS date
                                , null AS users
                                , 0 AS users_count
                                , 'moodle' AS origin
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' and cd.instanceid=c.id) AS specialty_id
                            FROM mdl_course c 
                            LEFT JOIN mdl_course_categories cc ON cc.id = c.category
                        ) AS cur
                        LEFT JOIN " . $bd_local . ".specialties s ON s.id = cur.specialty_id
                    WHERE 
                        DATE_FORMAT(cur.created_at,'%Y-%m-%d') BETWEEN ? AND ?
                        AND UPPER(cur.name)  LIKE UPPER(?)
                        AND cur.category LIKE ?
                        AND cur.status_id LIKE ?
                        AND s.id LIKE ?
                ", array($input['range'][0], $input['range'][1], $name, $category, $status_id, $specialty_id));
            } else {
                $courses_aux = DB::connection('mysql_aux')->select("
                    SELECT 
                        cur.id
                        , cur.code
                        , cur.name
                        , cur.shortname
                        , cur.category
                        , cur.hours
                        , cur.start_date
                        , cur.end_date
                        , cur.provide_id
                        , cur.specialty_id
                        , cur.training_request_id
                        , cur.status_id
                        , cur.required
                        , cur.created_at
                        , cur.updated_at
                        , cur.status_name
                        , cur.provider_name
                        , cur.date 
                        , cur.users
                        , cur.users_count
                        , cur.origin
                        , s.name AS specialty_name
                        FROM (
                            SELECT 
                                (c.id + 999) AS id
                                , c.id AS code
                                , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS name
                                , c.shortname
                                , cc.name AS category
                                ,(SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                                , DATE(FROM_UNIXTIME(c.startdate)) AS start_date
                                , if((c.enddate=0), NULL, DATE_FORMAT(DATE(FROM_UNIXTIME(c.enddate)),'%d/%m/%Y')) AS end_date
                                , 2 AS provide_id
                                , null AS training_request_id
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 12, 13) AS status_id
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='obligatorio' AND cd.instanceid=c.id) AS required
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timemodified)),'%Y-%m-%d %H:%m:%s') updated_at
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 'En curso', 'Finalizado') AS status_name
                                , 'DTX' AS provider_name
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y') AS date
                                , null AS users
                                , 0 AS users_count
                                , 'moodle' AS origin
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' and cd.instanceid=c.id) AS specialty_id
                            FROM mdl_course c 
                            LEFT JOIN mdl_course_categories cc ON cc.id = c.category
                        ) AS cur
                        LEFT JOIN " . $bd_local . ".specialties s ON s.id = cur.specialty_id
                    WHERE 
                        DATE_FORMAT(cur.created_at,'%Y-%m-%d') BETWEEN ? AND ?
                        AND UPPER(cur.name)  LIKE UPPER(?)
                        AND cur.category LIKE ?
                        AND cur.status_id LIKE ?
                ", array($input['range'][0], $input['range'][1], $name, $category, $status_id));
            }
        } else {
            if ($input['specialty_id'] !== null) {
                $courses_aux = DB::connection('mysql_aux')->select("
                    SELECT 
                        cur.id
                        , cur.code
                        , cur.name
                        , cur.shortname
                        , cur.category
                        , cur.hours
                        , cur.start_date
                        , cur.end_date
                        , cur.provide_id
                        , cur.specialty_id
                        , cur.training_request_id
                        , cur.status_id
                        , cur.required
                        , cur.created_at
                        , cur.updated_at
                        , cur.status_name
                        , cur.provider_name
                        , cur.date
                        , cur.users
                        , cur.users_count
                        , cur.origin
                        , s.name AS specialty_name
                        FROM (
                            SELECT 
                                (c.id + 999) AS id
                                , c.id AS code
                                , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS name
                                , c.shortname
                                , cc.name AS category
                                , (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                                , DATE(FROM_UNIXTIME(c.startdate)) AS start_date
                                , if((c.enddate=0), NULL, DATE_FORMAT(DATE(FROM_UNIXTIME(c.enddate)),'%d/%m/%Y')) AS end_date
                                , 2 AS provide_id
                                , null AS training_request_id
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 12, 13) AS status_id
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='obligatorio' AND cd.instanceid=c.id) AS required
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timemodified)),'%Y-%m-%d %H:%m:%s') updated_at
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 'En curso', 'Finalizado') AS status_name
                                , 'DTX' AS provider_name
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y') AS date
                                , null AS users
                                , 0 AS users_count
                                , 'moodle' AS origin
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' and cd.instanceid=c.id) AS specialty_id
                            FROM mdl_course c 
                            LEFT JOIN mdl_course_categories cc ON cc.id = c.category
                        ) AS cur
                        LEFT JOIN " . $bd_local . ".specialties s ON s.id = cur.specialty_id
                    WHERE 
                        UPPER(cur.name)  LIKE UPPER(?)
                        AND cur.category LIKE ?
                        AND cur.status_id LIKE ?
                        AND s.id LIKE ?
                ", array($name, $category, $status_id, $specialty_id));
            } else {
                $courses_aux = DB::connection('mysql_aux')->select("
                    SELECT 
                        cur.id
                        , cur.code
                        , cur.name
                        , cur.shortname
                        , cur.category
                        , cur.hours
                        , cur.start_date 
                        , cur.end_date
                        , cur.provide_id
                        , cur.specialty_id
                        , cur.training_request_id
                        , cur.status_id
                        , cur.required
                        , cur.created_at
                        , cur.updated_at
                        , cur.status_name
                        , cur.provider_name
                        , cur.date
                        , cur.users
                        , cur.users_count
                        , cur.origin
                        , s.name AS specialty_name
                        FROM (
                            SELECT 
                                (c.id + 999) AS id
                                , c.id AS code
                                , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS name
                                , c.shortname
                                , cc.name AS category
                                , (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                                , DATE(FROM_UNIXTIME(c.startdate)) AS start_date
                                , if((c.enddate=0), NULL, DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y')) AS end_date
                                , 2 AS provide_id
                                , null AS training_request_id
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 12, 13) AS status_id
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='obligatorio' AND cd.instanceid=c.id) AS required
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timemodified)),'%Y-%m-%d %H:%m:%s') updated_at
                                , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 'En curso', 'Finalizado') AS status_name
                                , 'DTX' AS provider_name
                                , DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y') AS date
                                , null AS users
                                , 0 AS users_count
                                , 'moodle' AS origin
                                , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' and cd.instanceid=c.id) AS specialty_id
                            FROM mdl_course c 
                            LEFT JOIN mdl_course_categories cc ON cc.id = c.category
                        ) AS cur
                        LEFT JOIN " . $bd_local . ".specialties s ON s.id = cur.specialty_id
                    WHERE 
                        UPPER(cur.name)  LIKE UPPER(?)
                        AND cur.category LIKE ?
                        AND cur.status_id LIKE ?
                ", array($name, $category, $status_id));
            }
        }

        foreach ($courses_aux as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS
            // --------------------------------------------------------------------------------------

            $users = DB::connection('mysql_aux')->select(
                "
                SELECT 
                    u.id
                    , u.email
                    , 'S' as attend_how
                    , CONCAT(u.lastname, ' ',u.firstname) lastname
                    , null AS position
                    , u.city AS city
                    , null AS area
                FROM mdl_user u
                INNER JOIN mdl_role_assignments ra ON ra.userid = u.id and ra.roleid <> '3' 
                INNER JOIN mdl_context ct ON ct.id = ra.contextid
                INNER JOIN mdl_course c ON c.id = ct.instanceid
                INNER JOIN mdl_role r ON r.id = ra.roleid
                INNER JOIN mdl_course_categories cc ON cc.id = c.category
                WHERE c.id = ?
                ",
                array($item->code)
            );

            $item->users_count = count($users);
            $item->users = $users;
        }

        return $courses_aux;
    }

    /**
     * Lista los cursos por usuario
     * 
     * @return \Illuminate\Http\Response
     */
    public function getDashboardByFilter(Request $request)
    {

        try {
            $input = $request->all();
            //$range = $input['range'] === null ? '%%' : $input['range'];
            $name = $input['name'] === null ? '%%' : '%%' . $input['name'] . '%%';
            $user_email = $input['user_email'] === null ? '%%' : $input['user_email'];
            // $area = $input['area'] === null ? '%%' : $input['area'];
            $position = $input['position'] === null ? '%%' : $input['position'];
            //$city = $input['city'] === null ? '%%' : $input['city'];
            $status_id = $input['status_id'] === null ? '%%' : $input['status_id'];
            $tipo = isset($input['tipo']) ? $input['tipo'] : 1;

            // VALIDAR EL TIPO DE REPO / 1: GENERAL, 2: INDIVIDUAL
            if ($tipo === 2) {
                $user_email = $this->user->email;
            }

            // VALIDAR LA CIUDAD
            // PARA EL ROL 4: Encargado de oficina y ROL 5: Socio, Cargar solo lo de su ciudad, ROL 10: Gerente
            if ($this->user->rol_id == 4 || $this->user->rol_id == 5 || $this->user->rol_id == 10) {
                $city = $this->user->city;
                $area = $this->user->area;
            } else {
                $city = $input['city'] === null ? '%%' : $input['city'];
                $area = $input['area'] === null ? '%%' : $input['area'];
            }

            if ($input['range'] !== null) {
                $courses = DB::table('courses AS c')
                    ->join('user_courses AS uc', 'uc.course_id', '=', 'c.id')
                    ->join('users AS u', 'u.id', '=', 'uc.user_id')
                    ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                    ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                    ->join('rols AS r', 'r.id', '=', 'u.rol_id')
                    ->select(
                        'c.id AS course_id',
                        'u.lastname AS user_name',
                        'u.area',
                        'u.city',
                        'u.position',
                        'c.name AS course_name',
                        DB::raw('DATE_FORMAT(c.start_date,"%d/%m/%Y") AS date'),
                        DB::raw('DATE_FORMAT(c.end_date,"%d/%m/%Y") AS end_date'),
                        DB::raw('CONCAT(ROUND(IFNULL(uc.progress, 0), 0), "%")  AS progress'),
                        'uc.qualification',
                        'pv.name AS provider_name',
                        'uc.hours',
                        'p.name AS status_name',
                        'r.name AS role_name',
                        'u.email AS user_email',
                        'c.required',
                        DB::raw('CASE c.required WHEN "S" THEN "Si" WHEN "N" THEN "No" END AS required'),
                        'c.status_id'
                        // , DB::raw('DATE_FORMAT(c.created_at,"%b %d de %Y") AS date')
                    )
                    ->whereRaw("DATE_FORMAT(c.created_at,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                    ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                    ->whereRaw('u.email LIKE "' . $user_email . '"')
                    ->whereRaw('u.area LIKE "' . $area . '"')
                    ->whereRaw('u.position LIKE "' . $position . '"')
                    ->whereRaw('u.city LIKE "' . $city . '"')
                    ->whereRaw('c.status_id LIKE "' . $status_id . '"')
                    ->orderBy('c.created_at', 'desc')
                    ->get();
            } else {
                $courses = DB::table('courses AS c')
                    ->join('user_courses AS uc', 'uc.course_id', '=', 'c.id')
                    ->join('users AS u', 'u.id', '=', 'uc.user_id')
                    ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                    ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                    ->join('rols AS r', 'r.id', '=', 'u.rol_id')
                    ->select(
                        'c.id AS course_id',
                        'u.lastname AS user_name',
                        'u.area',
                        'u.city',
                        'u.position',
                        'c.name AS course_name',
                        DB::raw('DATE_FORMAT(c.start_date,"%d/%m/%Y") AS date'),
                        DB::raw('DATE_FORMAT(c.end_date,"%d/%m/%Y") AS end_date'),
                        DB::raw('CONCAT(ROUND(IFNULL(uc.progress, 0), 0), "%")  AS progress'),
                        'uc.qualification',
                        'pv.name AS provider_name',
                        'uc.hours',
                        'p.name AS status_name',
                        'r.name AS role_name',
                        'u.email AS user_email',
                        DB::raw('CASE c.required WHEN "S" THEN "Si" WHEN "N" THEN "No" END AS required')
                        // , DB::raw('DATE_FORMAT(c.created_at,"%b %d de %Y") AS date')
                        ,
                        'c.status_id'
                    )
                    ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                    ->whereRaw('u.email LIKE "' . $user_email . '"')
                    ->whereRaw('u.area LIKE "' . $area . '"')
                    ->whereRaw('u.position LIKE "' . $position . '"')
                    ->whereRaw('u.city LIKE "' . $city . '"')
                    ->whereRaw('c.status_id LIKE "' . $status_id . '"')
                    ->orderBy('c.created_at', 'desc')
                    ->get();
            }

            // VALIDAR EL TIPO DE REPO / 1: GENERAL, 2: INDIVIDUAL => CUSROS COMO DOCENTE
            $courses_teach = null;
            $results_teach = null;
            $hours_teach = 0;

            if ($tipo === 2) {
                if ($input['range'] !== null) {

                    $courses_teach = DB::table('courses AS c')
                        ->join('user_courses AS uc', 'uc.course_id', '=', 'c.id')
                        ->join('users AS u', 'u.id', '=', 'uc.user_id')
                        ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                        ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                        ->join('rols AS r', 'r.id', '=', 'u.rol_id')
                        ->select(
                            'c.id AS course_id',
                            'c.name AS course_name',
                            DB::raw('CONCAT(ROUND(IFNULL(uc.progress, 0), 0), "%")  AS progress'),
                            'uc.qualification',
                            'uc.hours',
                            'u.email AS user_email'
                        )
                        ->whereRaw("DATE_FORMAT(c.created_at,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                        ->whereRaw('u.email LIKE "' . $user_email . '"')
                        ->whereRaw('uc.attend_how = "T"')
                        ->orderBy('c.created_at', 'desc')
                        ->get();
                } else {
                    $courses_teach = DB::table('courses AS c')
                        ->join('user_courses AS uc', 'uc.course_id', '=', 'c.id')
                        ->join('users AS u', 'u.id', '=', 'uc.user_id')
                        ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                        ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                        ->join('rols AS r', 'r.id', '=', 'u.rol_id')
                        ->select(
                            'c.id AS course_id',
                            'c.name AS course_name',
                            DB::raw('CONCAT(ROUND(IFNULL(uc.progress, 0), 0), "%")  AS progress'),
                            'uc.qualification',
                            'uc.hours',
                            'u.email AS user_email'
                        )
                        ->whereRaw('u.email LIKE "' . $user_email . '"')
                        ->whereRaw('uc.attend_how = "T"')
                        ->orderBy('c.created_at', 'desc')
                        ->get();
                }
            }

            // CURSOS DE MOODLE
            $courses_moodle = $this->getDashboardByFilterMoodle($input);
            $merged = $courses->merge($courses_moodle);
            $results = $merged->all();

            if ($courses_teach != null) {
                // CURSOS DE MOODLE TEACH
                $courses_teach_moodle = $this->getCourseTeachMoodle($user_email, $input['range']);
                $merged_teach = $courses_teach->merge($courses_teach_moodle);
                $results_teach = $merged_teach->all();

                // CONTAR LA CANTIDAD DE HORAS COMO TEACH
                foreach ($results_teach as $item) {
                    $hours_teach += $item->hours;
                }
            }

            // CONTAR LA CANTIDAD DE HORAS DE CURSOS FINALIZADOS
            $hours_aprove = 0;
            $hours_total = 0;
            foreach ($results as $item) {
                if ($item->status_id == 13) $hours_aprove += $item->hours;
                $hours_total += $item->hours;
            }

            // IDS CURSOS
            // $lstID = array();
            $lstID[] = 0;
            foreach ($courses as $item) {
                $lstID[] = $item->course_id;
            }

            $providersA = DB::table('providers AS p')
                ->leftJoin('courses AS c', 'p.id', '=', 'c.provider_id')
                ->select(
                    'p.id',
                    'p.name',
                    DB::raw('COUNT(p.id) AS count') // CANTIDAD DE CURSOS
                )
                ->whereIn('c.id', $lstID)
                ->groupBy('p.id', 'p.name')
                ->get();

            $providersB = DB::table('providers AS p')
                ->select(
                    'p.id',
                    'p.name',
                    DB::raw('0 AS count') // CANTIDAD DE CURSOS
                )
                ->where('p.id', 3) // DTX
                ->get();

            $courses_moodle = $this->getDashboardByFilterMoodle($input);
            $mergedProviders = $providersA->merge($providersB);
            $providers = $mergedProviders->all();

            // ------------------------------------------------
            // INICIO ARMAR DATOS PARA LA GRAFICA
            // ------------------------------------------------
            $labels = array();
            $series = array();

            foreach ($providers as $item) {
                $labels[] = $item->name;
                $series[] = $item->id == 3 ? ($item->count + count($courses_moodle)) : $item->count;
            }

            $high = max($series);

            $chart = new \stdClass();
            $chart->labels = $labels;
            $chart->series = array($series);
            $chart->high = $high > 0 ? ($high + 10) : $high;
            // ------------------------------------------------
            // FIN ARMAR DATOS PARA LA GRAFICA
            // ------------------------------------------------

            $summary = new \stdClass();
            $summary->total_courses = $courses->count() . "";
            $summary->providers = $providers;
            $summary->training_to_approved = "0";
            $summary->hours_teach = $hours_teach;
            $summary->hours_aprove = $hours_aprove;
            $summary->hours_total = $hours_total;

            $data = new \stdClass();
            $data->summary = $summary;
            $data->data = $results;
            $data->chart = $chart;
            $data->teach = $results_teach;

            $response = [
                'success' => true,
                'data' => $data,
                'lstID' => $lstID,
                'message' => 'List Courses Dashboard Successfully'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al consultar cursos.'
            ];
            return response()->json($response, 400);
        }
    }

    /**
     * Lista los cursos de moodle por usuario, conectandose a la otra base de datos
     * 
     * @return Array()
     */
    private function getDashboardByFilterMoodle($input)
    {
        $range = $input['range'] === null ? '%%' : $input['range'];
        $name = $input['name'] === null ? '%%' : '%%' . $input['name'] . '%%';
        $user_email = $input['user_email'] === null ? '%%' : $input['user_email'];
        //$area = $input['area'] === null ? '%%' : $input['area']; // Pendiente
        $position = $input['position'] === null ? '%%' : $input['position']; // Pendiente
        // $city = $input['city'] === null ? '%%' : $input['city'];
        $status_id = $input['status_id'] === null ? '%%' : $input['status_id'];
        $tipo = isset($input['tipo']) ? $input['tipo'] : 1;

        // VALIDAR EL TIPO DE REPO / 1: GENERAL, 2: INDIVIDUAL
        if ($tipo === 2) {
            $user_email = $this->user->email;
        }

        // VALIDAR LA CIUDAD
        // PARA EL ROL 4: Encargado de oficina y ROL 5: Socio, Cargar solo lo de su ciudad, ROL 10: Gerente
        if ($this->user->rol_id == 4 || $this->user->rol_id == 5 || $this->user->rol_id == 10) {
            $city = $this->user->city;
            $area = $this->user->area;
        } else {
            $city = $input['city'] === null ? '%%' : $input['city'];
            $area = $input['area'] === null ? '%%' : $input['area'];
        }

        // Base de datos principal
        $bd_local = config('app.database_owner');

        if ($input['range'] !== null) {
            $courses_aux = DB::connection('mysql_aux')->select("
                SELECT 
                    cur.course_id
                    , cur.user_name
                    , cur.area
                    , cur.city
                    , cur.position
                    , cur.date 
                    , cur.end_date
                    , cur.course_name
                    , CONCAT(ROUND(IFNULL(cur.progress, 0), 0), '%') AS progress
                    , ROUND(IFNULL(cur.qualification, 0), 1) AS qualification
                    , cur.provider_name
                    , IFNULL(cur.hours, 0) AS hours
                    , cur.status_name
                    , cur.role_name
                    , cur.user_email
                    , CASE cur.required WHEN '1' THEN 'Si' WHEN '2' THEN 'No' END AS required
                    , cur.status_id
                FROM
                (
                    SELECT 
                        c.id as course_id
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                        , CONCAT(u.lastname, ' ',u.firstname) AS user_name
                        , ug.area
                        , u.city
                        , ug.position
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y') AS date
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.enddate)),'%d/%m/%Y') AS end_date
                        , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS course_name
                        , ((SELECT count(completionstate) FROM mdl_course_modules_completion where userid=u.id and completionstate<>0 and coursemoduleid in (select id from mdl_course_modules where course=c.id and completion>1)) / (select count(id) from mdl_course_modules where course=c.id and completion>1))*100 AS progress 
                        , (select gg.finalgrade from mdl_grade_grades gg left join mdl_grade_items gi on gi.id = gg.itemid where gg.userid = u.id and gi.courseid=c.id and gi.itemtype = 'course') AS qualification
                        , 'DTX' AS provider_name
                        , (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 12, 13) AS status_id
                        , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 'En curso', 'Finalizado') AS status_name
                        , u.email AS user_email
                        , ru.name AS role_name
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='obligatorio' AND cd.instanceid=c.id) AS required
                    FROM mdl_user u
                    INNER JOIN mdl_role_assignments ra ON ra.userid = u.id and ra.roleid <> '3' 
                    INNER JOIN mdl_context ct ON ct.id = ra.contextid
                    INNER JOIN mdl_course c ON c.id = ct.instanceid
                    INNER JOIN mdl_role r ON r.id = ra.roleid
                    INNER JOIN mdl_course_categories cc ON cc.id = c.category
                    LEFT JOIN " . $bd_local . ".users ug ON ug.email = u.email
                    LEFT JOIN " . $bd_local . ".rols ru ON ru.id = ug.rol_id
                ) cur
                WHERE 
                    DATE_FORMAT(cur.created_at,'%Y-%m-%d') BETWEEN ? AND ?
                    AND UPPER(cur.course_name)  LIKE UPPER(?)
                    AND cur.user_email LIKE ?
                    AND cur.city LIKE ?
                    AND cur.area LIKE ?
                    AND cur.position LIKE ?
                    AND cur.status_id LIKE ?
            ", array($input['range'][0], $input['range'][1], $name, $user_email, $city, $area, $position, $status_id));
        } else {
            $courses_aux = DB::connection('mysql_aux')->select("
                SELECT 
                    cur.course_id
                    , cur.user_name
                    , cur.area
                    , cur.city
                    , cur.position
                    , cur.date
                    , cur.end_date
                    , cur.course_name
                    , CONCAT(ROUND(IFNULL(cur.progress, 0), 0), '%') AS progress
                    , ROUND(IFNULL(cur.qualification, 0), 1) AS qualification
                    , cur.provider_name
                    , IFNULL(cur.hours, 0) AS hours
                    , cur.status_name
                    , cur.role_name
                    , cur.user_email
                    , CASE cur.required WHEN '1' THEN 'Si' WHEN '2' THEN 'No' END AS required
                    , cur.status_id
                FROM
                (
                    SELECT 
                        c.id as course_id
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                        , CONCAT(u.lastname, ' ',u.firstname) AS user_name
                        , ug.area
                        , u.city
                        , ug.position
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.startdate)),'%d/%m/%Y') AS date
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.enddate)),'%d/%m/%Y') AS end_date
                        , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS course_name
                        , ((SELECT count(completionstate) FROM mdl_course_modules_completion where userid=u.id and completionstate<>0 and coursemoduleid in (select id from mdl_course_modules where course=c.id and completion>1)) / (select count(id) from mdl_course_modules where course=c.id and completion>1))*100 AS progress 
                        , (select gg.finalgrade from mdl_grade_grades gg left join mdl_grade_items gi on gi.id = gg.itemid where gg.userid = u.id and gi.courseid=c.id and gi.itemtype = 'course') AS qualification
                        , 'DTX' AS provider_name
                        , (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 12, 13) AS status_id
                        , if(c.enddate=0 or DATE(FROM_UNIXTIME(c.enddate))>=now(), 'En curso', 'Finalizado') AS status_name
                        , u.email AS user_email
                        , ru.name AS role_name
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='obligatorio' AND cd.instanceid=c.id) AS required
                    FROM mdl_user u
                    INNER JOIN mdl_role_assignments ra ON ra.userid = u.id and ra.roleid <> '3' 
                    INNER JOIN mdl_context ct ON ct.id = ra.contextid
                    INNER JOIN mdl_course c ON c.id = ct.instanceid
                    INNER JOIN mdl_role r ON r.id = ra.roleid
                    INNER JOIN mdl_course_categories cc ON cc.id = c.category
                    LEFT JOIN " . $bd_local . ".users ug ON ug.email = u.email
                    LEFT JOIN " . $bd_local . ".rols ru ON ru.id = ug.rol_id
                ) cur
                WHERE 
                    UPPER(cur.course_name)  LIKE UPPER(?)
                    AND cur.user_email LIKE ?
                    AND cur.city LIKE ?
                    AND cur.area LIKE ?
                    AND cur.position LIKE ?
                    AND cur.status_id LIKE ?
            ", array($name, $user_email, $city, $area, $position, $status_id));
        }

        return $courses_aux;
    }

    private function getCourseTeachMoodle($user_email, $range)
    {
        // Base de datos principal
        $bd_local = config('app.database_owner');

        if ($range !== null) {
            $courses_aux = DB::connection('mysql_aux')->select("
                SELECT 
                    cur.course_id
                    , cur.course_name
                    , null AS progress
                    , ROUND(IFNULL(cur.qualification, 0), 1) AS qualification
                    , IFNULL(cur.hours, 0) AS hours
                    , cur.user_email
                FROM
                (
                    SELECT c.id AS course_id
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                        , c.fullname AS course_name
                        , 'T' AS attend_how
                        , u.email AS user_email
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , (SELECT gg.finalgrade FROM mdl_grade_grades gg left join mdl_grade_items gi ON gi.id = gg.itemid WHERE gg.userid = u.id AND gi.courseid=c.id AND gi.itemtype = 'course') AS qualification
                    FROM mdl_course c 
                        LEFT JOIN mdl_context cx ON c.id = cx.instanceid 
                        LEFT join mdl_role_assignments ra ON cx.id = ra.contextid AND ra.roleid = '3' 
                        left jOIN mdl_user u ON ra.userid = u.id 
                        LEFT JOIN " . $bd_local . ".users ug ON ug.email = u.email
                        LEFT JOIN " . $bd_local . ".rols ru ON ru.id = ug.rol_id
                    WHERE cx.contextlevel = '50' AND u.id IS NOT null  
                ) cur
                WHERE 
                    DATE_FORMAT(cur.created_at,'%Y-%m-%d') BETWEEN ? AND ?
                    AND cur.user_email LIKE ?
            ", array($range[0], $range[1], $user_email));
        } else {
            $courses_aux = DB::connection('mysql_aux')->select("
                SELECT 
                    cur.course_id
                    , cur.course_name
                    , null AS progress
                    , ROUND(IFNULL(cur.qualification, 0), 1) AS qualification
                    , IFNULL(cur.hours, 0) AS hours
                    , cur.user_email
                FROM
                (
                    SELECT c.id AS course_id
                        , c.fullname AS course_name
                        , 'T' AS attend_how
                        , u.email AS user_email
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , (SELECT gg.finalgrade FROM mdl_grade_grades gg left join mdl_grade_items gi ON gi.id = gg.itemid WHERE gg.userid = u.id AND gi.courseid=c.id AND gi.itemtype = 'course') AS qualification
                    FROM mdl_course c 
                        LEFT JOIN mdl_context cx ON c.id = cx.instanceid 
                        LEFT join mdl_role_assignments ra ON cx.id = ra.contextid AND ra.roleid = '3' 
                        left jOIN mdl_user u ON ra.userid = u.id 
                        LEFT JOIN " . $bd_local . ".users ug ON ug.email = u.email
                        LEFT JOIN " . $bd_local . ".rols ru ON ru.id = ug.rol_id
                    WHERE cx.contextlevel = '50' AND u.id IS NOT null  
                ) cur
                WHERE 
                    cur.user_email LIKE ?
            ", array($user_email));
        }

        return $courses_aux;
    }

    /**
     * Lista los cursos por usuario instructor
     * 
     * @return \Illuminate\Http\Response
     */
    public function getInstructorsByFilter(Request $request)
    {

        $input = $request->all();
        $range = $input['range'] === null ? '%%' : $input['range'];
        $name = $input['name'] === null ? '%%' : '%%' . $input['name'] . '%%';
        $user_email = $input['user_email'] === null ? '%%' : $input['user_email'];

        // VALIDAR EL TIPO DE REPO / 1: GENERAL, 2: INDIVIDUAL => CUSROS COMO DOCENTE
        $courses_teach = null;
        $results_teach = null;
        $hours_teach = 0;

        if ($input['range'] !== null) {
            $courses_teach = DB::table('courses AS c')
                ->join('user_courses AS uc', 'uc.course_id', '=', 'c.id')
                ->join('users AS u', 'u.id', '=', 'uc.user_id')
                ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                ->join('rols AS r', 'r.id', '=', 'u.rol_id')
                ->select(
                    'u.area',
                    'u.city',
                    'u.lastname',
                    'u.position',
                    'c.id AS course_id',
                    'c.name AS course_name',
                    DB::raw('CONCAT(ROUND(IFNULL(uc.progress, 0), 0), "%")  AS progress'),
                    'uc.qualification',
                    'uc.hours',
                    'u.email AS user_email'
                )
                ->whereRaw("DATE_FORMAT(c.created_at,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                ->whereRaw('u.email LIKE "' . $user_email . '"')
                ->whereRaw('uc.attend_how = "T"')
                ->orderBy('c.created_at', 'desc')
                ->get();
        } else {
            $courses_teach = DB::table('courses AS c')
                ->join('user_courses AS uc', 'uc.course_id', '=', 'c.id')
                ->join('users AS u', 'u.id', '=', 'uc.user_id')
                ->join('parameters AS p', 'p.id', '=', 'c.status_id')
                ->join('providers AS pv', 'pv.id', '=', 'c.provider_id')
                ->join('rols AS r', 'r.id', '=', 'u.rol_id')
                ->select(
                    'u.area',
                    'u.city',
                    'u.lastname',
                    'u.position',
                    'c.id AS course_id',
                    'c.name AS course_name',
                    DB::raw('CONCAT(ROUND(IFNULL(uc.progress, 0), 0), "%")  AS progress'),
                    'uc.qualification',
                    'uc.hours',
                    'u.email AS user_email'
                )
                ->whereRaw('(UPPER(c.name) LIKE UPPER("' . $name . '") OR UPPER(c.shortname) LIKE UPPER("' . $name . '"))')
                ->whereRaw('u.email LIKE "' . $user_email . '"')
                ->whereRaw('uc.attend_how = "T"')
                ->orderBy('c.created_at', 'desc')
                ->get();
        }

        // CURSOS DE MOODLE TEACH
        $courses_teach_moodle = $this->getInstructorsMoodle($input);
        $merged_teach = $courses_teach->merge($courses_teach_moodle);
        $results_teach = $merged_teach->all();

        // CONTAR LA CANTIDAD DE HORAS COMO TEACH
        foreach ($results_teach as $item) {
            $hours_teach += $item->hours;
        }

        $summary = new \stdClass();
        $summary->hours_teach = $hours_teach;

        $data = new \stdClass();
        $data->summary = $summary;
        $data->data = $results_teach;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Courses Instructors Successfully'
        ];

        return response()->json($response, 200);
    }

    private function getInstructorsMoodle($input)
    {
        $range = $input['range'] === null ? '%%' : $input['range'];
        $name = $input['name'] === null ? '%%' : '%%' . $input['name'] . '%%';
        $user_email = $input['user_email'] === null ? '%%' : $input['user_email'];

        // Base de datos principal
        $bd_local = config('app.database_owner');

        if ($input['range'] !== null) {
            $courses_aux = DB::connection('mysql_aux')->select("
                SELECT 
                    cur.area
                    , cur.city
                    , cur.lastname
                    , cur.position
                    , cur.course_id
                    , cur.course_name
                    , null AS progress
                    , ROUND(IFNULL(cur.qualification, 0), 1) AS qualification
                    , IFNULL(cur.hours, 0) AS hours
                    , cur.user_email
                FROM
                (
                    SELECT 
                        ug.area
                        , ug.city
                        , ug.lastname
                        , ug.position
                        , c.id AS course_id
                        , DATE_FORMAT(DATE(FROM_UNIXTIME(c.timecreated)),'%Y-%m-%d %H:%m:%s') created_at
                        , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS course_name
                        , 'T' AS attend_how
                        , u.email AS user_email
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , (SELECT gg.finalgrade FROM mdl_grade_grades gg left join mdl_grade_items gi ON gi.id = gg.itemid WHERE gg.userid = u.id AND gi.courseid=c.id AND gi.itemtype = 'course') AS qualification
                    FROM mdl_course c 
                        LEFT JOIN mdl_context cx ON c.id = cx.instanceid 
                        LEFT join mdl_role_assignments ra ON cx.id = ra.contextid AND ra.roleid = '3' 
                        LEFT jOIN mdl_user u ON ra.userid = u.id 
                        LEFT JOIN " . $bd_local . ".users ug ON ug.email = u.email
                        LEFT JOIN " . $bd_local . ".rols ru ON ru.id = ug.rol_id
                    WHERE cx.contextlevel = '50' AND u.id IS NOT null  
                ) cur
                WHERE 
                    DATE_FORMAT(cur.created_at,'%Y-%m-%d') BETWEEN ? AND ?
                    AND cur.course_name LIKE ?
                    AND cur.user_email LIKE ?
            ", array($input['range'][0], $input['range'][1], $name, $user_email));
        } else {
            $courses_aux = DB::connection('mysql_aux')->select("
                SELECT 
                    cur.area
                    , cur.city
                    , cur.lastname
                    , cur.position
                    , cur.course_id
                    , cur.course_name
                    , null AS progress
                    , ROUND(IFNULL(cur.qualification, 0), 1) AS qualification
                    , IFNULL(cur.hours, 0) AS hours
                    , cur.user_email
                FROM
                (
                    SELECT 
                        ug.area
                        , ug.city
                        , ug.lastname
                        , ug.position
                        , c.id AS course_id
                        , concat('<a target=\"_new\" href=\"https://dtx.grantthornton.mx/course/view.php?id=', c.id, '\">', c.fullname, '</a>') AS course_name
                        , 'T' AS attend_how
                        , u.email AS user_email
                        , (SELECT cd.value FROM mdl_customfield_data cd left join mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='horascurso' AND cd.instanceid=c.id) AS hours
                        , (SELECT gg.finalgrade FROM mdl_grade_grades gg left join mdl_grade_items gi ON gi.id = gg.itemid WHERE gg.userid = u.id AND gi.courseid=c.id AND gi.itemtype = 'course') AS qualification
                    FROM mdl_course c 
                        LEFT JOIN mdl_context cx ON c.id = cx.instanceid 
                        LEFT join mdl_role_assignments ra ON cx.id = ra.contextid AND ra.roleid = '3' 
                        left jOIN mdl_user u ON ra.userid = u.id 
                        LEFT JOIN " . $bd_local . ".users ug ON ug.email = u.email
                        LEFT JOIN " . $bd_local . ".rols ru ON ru.id = ug.rol_id
                    WHERE cx.contextlevel = '50' AND u.id IS NOT null  
                ) cur
                WHERE 
                    cur.course_name LIKE ?
                    AND cur.user_email LIKE ?
            ", array($name, $user_email));
        }

        return $courses_aux;
    }

    /**
     * Store a newly created Courses in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            // 'code' => 'required',
            // 'name' => 'required',
            'hours' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'provider_id' => 'required',
            // 'training_request_id' => 'required',
        ];

        $messages = [
            // 'code.required' => 'El código es obligatorio.',
            // 'name.required' => 'El nombre es obligatorio.',
            'hours.required' => 'Las horas son obligatorias.',
            'start_date.required' => 'La fecha inicio es obligatoria.',
            'end_date.required' => 'La fecha fin es obligatoria.',
            'provider_id.required' => 'El proveedor es obligatorio.',
            // 'training_request_id.required' => 'La solicitud de entrenamiento es obligatoria.'
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

        $course = new Course;
        if (isset($input['code'])) $course->code = $input['code'];
        if (isset($input['name'])) $course->name = $input['name'];
        else $course->name = $input['shortname'];

        $course->shortname = $input['shortname'];
        $course->category = $input['category'];
        $course->hours = $input['hours'];
        $course->start_date = $input['start_date'];
        $course->end_date = $input['end_date'];
        $course->provider_id = $input['provider_id'];
        $course->required = $input['required'];
        if (isset($input['training_request_id'])) $course->training_request_id = $input['training_request_id'];
        $course->status_id = 12; // EN CURSO
        $course->specialty_id = $input['specialty_id'];
        // GUARDAR
        $course->save();

        // CÓDIGO
        // DTX-anio-id
        $code = 'DTX-' . date("Y") . '-' . $course->id;

        $course->code = $code;
        $course->save();

        $response = [
            'success' => true,
            'data' => $course,
            'message' => 'Curso creado correctamente.'
        ];

        return response()->json($response, 200);
    }



    /**
     * Store a newly import Courses excel in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeExcel(Request $request)
    {
        $inputs = $request->all();


        $rules = [
            'shortname' => 'required',
            'hours' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'provider_id' => 'required',
            'specialty_id' => 'required',
            'status_id' => 'required',
            'required' => 'required',
        ];

        $messages = [
            'shortname.required' => 'El nombre es obligatorio.',
            'hours.required' => 'Las horas son obligatorias.',
            'start_date.required' => 'La fecha inicio es obligatoria.',
            'end_date.required' => 'La fecha fin es obligatoria.',
            'provider_id.required' => 'El proveedor es obligatorio.',
            'specialty_id.required' => 'La especialidad es obligatoria.',
            'status_id.required' => 'El estado es obligatorio.',
            'required.required' => 'Se debe definir si es requerido.'
        ];

        // VALIDACIONES

        $providers = Provider::all()->pluck('id');
        $specialties = Specialty::all()->pluck('id');
        $statuses = Parameter::all()->pluck('id');

        $responses = [];

        foreach ($inputs as $input) {

            $validator = Validator::make($input, $rules, $messages);

            if ($validator->fails()) {
                $responses[] = [
                    'success' => false,
                    'data' => null,
                    'message' => $validator->errors(),
                ];
                return response()->json($response, 200);
            }

            if (!$providers->contains($input['provider_id'])) {
                $responses[] = [
                    'success' => false,
                    'data' => null,
                    'message' => 'El proveedor con código: ' . $input['provider_id'] . ', No existe.',
                ];
                continue;
            }

            if (!$specialties->contains($input['specialty_id'])) {
                $responses[] = [
                    'success' => false,
                    'data' => null,
                    'message' => 'La especialidad con código: ' . $input['specialty_id'] . ', No existe.',
                ];
                continue;
            }

            if (!$statuses->contains($input['status_id'])) {
                $responses[] = [
                    'success' => false,
                    'data' => null,
                    'message' => 'El estado con código: ' . $input['status_id'] . ', No existe.',
                ];
                continue;
            }

            $courseData = [
                'code' => $input['code'],
                'name' => $input['name'] ?? $input['shortname'],
                'shortname' => $input['shortname'],
                'category' => $input['category'],
                'hours' => $input['hours'],
                'start_date' => $input['start_date'],
                'end_date' => $input['end_date'],
                'provider_id' => $input['provider_id'],
                'required' => $input['required'],
                'status_id' => $input['status_id'],
                'specialty_id' => $input['specialty_id'],
            ];

            $course = Course::updateOrCreate(['code' => $input['code']], $courseData);

            $responses[] = [
                'success' => true,
                'data' => $course,
                'message' => 'Curso ' . $course->code . ' - ' . $course->shortname . ' importado correctamente.',
            ];
        }

        return response()->json($responses, 200);
    }



    /**
     * Store a newly import Users excel in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeUsersExcel(Request $request)
    {
        $input = $request->all();

        $rules = [
            'course_code' => 'required',
            'user_email' => 'required',
            'attend_how' => 'required',
            'progress' => 'required',
            'qualification' => 'required',
            'hours' => 'required',
            'status' => 'required',
            'objective_id' => 'required',
        ];

        $messages = [
            'course_code.required' => 'El curso es obligatorio.',
            'user_email.required' => 'El correo del usuario es obligatorio.',
            'attend_how.required' => 'Se debe definir como lo atendio.',
            'progress.required' => 'El pregreso en el curso es obligatorio.',
            'qualification.required' => 'La calificación es obligatoria.',
            'hours.required' => 'Las horas en el curso son obligatorias.',
            'status.required' => 'El estado es obligatorio.',
            'objective_id.required' => 'Se debe definir objetivo.'
        ];

        // VALIDACIONES
        $validator = Validator::make($input, $rules, $messages);

        if ($validator->fails()) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];

            return response()->json($response, 200);
        }

        // VALIDAR LA EXISTENCIA DEL CURSO
        $courses = DB::table('courses AS c')
            ->select('c.id')
            ->where('c.code', $input['course_code'])
            ->get();

        if ($courses->count() == 0) {

            $response = [
                'success' => false,
                'data' => null,
                'message' => 'El curso con código: ' . $input['course_code'] . ', no existe.'
            ];

            return response()->json($response, 200);
        }

        // VALIDAR LA EXISTENCIA DEL USUARIO
        $users = DB::table('users AS u')
            ->select('u.id')
            ->where('u.email', $input['user_email'])
            ->get();

        if ($users->count() == 0) {

            $response = [
                'success' => false,
                'data' => null,
                'message' => 'El usuario con email: ' . $input['user_email'] . ', no existe.'
            ];

            return response()->json($response, 200);
        }

        // VALIDAR LA EXISTENCIA DEL OBJETIVO
        $objectives = DB::table('objectives AS o')
            ->select('o.id')
            ->where('o.id', $input['objective_id'])
            ->get();

        if ($objectives->count() == 0) {

            $response = [
                'success' => false,
                'data' => null,
                'message' => 'El objetivo con código: ' . $input['objective_id'] . ', no existe.'
            ];

            return response()->json($response, 200);
        }

        $userCourse = new UserCourse;

        // VALIDAR EL USUARIO EN EL CURSO
        $userCourses = DB::table('user_courses AS c')
            ->select('c.*')
            ->where('c.course_id', $courses[0]->id)
            ->where('c.user_id', $users[0]->id)
            ->get();

        if ($userCourses->count() > 0) {
            $userCourse = UserCourse::find($userCourses[0]->id);
        }

        $userCourse->course_id = $courses[0]->id;
        $userCourse->user_id = $users[0]->id;
        $userCourse->attend_how = $input['attend_how'];
        $userCourse->progress = $input['progress'];
        $userCourse->qualification = $input['qualification'];
        $userCourse->hours = $input['hours'];
        $userCourse->status = $input['status'];
        $userCourse->objective_id = $objectives[0]->id;
        // GUARDAR
        $userCourse->save();

        $response = [
            'success' => true,
            'data' => $userCourse,
            'message' => 'Usuario ' . $input['user_email'] . ', en curso ' . $input['course_code'] . ', importado correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified Courses in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Course $course)
    {
        $input = $request->all();

        $rules = [
            // 'code' => 'required',
            // 'name' => 'required',
            'hours' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'provider_id' => 'required',
            // 'training_request_id' => 'required',
        ];

        $messages = [
            // 'code.required' => 'El código es obligatorio.',
            // 'name.required' => 'El nombre es obligatorio.',
            'hours.required' => 'Las horas son obligatorias.',
            'start_date.required' => 'La fecha inicio es obligatoria.',
            'end_date.required' => 'La fecha fin es obligatoria.',
            'provider_id.required' => 'El proveedor es obligatorio.',
            // 'training_request_id.required' => 'La solicitud de entrenamiento es obligatoria.'
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

        if (isset($input['code'])) $course->code = $input['code'];
        if (isset($input['name'])) $course->name = $input['name'];
        $course->shortname = $input['shortname'];
        $course->category = $input['category'];
        $course->hours = $input['hours'];
        $course->start_date = $input['start_date'];
        $course->end_date = $input['end_date'];
        $course->provider_id = $input['provider_id'];
        if (isset($input['training_request_id'])) $course->training_request_id = $input['training_request_id'];
        $course->status_id = $input['status_id'];
        $course->required = $input['required'];
        $course->specialty_id = $input['specialty_id'];
        // GUARDAR
        $course->save();

        $response = [
            'success' => true,
            'data' => $course,
            'message' => 'Curso actualizado correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified Courses from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        try {
            $course->delete();
            $data = null;

            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Curso eliminado correctamente.'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {

            if ($e->errorInfo[1] === 1451) { // Códifo de llave foranea
                $response = [
                    'success' => false,
                    'data' => 'Exception Error.',
                    'message' => 'No se puede eliminar, el curso esta relacionado con otros registros.'
                ];
            } else {
                $response = [
                    'success' => false,
                    'data' => 'Exception Error.',
                    'message' => $e->getCode()
                ];
            }
            return response()->json($response, 400);
        }
    }
}
