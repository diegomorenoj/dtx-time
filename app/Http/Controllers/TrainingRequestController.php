<?php

namespace App\Http\Controllers;

use Mail;
use JWTAuth;
use Validator;
use Illuminate\Http\Request;
use App\Models\TrainingRequest;
use App\Models\TrainingRequestUser;
use App\Models\Course;
use App\Models\UserCourse;
use App\Models\User;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Models\TrainingRequestsLog;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\File as Fil;

class TrainingRequestController extends Controller
{
    protected $table;
    protected $user;
    protected $feeLimit;
    protected $payment;
    protected $free;
    protected $cityMx;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $this->feeLimit = 50000;
        $this->payment = 'P';
        $this->free = 'G';
        $this->cityMx = 'Mexico';
        $this->table = 'training_requests';
    }

    /**
     * Display a listing of the TrainingRequests.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('training_requests AS t')
            ->join('users AS u', 'u.id', '=', 't.user_id')
            ->join('parameters AS p', 'p.id', '=', 't.status_id')
            ->select(
                't.*'
                , 'u.lastname'
                , 'u.area'
                , 'u.city'
                , 'u.position'
                , 'p.name AS status_name'
                , DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
                , DB::raw('null AS users')
            )
            ->where('t.deleted', 'N')
            ->orderBy('t.created_at','desc')
            ->get();

        foreach ($data as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS
            // --------------------------------------------------------------------------------------
            $users = DB::table('training_request_users AS t')
                ->join('users AS u', 'u.id', '=', 't.user_id')
                ->select('u.*')
                ->where('t.training_request_id', $item->id)
                ->get();   
            
            $item->users = $users;
        }
        

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Training Requests Successfully'
        ];
        
        return response()->json($response, 200);
    }

    public function getByUser($user_id)
    {
        $data = DB::table('training_requests AS t')
            ->join('users AS u', 'u.id', '=', 't.user_id')
            ->join('parameters AS p', 'p.id', '=', 't.status_id')
            ->select(
                't.*'
                , 'u.lastname'
                , 'u.area'
                , 'u.city'
                , 'u.position'
                , 'p.name AS status_name'
                , DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
                , DB::raw('null AS users'),
            )
            ->where('t.deleted', 'N')
            ->whereRaw('(t.user_id LIKE "'.$user_id.'" OR COALESCE(t.create_user_id, "") LIKE UPPER("'.$create_user_id.'") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "'.$user_id.'"))')
            ->orderBy('t.created_at','desc')
            ->get();
        
        foreach ($data as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS
            // --------------------------------------------------------------------------------------
            $users = DB::table('training_request_users AS t')
                ->join('users AS u', 'u.id', '=', 't.user_id')
                ->select('u.*')
                ->where('t.training_request_id', $item->id)
                ->get();   
            
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
     * Get list TrainingRequests in storage, by Filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByFilter(Request $request)
    {
        try {

            $input = $request->all();

            $range = $input['range'] === null ? '%%' : $input['range'];
            $user_name = $input['user_name'] === null ? '%%' : '%' . $input['user_name'] . '%';
            $name = $input['name'] === null ? '%%' : '%' . $input['name'] . '%';
            $group = $input['group'] === null ? '%%' : $input['group'];
            $area = $input['area'] === null ? '%%' : $input['area'];
            $position = $input['position'] === null ? '%%' : $input['position'];
            $status_id = $input['status_id'] === null ? '%%' : $input['status_id'];
            $type = '%%';
            $create_user_id = '%%';

            // VALIDAR EL USUARIO
            if($this->user->rol_id === 2 || $this->user->rol_id === 5)
            {
                $user_id = $this->user->id; // SOLO LO DE CADA UNO
                $create_user_id = $this->user->id;
            } else { // AL 1: ADMIN Y AL ROL 3: CAPACITACIONES SE LE CARGAN TODAS
                if($input['user_id'] == null || $this->user->rol_id === 1 || $this->user->rol_id === 3) {
                    $user_id = '%%';
                } else {
                    $user_id = $input['user_id'];
                }
            }
            
            // VALIDAR LA CIUDAD
            if($this->user->rol_id == 4)// PARA EL ROL 4: Encargado de oficina, Cargar solo lo de su ciudad
            {
                $city = $this->user->city;
            }
            else $city = $input['city'] === null ? '%%' : $input['city'];
            
            // El rol 8: Finanzas Debe poder ver las type => P y las de el
            if ($this->user->rol_id === 8) {
                $user_id = '%%'; // SOLO LO DE EL
                $type = 'P';
            }

            // El rol 9: Secretaria
            if ($this->user->rol_id === 9) {
                $user_id = $this->user->id; // SOLO LO DE EL
                $create_user_id = $this->user->id;
            }

            // El rol 7: Socio Director Debe poder ver las type => P y las que que superen los 50
            if($this->user->rol_id === 7)
            {
                $user_id = '%%';
                $type = 'P';
                $price = 50000;

                if($input['range'] !== null)
                {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*'
                            , 'u.lastname'
                            , 'u.area'
                            , 'u.city'
                            , 'u.position'
                            , 'p.name AS status_name'
                            , DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
                            , DB::raw('null AS users')
                        )
                        ->whereRaw("DATE_FORMAT(t.created_at,'%Y-%m-%d') BETWEEN '".$input['range'][0]."' AND '".$input['range'][1]."'")
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "'.$user_id.'" OR COALESCE(t.create_user_id, "") LIKE UPPER("'.$create_user_id.'") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "'.$user_id.'"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("'.$user_name.'") OR UPPER(u.lastname) LIKE UPPER("'.$user_name.'"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("'.$name.'") OR UPPER(t.shortname) LIKE UPPER("'.$name.'"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("'.$area.'")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("'.$group.'")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("'.$position.'")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("'.$city.'")')
                        ->whereRaw('t.status_id LIKE "'.$status_id.'"')
                        ->whereRaw('t.type LIKE "'.$type.'"') // para ver las pagas
                        ->whereRaw('t.fee > '.$price) // para ver las pagas mayores a 50000
                        ->orderBy('t.created_at','desc')
                        ->get();
                }
                else
                {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*'
                            , 'u.lastname'
                            , 'u.area'
                            , 'u.city'
                            , 'u.position'
                            , 'p.name AS status_name'
                            , DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
                            , DB::raw('null AS users')
                        )
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "'.$user_id.'" OR COALESCE(t.create_user_id, "") LIKE UPPER("'.$create_user_id.'") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "'.$user_id.'"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("'.$user_name.'") OR UPPER(u.lastname) LIKE UPPER("'.$user_name.'"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("'.$name.'") OR UPPER(t.shortname) LIKE UPPER("'.$name.'"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("'.$area.'")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("'.$group.'")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("'.$position.'")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("'.$city.'")')
                        ->whereRaw('t.status_id LIKE "'.$status_id.'"')
                        ->whereRaw('t.type LIKE "'.$type.'"') // para ver las pagas
                        ->whereRaw('t.fee > '.$price) // para ver las pagas mayores a 50000
                        ->orderBy('t.created_at','desc')
                        ->get();
                }
            } else {
                if($input['range'] !== null) {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*'
                            , 'u.lastname'
                            , 'u.area'
                            , 'u.city'
                            , 'u.position'
                            , 'p.name AS status_name'
                            , DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
                            , DB::raw('null AS users')
                        )
                        ->whereRaw("DATE_FORMAT(t.created_at,'%Y-%m-%d') BETWEEN '".$input['range'][0]."' AND '".$input['range'][1]."'")
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "'.$user_id.'" OR COALESCE(t.create_user_id, "") LIKE UPPER("'.$create_user_id.'") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "'.$user_id.'"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("'.$user_name.'") OR UPPER(u.lastname) LIKE UPPER("'.$user_name.'"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("'.$name.'") OR UPPER(t.shortname) LIKE UPPER("'.$name.'"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("'.$area.'")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("'.$group.'")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("'.$position.'")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("'.$city.'")')
                        ->whereRaw('t.status_id LIKE "'.$status_id.'"')
                        ->whereRaw('t.type LIKE "'.$type.'"') // para ver las pagas
                        ->orderBy('t.created_at','desc')
                        ->get();
                } else {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*'
                            , 'u.lastname'
                            , 'u.area'
                            , 'u.city'
                            , 'u.position'
                            , 'p.name AS status_name'
                            , DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
                            , DB::raw('null AS users')
                        )
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "'.$user_id.'" OR COALESCE(t.create_user_id, "") LIKE UPPER("'.$create_user_id.'") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "'.$user_id.'"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("'.$user_name.'") OR UPPER(u.lastname) LIKE UPPER("'.$user_name.'"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("'.$name.'") OR UPPER(t.shortname) LIKE UPPER("'.$name.'"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("'.$area.'")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("'.$group.'")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("'.$position.'")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("'.$city.'")')
                        ->whereRaw('t.status_id LIKE "'.$status_id.'"')
                        ->whereRaw('t.type LIKE "'.$type.'"') // para ver las pagas
                        ->orderBy('t.created_at','desc')
                        ->get();
                }
            }

            $training_to_approved = 0;
            $budget_spent = 0;
            $in = null;
            
            foreach ($trainings as $item) {
                // --------------------------------------------------------------------------------------
                // USUARIOS ASOCIADOS
                // --------------------------------------------------------------------------------------
                $users = DB::table('training_request_users AS t')
                    ->join('users AS u', 'u.id', '=', 't.user_id')
                    ->select('u.*')
                    ->where('t.training_request_id', $item->id)
                    ->get();
                
                $item->users = $users;

                // CONTAR LOS PENDIENTES
                if ($item->status_id == 1) $training_to_approved++;

                // SUMAR LOS PRESUPUESTOS GASTADOS
                $budget_spent =  $budget_spent + $item->fee;

                $in = $in . '' . $item->user_id . ',';
            }

            if ($in != null) $in = substr($in, 0, strlen($in) - 1);

            // CONSULTAR EL PRESUPUESTO GASTADO
            if($input['range'] !== null)
            {
                $results = DB::select("
                    SELECT 
                    SUM(b.value) total
                    FROM budgets b 
                    WHERE CONCAT(b.city, b.area) IN
                    (
                        SELECT DISTINCT CONCAT(u.city, u.area) FROM users u
                        WHERE u.id IN(?)
                    )
                    AND b.anio BETWEEN DATE_FORMAT(?,'%Y') AND DATE_FORMAT(?,'%Y')
                ", array($in, $input['range'][0], $input['range'][1]));
            } 
            else
            {
                $results = DB::select("
                    SELECT 
                    SUM(b.value) total
                    FROM budgets b 
                    WHERE CONCAT(b.city, b.area) IN
                    (
                        SELECT DISTINCT CONCAT(u.city, u.area) FROM users u
                        WHERE u.id IN(?)
                    )
                ", array($in));
            }

            // RESUMEN
            $summary = new \stdClass();
            $summary->budget_spent = $budget_spent; // gastado
            $summary->budget_available = $results[0]->total != null ? $results[0]->total - $budget_spent : 0;
            $summary->training_to_approved = $training_to_approved . "";

            $data = new \stdClass();
            $data->summary = $summary;
            $data->data = $trainings;
            $data->in = $in;

            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'List Training Requests Successfully'
            ];
            
            return response()->json($response, 200);
        } catch(\Exception $e){
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al listar las capacitaciones externas.'
            ];
            return response()->json($response, 400);
        }
    }

    public function filter($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('training_requests AS t')
            ->select('t.*')
            ->whereRaw('UPPER(t.name) LIKE UPPER("'.$_val.'") OR UPPER(t.shortname) LIKE UPPER("'.$_val.'")')
            ->orderBy('shortname', 'desc')
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data,
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Get all data single TrainingRequests in storage.
     *
     * @param  Intger  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // --------------------------------------------------------------------------------------
        // CONSULTA PRINCIPAL
        // --------------------------------------------------------------------------------------
        $data = DB::table('training_requests AS t')
            ->join('users AS u', 'u.id', '=', 't.user_id')
            ->join('parameters AS p', 'p.id', '=', 't.status_id')
            ->select(
                't.*',
                'u.lastname', 
                'u.area', 
                'u.city', 
                'u.position', 
                'u.email',
                'p.name AS status_name',
                DB::raw('CASE t.type WHEN "G" THEN "Gratis" WHEN "P" THEN "Pago" END AS type_name'),
                DB::raw('CASE t.methodology WHEN "V" THEN "Virtual" WHEN "P" THEN "Presencial" END AS methodology_name'),
                DB::raw('CASE t.permission WHEN "S" THEN "Si" WHEN "N" THEN "No" END AS permission_name'),
                DB::raw('null AS lstLastTrainings'),
                DB::raw('null AS users'),
                DB::raw('null AS lstTrainingRequestsLogs'),
                DB::raw('null AS lstTrainingRequestsComments'),
                DB::raw('null AS constancy'),
                DB::raw('null AS file')
            )
            ->where('t.id', $id)
            ->first();

        // --------------------------------------------------------------------------------------
        // USUARIOS ASOCIADOS
        // --------------------------------------------------------------------------------------
        $users = DB::table('training_request_users AS t')
            ->join('users AS u', 'u.id', '=', 't.user_id')
            ->select('u.*')
            ->where('t.training_request_id', $data->id)
            ->get();
        
        $data->users = $users;

        // --------------------------------------------------------------------------------------
        // FILE
        // --------------------------------------------------------------------------------------
        $lstFile = FileController::privateGetByTableAndId($this->table, $data->id);
        if($lstFile->count() > 0) $data->file = $lstFile[0];

        // --------------------------------------------------------------------------------------
        // HISTORICO CAPACITACIONES EXTERNAS
        // --------------------------------------------------------------------------------------
        $lstLastTrainings = DB::table('training_requests AS t')
            ->join('users AS u', 'u.id', '=', 't.user_id')
            ->join('parameters AS p', 'p.id', '=', 't.status_id')
            ->select(
                't.shortname',
                'p.name AS status_name',
                DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date')
            )
            ->where('u.id', $data->user_id)
            ->orderBy('t.created_at','desc')
            ->get();

        $data->lstLastTrainings = $lstLastTrainings;

        // --------------------------------------------------------------------------------------
        // LOG
        // --------------------------------------------------------------------------------------
        $lstTrainingRequestsLogs = DB::table('training_requests_logs AS l')
            ->join('users AS u', 'u.id', '=', 'l.user_id')
            ->leftJoin('parameters AS pb', 'pb.id', '=', 'l.before_status_id')
            ->leftJoin('parameters AS pa', 'pa.id', '=', 'l.after_status_id')
            ->select(
                'pb.name AS before_status_name',
                'pa.name AS after_status_name',
                'l.comments',
                'u.lastname',
                DB::raw('DATE_FORMAT(l.created_at,"%b %d de %Y a las %h:%m %p") AS date')
            )
            ->where('l.training_request_id', $data->id)
            ->orderBy('l.created_at','asc')
            ->get();

        $data->lstTrainingRequestsLogs = $lstTrainingRequestsLogs;

        // --------------------------------------------------------------------------------------
        // COMENTARIOS
        // --------------------------------------------------------------------------------------
        $lstTrainingRequestsComments = DB::table('training_requests_comments AS c')
            ->join('users AS u', 'u.id', '=', 'c.user_id')
            ->select(
                'c.id',
                'c.training_request_id',
                'c.comments',
                'u.lastname',
                DB::raw('DATE_FORMAT(c.created_at,"%b %d de %Y a las %h:%m %p") AS date'),
                DB::raw('null AS file'),
            )
            ->where('c.training_request_id', $data->id)
            ->orderBy('c.created_at','desc')
            ->get();

        // --------------------------------------------------------------------------------------
        // FILE
        // --------------------------------------------------------------------------------------
        foreach($lstTrainingRequestsComments as $item) {
            $lstFile = FileController::privateGetByTableAndId('training_requests_comments', $item->id);
            if($lstFile->count() > 0) $item->file = $lstFile[0];
        }

        $data->lstTrainingRequestsComments = $lstTrainingRequestsComments;

        // --------------------------------------------------------------------------------------
        // CONSTANCIA
        // --------------------------------------------------------------------------------------
        $constancy = DB::table('files AS c')
            ->join('constancies AS cn', 'cn.training_request_id', '=', 'c.table_id')
            ->select(
                'cn.id',
                'c.table_id AS training_request_id',
                'c.name',
                'c.created_at',
                'c.updated_at',
                DB::raw('DATE_FORMAT(c.updated_at,"%b %d de %Y") AS date')
            )
            ->where('c.table', 'constancies')
            ->where('c.table_id', $data->id)
            ->first();

        $data->constancy = $constancy;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Training Requests Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Store a newly created TrainingRequests in storage.
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

            $users = null;

            // VALIDAR SI ES GRUPAL O INDIVIDUAL
            $group = $input['group'];

            if($group === 'S')
            {
                if($input['users'] == null)
                {
                    $response = [
                        'success' => false,
                        'data' => null,
                        'message' => 'No ha seleccionado ningun usuario'
                    ];
                    return response()->json($response, 404);
                }
                else $users = $input['users'];
            }
            
            $rules = [
                // 'code' => 'required',
                // 'name' => 'required',
                'hours' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'user_id' => 'required',
                'methodology' => 'required',
            ];

            $messages = [
                // 'code.required' => 'El código es obligatorio.',
                // 'name.required' => 'El nombre es obligatorio.',
                'hours.required' => 'Las horas son obligatorias.',
                'start_date.required' => 'La fecha inicio es obligatoria.',
                'end_date.required' => 'La fecha fin es obligatoria.',
                'user_id.required' => 'El usuario es obligatorio.',
                'methodology.required' => 'La metodología es obligatoria.'
            ];

            // VALORES REQUERIDOS CUANDO ES DE PAGO
            if ($input['type'] == 'P') {
                $rules['fee'] = 'required';
                $messages['fee.required'] = 'El valor solicitado es obligatorio.';
            }

            // VALORES REQUERIDOS PARA CUANDO REQUERE HORARIO
            if ($input['permission'] == 'S') {
                $rules['schedule'] = 'required';
                $messages['schedule.required'] = 'La descripción del horario del permiso solicitado es obligatorio.';
            }

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

            $trainingRequest = new TrainingRequest;
            $trainingRequest->type = $input['type'];
            $trainingRequest->status_id = $input['status_id'];
            if (isset($input['code'])) $trainingRequest->code = $input['code'];
            if (isset($input['name'])) $trainingRequest->name = $input['name'];
            $trainingRequest->shortname = $input['shortname'];
            $trainingRequest->institute = $input['institute'];
            if (isset($input['category'])) $trainingRequest->category = $input['category'];
            $trainingRequest->hours = $input['hours'];
            $trainingRequest->start_date = $input['start_date'];
            $trainingRequest->end_date = $input['end_date'];
            $trainingRequest->permission = $input['permission'];
            if (isset($input['schedule'])) $trainingRequest->schedule = $input['schedule'];
            $trainingRequest->methodology = $input['methodology'];
            if (isset($input['comments'])) $trainingRequest->comments = $input['comments'];
            if (isset($input['fee'])) $trainingRequest->fee = $input['fee'];
            $trainingRequest->group = $group;
            $trainingRequest->user_id = $input['user_id']; // USAURIO INVOLUCRADO EN LA CAPACITACION
            $trainingRequest->create_user_id = $this->user->id; // USUARIO QUE CREA LA CAPACITACIÓN
            $trainingRequest->deleted = 'N';
            if (isset($input['specialty_id'])) $trainingRequest->specialty_id = $input['specialty_id'];
            
            // GUARDAR
            $trainingRequest->save();

            // GUARDAR LA SOLICITUD GRUPAL
            if($users != null)
            {
                // BORRAR LA RELACIÓN
                TrainingRequestUser::where('training_request_id', $trainingRequest->id)->delete();

                // CREAR LA RELACIÓN
                foreach ($users as $user) {

                    $trainingRequestUser = new TrainingRequestUser;
                    $trainingRequestUser->user_id = $user['id'];
                    $trainingRequestUser->training_request_id = $trainingRequest->id;
                    $trainingRequestUser->save();

                    // Datos para el template del reporte
                    /*
                    $data = $this->getUserDataMail($user['id'], 1);

                    // Para el template
                    
                    $mailData["subject"] = $data->subject;
                    $mailData["mail"] = $data->email;
                    $mailData["fecha_actual"] = $data->fecha_actual;
                    $mailData["user_name"] = $data->user_name;
                    $mailData["training_requests_name"] = $trainingRequest->shortname;
                    $mailData["training_requests_start_date"] = $trainingRequest->start_date;
                    $mailData["training_requests_end_date"] = $trainingRequest->end_date;
                    $mailData["training_requests_value"] = $trainingRequest->fee == null ? 0 : $trainingRequest->fee;
                    $mailData["training_requests_methodology"] =
                        $trainingRequest->methodology == 'P' ? 'Presencial': 'Virtual';
                    $mailData["url_training"] = 'http://localhost:8080/config/trainingrequests/';

                    // Enviar el correo
                    Mail::send($data->template, $mailData, function($message)use($mailData) {
                        $message->to($mailData['mail'] )->subject($mailData['subject']);
                    });
                    */
                }
            } else {
                // Datos para el template del reporte
                /*
                $data = $this->getUserDataMail($input['user_id'], 1);

                // Para el template
                $mailData["subject"] = $data->subject;
                $mailData["mail"] = $data->email;
                $mailData["fecha_actual"] = $data->fecha_actual;
                $mailData["user_name"] = $data->user_name;
                $mailData["training_requests_name"] = $trainingRequest->shortname;
                $mailData["training_requests_start_date"] = $trainingRequest->start_date;
                $mailData["training_requests_end_date"] = $trainingRequest->end_date;
                $mailData["training_requests_value"] = $trainingRequest->fee == null ? 0 : $trainingRequest->fee;
                $mailData["training_requests_methodology"] =
                    $trainingRequest->methodology == 'P' ? 'Presencial': 'Virtual';
                $mailData["url_training"] = 'http://localhost:8080/config/trainingrequests/';

                // Enviar el correo
                Mail::send($data->template, $mailData, function($message)use($mailData) {
                    $message->to($mailData['mail'] )->subject($mailData['subject']);
                }); */
            }

            // LOG
            $trainingRequestsLog = new TrainingRequestsLog;
            $trainingRequestsLog->training_request_id = $trainingRequest->id;
            $trainingRequestsLog->user_id = $this->user->id;
            $trainingRequestsLog->comments = "Se creó";
            $trainingRequestsLog->save();

            // GUARDAR EL ARCHIVO
            if($file){
                $table = $this->table;
                $table_id = $trainingRequest->id;
                $data = null;
               
                // GUARDAR EL DOCUMENTO
                FileController::privateStore($file, $table, $table_id, $data);
            }

            DB::commit();

            $response = [
                'success' => true,
                'data' => $trainingRequest,
                'message' => 'Capacitación externa creada correctamente.'
            ];

            return response()->json($response, 200);

        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al crear la capacitación externa.'
            ];
            return response()->json($response, 400);
        }
    }

    /**
     * Update the specified TrainingRequests in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Integer $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();
            $input = json_decode($data['data'], true); // CONVERTIR A JSON LOS DATOS DEL FORMULARIO
            $file = $request->file('file'); // OBTENER EL DOCUMENTO

            $users = null;

            // VALIDAR SI ES GRUPAL O INDIVIDUAL
            $group = $input['group'];

            if($group === 'S')
            {
                if(count($input['users']) == 0)
                {
                    $response = [
                        'success' => false,
                        'data' => null,
                        'message' => 'No ha seleccionado ningun usuario'
                    ];
                    return response()->json($response, 404);
                }
                else $users = $input['users'];
            }

            $trainingRequest = TrainingRequest::find($id);

            $rules = [
                'hours' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'user_id' => 'required',
            ];

            $messages = [
                'hours.required' => 'Las horas son obligatorias.',
                'start_date.required' => 'La fecha inicio es obligatoria.',
                'end_date.required' => 'La fecha fin es obligatoria.',
                'user_id.required' => 'El usuario es obligatorio.',
            ];

            // VALORES REQUERIDOS CUANDO ES DE PAGO
            if ($input['type'] == 'P') {
                $rules['fee'] = 'required';
                $messages['fee.required'] = 'El valor solicitado es obligatorio.';
            }

            // VALORES REQUERIDOS PARA CUANDO REQUERE HORARIO
            if ($input['permission'] == 'S') {
                $rules['schedule'] = 'required';
                $messages['schedule.required'] = 'La descripción del horario del permiso solicitado es obligatorio.';
            }

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

            $trainingRequest->code = $input['code'];
            $trainingRequest->name = $input['name'];
            $trainingRequest->shortname = $input['shortname'];
            $trainingRequest->institute = $input['institute'];
            if (isset($input['category'])) $trainingRequest->category = $input['category'];
            $trainingRequest->hours = $input['hours'];
            $trainingRequest->start_date = $input['start_date'];
            $trainingRequest->end_date = $input['end_date'];
            $trainingRequest->permission = $input['permission'];
            $trainingRequest->schedule = $input['schedule'];
            $trainingRequest->methodology = $input['methodology'];
            $trainingRequest->comments = $input['comments'];
            $trainingRequest->fee = $input['fee'];
            $trainingRequest->group = $group;
            $trainingRequest->user_id = $input['user_id'];
            $trainingRequest->specialty_id = $input['specialty_id'];

            // EDITAR
            $trainingRequest->save();

            // GUARDAR LA SOLICITUD GRUPAL
            if($users != null)
            {
                // BORRAR LA RELACIÓN
                TrainingRequestUser::where('training_request_id', $id)->delete();
                
                // CREAR LA RELACIÓN
                foreach ($users as $user) {
                    $trainingRequestUser = new TrainingRequestUser;
                    $trainingRequestUser->user_id = $user['id'];
                    $trainingRequestUser->training_request_id = $trainingRequest->id;
                    $trainingRequestUser->save();
                }

            }

            // LOG
            $trainingRequestsLog = new TrainingRequestsLog;
            $trainingRequestsLog->user_id = $this->user->id;
            $trainingRequestsLog->training_request_id = $trainingRequest->id;
            $trainingRequestsLog->comments = "Se editó";
            $trainingRequestsLog->save();

            // GUARDAR EL ARCHIVO
            if($file){

                // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
                $lstFiles = FileController::privateGetByTableAndId($this->table, $trainingRequest->id);
                if($lstFiles->count() > 0) {
                    foreach($lstFiles as $item) {
                        $item->delete();
                        Fil::delete($item->path);
                    }
                }

                $table = $this->table;
                $table_id = $trainingRequest->id;
                $data = null;
               
                // GUARDAR EL DOCUMENTO
                FileController::privateStore($file, $table, $table_id, $data);
            }

            DB::commit();

            $response = [
                'success' => true,
                'data' => $trainingRequest,
                'message' => 'Capacitación externa actualizada correctamente.'
            ];

            return response()->json($response, 200);
        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al actualizar la capacitación externa.'
            ];
            return response()->json($response, 404);
        }
    }

    /**
     * Remove the specified TrainingRequests from storage.
     *
     * @param  Integer  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $trainingRequest = TrainingRequest::find($id);
        $trainingRequest->deleted = 'S';
        $trainingRequest->save();
        $data = $trainingRequest;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Capacitación externa eliminada correctamente.'
        ];

        return response()->json($response, 200);
    }

    public function changeStatus(Request $request, $id)
    {
        try {
            $input = $request->all();

            $trainingRequest = TrainingRequest::find($id);
            $statusId = $input['status_id'];

            $tr = TrainingRequestsLog::where('training_request_id', $id)->where('before_status_id', $statusId)->get();
            
            if($tr->count() > 0) {
                $response = [
                    'success' => false,
                    'data' => null,
                    'message' => 'La capacitación externa no puede regresar a un estado anterior'
                ];
                return response()->json($response, 404);
            }

            // ESTADO ANTERIOR
            $oldStatus = Parameter::find($trainingRequest->status_id);

            // ESTADO NUEVO
            $newStatus = Parameter::find($statusId);

            $trainingRequestsLog = new TrainingRequestsLog;
            $trainingRequestsLog->before_status_id = $trainingRequest->status_id;
            $trainingRequestsLog->training_request_id = $trainingRequest->id;

            DB::beginTransaction();
            $trainingRequest->status_id = $input['status_id'];
            $trainingRequest->save();

            // LOG
            $trainingRequestsLog->user_id = $this->user->id;
            $trainingRequestsLog->after_status_id = $input['status_id'];
            $trainingRequestsLog->comments = "Se cambia el estado";
            $trainingRequestsLog->save();

            // CUANDO SE PONE EN ESTADO APROBADA SE DEBE CREAR EL CURSO EN ESTADO EN CURSO => // 5: Aprobada
            // SE SOLICITA ESTE AJUSTE EN LA TAREA => https://tintobox.atlassian.net/browse/GTMX-180
            // 2: Autorizada para pago
            // 4: Autorizada
            if($statusId == 2 || $statusId == 4)
            {
                $course = new Course;
                $course->code = $trainingRequest->code;
                $course->name = $trainingRequest->name;
                $course->shortname = $trainingRequest->shortname;
                $course->category = $trainingRequest->category;
                $course->hours = $trainingRequest->hours;
                $course->start_date = $trainingRequest->start_date;
                $course->end_date = $trainingRequest->end_date;
                $course->provider_id = 4; // 4: Capacitación externa
                $course->training_request_id = $trainingRequest->id;
                $course->category = 'Ninguna';
                $course->status_id = 12; // 12: En curso
                $course->save();

                // CÓDIGO
                // DTX-anio-id
                $code = 'DTX-' .date("Y") . '-' . $course->id;

                $course->code = $code;
                $course->save();

                // ASOCAIR LOS USUARIOS AL CURSO
                
                // Tomar los usuarios asociados a la capacitación
                $users = TrainingRequestUser::where('training_request_id', $id)->get();
                
                // CREAR LA RELACIÓN
                foreach ($users as $user) {
                    $userCourse = new UserCourse;
                    $userCourse->course_id = $course->id;
                    $userCourse->user_id =  $user->user_id;
                    $userCourse->attend_how = 'S';
                    $userCourse->progress = 0;
                    $userCourse->qualification = 0;
                    $userCourse->hours = 0;
                    $userCourse->status = 'A';
                    $userCourse->objective_id = null;
                    // GUARDAR
                    $userCourse->save();
                }

                // Tomar el usuario principal de la capacitación
                $userCourse = new UserCourse;
                $userCourse->course_id = $course->id;
                $userCourse->user_id =  $trainingRequest->user_id;
                $userCourse->attend_how = 'S';
                $userCourse->progress = 0;
                $userCourse->qualification = 0;
                $userCourse->hours = 0;
                $userCourse->status = 'A';
                $userCourse->objective_id = null;
                // GUARDAR
                $userCourse->save();
            }

            DB::commit();

            // ***************************************************************************************
            // INICIO NOTIFICACIONES DE CAMBIO DE ESTADO DE UNA CAPACITACIÓN EXTERNA
            // ***************************************************************************************
            $this->statusNotifyMail($trainingRequest, $oldStatus, $newStatus);
            // ***************************************************************************************
            // FIN NOTIFICACIONES DE CAMBIO DE ESTADO DE UNA CAPACITACIÓN EXTERNA
            // ***************************************************************************************

            $response = [
                'success' => true,
                'data' => $trainingRequest,
                'message' => 'Actualización de estado realizada con éxito.'
            ];
    
            return response()->json($response, 200);

        } catch(\Exception $e){
            DB::rollback();
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => 'Lo sentimos, hubo un error, por favor intente nuevamente: ' . $e->getMessage()
            ];
            return response()->json($response, 400);
        }

    }

    private function statusNotifyMail($trainingRequest, $oldStatus, $newStatus)
    {
        $usersId = array();
        $inRoles = array();
        $userOwner = User::find($trainingRequest->user_id);

        // Notificación a los usuarios relacinados con la capacitación
        if($trainingRequest->group == 'S') { // Grupal

            // Tomar los usuarios asociados a la capacitación
            $users = TrainingRequestUser::where('training_request_id', $id)->get();
                
            // LLENAR LOS ID DE USAURIOS
            foreach ($users as $user) {
                $usersId[] = $user->user_id;
            }

        } else {
            $usersId[] = $trainingRequest->user_id;
            // CREADOR DE LA CAPACITACIÓN
            if($trainingRequest->user_id != $trainingRequest->create_user_id)
                $usersId[] = $trainingRequest->create_user_id;
        }

        // 1: Administrador
        // 2: Usuario general
        // 3: Capacitaciones
        // 4: Encargado de oficina
        // 5: Socio
        // 6: Socio de capacitaciones
        // 7: Socio Director
        // 8: Finanzas
        // 9: Secretaria
        // 10: Gerente

        // CONSULTAR EL USAURIO POR ROL Y CIUDAD
        switch ($newStatus->id) {
            case 1: // 1: Por autorizar
                if (str_contains($userOwner->city, $this->cityMx)) {
                    // En el caso de personas de Mexico y
                    // si la capacitación es menor a 50,000.00
                    if ($trainingRequest->type == $this->payment && $trainingRequest->fee < $this->fee_limit) {
                        // notificación al socio del área y capacitaciones
                        // 5: Socio
                        $inRoles[] = 5;
                        // 6: Socio de capacitaciones
                        $inRoles[] = 6;
                    } elseif($trainingRequest->type == $this->payment && $trainingRequest->fee >= $this->fee_limit) { // La capacitación es igual o mayor a 50,000.00
                        // notificación al Socio del área, Socio Director, Socio de capacitaciones y Capacitaciones
                        // 5: Socio
                        $inRoles[] = 5;
                        // 6: Socio de capacitaciones
                        $inRoles[] = 6;
                        // 7: Socio Director
                        $inRoles[] = 7;
                        // 3: Capacitaciones
                        $inRoles[] = 3;
                    }
                } else { // En el caso de personas de oficinas diferentes a Mexico (foráneos)
                    // Si la capacitación es menor a 50,000.00
                    if ($trainingRequest->type == $this->payment && $trainingRequest->fee < $this->fee_limit) {
                        // notificación al encargado de la oficina y capacitaciones
                        // 4: Encargado de oficina
                        $inRoles[] = 4;
                        // 6: Socio de capacitaciones
                        $inRoles[] = 6;
                    } elseif($trainingRequest->type == $this->payment && $trainingRequest->fee >= $this->fee_limit) { // La capacitación es igual o mayor a 50,000.00
                        // notificación al Socio Director, Encargado de la oficina, Socio de capacitaciones y Capacitaciones
                        // 7: Socio Director
                        $inRoles[] = 7;
                        // 4: Encargado de oficina
                        $inRoles[] = 4;
                        // 6: Socio de capacitaciones
                        $inRoles[] = 6;
                        // 3: Capacitaciones
                        $inRoles[] = 3;
                    }
                }
                
                break;
            case 2: // 2: Autorizada para pago
                if(str_contains($userOwner->city, $this->cityMx)) {
                    // Notificar Socio de la división
                    // 5: Socio
                    $inRoles[] = 5;
                } else {
                    // Notificar encargado de oficina
                    // 4: Encargado de oficina
                    $inRoles[] = 4;
                }

                // Notificar Rol de finanzas
                // Notificar Rol Socio de capacitaciones
                // Notificar Rol Capacitaciones
                 // 6: Socio de capacitaciones
                 $inRoles[] = 6;
                 // 3: Capacitaciones
                 $inRoles[] = 3;
                 // 8: Finanzas
                $inRoles[] = 8;
                break;
            case 3: // 3: Pagada
                if (str_contains($userOwner->city, $this->cityMx)) {
                    // En el caso de personas de Mexico y
                    // si la capacitación es menor a 50,000.00
                    if($trainingRequest->type == $this->payment && $trainingRequest->fee >= $this->fee_limit) { // La capacitación es igual o mayor a 50,000.00
                        // notificación al Socio del área, Socio Director, Socio de capacitaciones y Capacitaciones
                        // 5: Socio
                        $inRoles[] = 5;
                        // 6: Socio de capacitaciones
                        $inRoles[] = 6;
                        // 7: Socio Director
                        $inRoles[] = 7;
                        // 3: Capacitaciones
                        $inRoles[] = 3;
                    }

                    // Notificar Socio de la división => 5

                } else { // En el caso de personas de oficinas diferentes a Mexico (foráneos)
                    // Si la capacitación es menor a 50,000.00
                    if($trainingRequest->type == $this->payment && $trainingRequest->fee >= $this->fee_limit) { // La capacitación es igual o mayor a 50,000.00
                        // notificación al Socio Director, Encargado de la oficina, Socio de capacitaciones y Capacitaciones
                        // 7: Socio Director
                        $inRoles[] = 7;
                        // 4: Encargado de oficina
                        $inRoles[] = 4;
                        // 6: Socio de capacitaciones
                        $inRoles[] = 6;
                        // 3: Capacitaciones
                        $inRoles[] = 3;
                    }
                    // Notificar encargado de oficina
                    // 4: Encargado de oficina
                    $inRoles[] = 4;
                }

                // Notificar Rol de finanzas
                // Notificar Rol Socio de capacitaciones
                // Notificar Rol Capacitaciones
                // 6: Socio de capacitaciones
                $inRoles[] = 6;
                // 3: Capacitaciones
                $inRoles[] = 3;
                // 8: Finanzas
                $inRoles[] = 8;
                break;
            case 4: // 4: Autorizada
            case 5: // 5: Preautorizada
            case 7: // 7: Autorizada sin pago
            case 8: // 8: Rechazada
                if(str_contains($userOwner->city, $this->cityMx)) {
                    // Notificar Socio de la división
                    // 5: Socio
                    $inRoles[] = 5;
                } else { // En el caso de personas de oficinas diferentes a Mexico (foráneos)
                    // Notificar encargado de oficina
                    // 4: Encargado de oficina
                    $inRoles[] = 4;
                }

                // Notificar Rol Socio de capacitaciones
                // Notificar Rol Capacitaciones
                // 6: Socio de capacitaciones
                $inRoles[] = 6;
                // 3: Capacitaciones
                $inRoles[] = 3;
                break;
            case 9: // 9: En curso
                if(str_contains($userOwner->city, $this->cityMx)) {
                    // Notificar Socio de la división
                    // 5: Socio
                    $inRoles[] = 5;
                } else { // En el caso de personas de oficinas diferentes a Mexico (foráneos)
                    // Notificar encargado de oficina
                    // 4: Encargado de oficina
                    $inRoles[] = 4;
                }

                // Notificar Rol Capacitaciones
                // 3: Capacitaciones
                $inRoles[] = 3;
                break;
            case 10: // 10: Completada para revisión
            case 11: // 11: Completada
                if(str_contains($userOwner->city, $this->cityMx)) {
                    // Notificar Socio de la división
                    // 5: Socio
                    $inRoles[] = 5;
                } else { // En el caso de personas de oficinas diferentes a Mexico (foráneos)
                    // Notificar encargado de oficina
                    // 4: Encargado de oficina
                    $inRoles[] = 4;
                }

                if($trainingRequest->type == $this->payment && $trainingRequest->fee >= $this->fee_limit) { // La capacitación es igual o mayor a 50,000.00
                    // notificación al Socio Director
                    // 7: Socio Director
                    $inRoles[] = 7;
                }

                // Notificar Rol Socio de capacitaciones
                // Notificar Rol Capacitaciones
                // 6: Socio de capacitaciones
                $inRoles[] = 6;
                // 3: Capacitaciones
                $inRoles[] = 3;
                break;
        }

        $usersRole = $this->getUserByRoleAndCity($inRoles, $userOwner->city);
        foreach ($usersRole as $item) {
            $usersId[] = $item->id;
        }

        // RECORRER TODA LA LISTA DE USUARIOS Y ENVIAR EL EMAIL
        foreach ($usersId as $id) {
            // ENVIO DE CORREO
            $data = $this->getUserDataMail($id, 2);

            // Para el template
            $mailData["subject"] = $data->subject;
            $mailData["mail"] = $data->email;
            $mailData["fecha_actual"] = $data->fecha_actual;
            $mailData["user_name"] = $this->user->name;
            $mailData["training_requests_name"] = $trainingRequest->shortname;
            $mailData["old_status"] = $oldStatus->name;
            $mailData["new_status"] = $newStatus->name;
            $mailData["url_training"] = 'http://localhost:8080/config/trainingrequests/';

            // Enviar el correo
            Mail::send($data->template, $mailData, function($message)use($mailData) {
                $message->to($mailData['mail'] )->subject($mailData['subject']);
            });
        }
    }

    private function getUserDataMail($userId, $type)
    {
        $results = DB::select("
            SELECT
                DATE_FORMAT(now(),'%d de %M de %Y') AS fecha_actual,
                u.lastname user_name,
                u.email,
                null template,
                null subject,
                null title
            FROM
                users as u
            WHERE u.id = ?
            ORDER BY u.id DESC
            LIMIT 1
        ", array($userId));

        $resp = $results[0];

        if($type == 1) {
            $resp->subject = "REGISTRO DE CAPACITACIÓN EXTERNA";
            $resp->template = "emails.trainingrequest";
        } elseif($type == 2) {
            $resp->subject = "ACTUALIZACIÓN DE ESTADOS";
            $resp->template = "emails.trainingrequeststatus";
        } else {
            $resp->subject = "";
            $resp->template = "emails.trainingrequeststatus";
        }
        

        return $resp;
    }

    private function getUserByRoleAndCity($roleIdIn, $city)
    {
        return DB::table('users AS u')
            ->select('u.id')
            ->whereIn('u.rol_id', $roleIdIn)
            ->where('u.city', $city)
            ->get();
    }

}
