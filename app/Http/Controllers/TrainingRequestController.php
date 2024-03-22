<?php

namespace App\Http\Controllers;

use Mail;
use JWTAuth;
use Validator;
use Illuminate\Http\Request;
use App\Models\TrainingRequest;
use App\Models\TrainingRequestUser;
use App\Models\Course;
use App\Models\Budget;
use App\Models\UserCourse;
use App\Models\User;
use App\Models\Parameter;
use Illuminate\Support\Facades\DB;
use App\Models\TrainingRequestsLog;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\File as Fil;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

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
        Log::info('Entra INDEX');
        $data = DB::table('training_requests AS t')
            ->join('users AS u', 'u.id', '=', 't.user_id')
            ->join('parameters AS p', 'p.id', '=', 't.status_id')
            ->select(
                't.*',
                'u.lastname',
                'u.area',
                'u.city',
                'u.position',
                'p.name AS status_name',
                DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date'),
                DB::raw('null AS users')
            )
            ->where('t.deleted', 'N')
            ->orderBy('t.created_at', 'desc')
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
                't.*',
                'u.lastname',
                'u.area',
                'u.city',
                'u.position',
                'p.name AS status_name',
                DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date'),
                DB::raw('null AS users'),
            )
            ->where('t.deleted', 'N')
            ->whereRaw('(t.user_id LIKE "' . $user_id . '" OR COALESCE(t.create_user_id, "") LIKE UPPER("' . $create_user_id . '") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "' . $user_id . '"))')
            ->orderBy('t.created_at', 'desc')
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

            /**
             * 
             * 1	Administrador
             * 2	Usuario general
             * 3	Capacitaciones
             * 4	Encargado de oficina
             * 5	Socio
             * 6	Socio de capacitaciones
             * 7	Socio Director
             * 8	Finanzas
             * 9	Secretaria
             * 10	Gerente

             * 
             */

            // VALIDAR EL USUARIO
            if ($this->user->rol_id === 2) {
                $user_id = $this->user->id; // SOLO LO DE CADA UNO
                $create_user_id = $this->user->id;
            } else { // AL 1: ADMIN Y AL ROL 3: CAPACITACIONES SE LE CARGAN TODAS
                if ($input['user_id'] == null || $this->user->rol_id === 1 || $this->user->rol_id === 3) {
                    $user_id = '%%';
                } else {
                    $user_id = $input['user_id'];
                }
            }

            // VALIDAR LA CIUDAD
            if ($this->user->rol_id == 4 || $this->user->rol_id == 5 || $this->user->rol_id == 10) // PARA EL ROL 4: Encargado de oficina, Cargar solo lo de su ciudad
            {
                $city = $this->user->city;
            } else $city = $input['city'] === null ? '%%' : $input['city'];

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
            if ($this->user->rol_id === 7) {
                $user_id = '%%';
                $type = 'P';
                $price = 50000;

                if ($input['range'] !== null) {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*',
                            'u.lastname',
                            'u.area',
                            'u.city',
                            'u.position',
                            'p.name AS status_name',
                            DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date'),
                            DB::raw('null AS users')
                        )
                        ->whereRaw("DATE_FORMAT(t.created_at,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "' . $user_id . '" OR COALESCE(t.create_user_id, "") LIKE UPPER("' . $create_user_id . '") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "' . $user_id . '"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("' . $user_name . '") OR UPPER(u.lastname) LIKE UPPER("' . $user_name . '"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("' . $name . '") OR UPPER(t.shortname) LIKE UPPER("' . $name . '"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("' . $area . '")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("' . $group . '")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("' . $position . '")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("' . $city . '")')
                        ->whereRaw('t.status_id LIKE "' . $status_id . '"')
                        ->whereRaw('t.type LIKE "' . $type . '"') // para ver las pagas
                        ->whereRaw('t.fee > ' . $price) // para ver las pagas mayores a 50000
                        ->orderBy('t.created_at', 'desc')
                        ->get();
                } else {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*',
                            'u.lastname',
                            'u.area',
                            'u.city',
                            'u.position',
                            'p.name AS status_name',
                            DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date'),
                            DB::raw('null AS users')
                        )
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "' . $user_id . '" OR COALESCE(t.create_user_id, "") LIKE UPPER("' . $create_user_id . '") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "' . $user_id . '"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("' . $user_name . '") OR UPPER(u.lastname) LIKE UPPER("' . $user_name . '"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("' . $name . '") OR UPPER(t.shortname) LIKE UPPER("' . $name . '"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("' . $area . '")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("' . $group . '")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("' . $position . '")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("' . $city . '")')
                        ->whereRaw('t.status_id LIKE "' . $status_id . '"')
                        ->whereRaw('t.type LIKE "' . $type . '"') // para ver las pagas
                        ->whereRaw('t.fee > ' . $price) // para ver las pagas mayores a 50000
                        ->orderBy('t.created_at', 'desc')
                        ->get();
                }
            } else {
                if ($input['range'] !== null) {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*',
                            'u.lastname',
                            'u.area',
                            'u.city',
                            'u.position',
                            'p.name AS status_name',
                            DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date'),
                            DB::raw('null AS users')
                        )
                        ->whereRaw("DATE_FORMAT(t.created_at,'%Y-%m-%d') BETWEEN '" . $input['range'][0] . "' AND '" . $input['range'][1] . "'")
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "' . $user_id . '" OR COALESCE(t.create_user_id, "") LIKE UPPER("' . $create_user_id . '") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "' . $user_id . '"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("' . $user_name . '") OR UPPER(u.lastname) LIKE UPPER("' . $user_name . '"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("' . $name . '") OR UPPER(t.shortname) LIKE UPPER("' . $name . '"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("' . $area . '")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("' . $group . '")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("' . $position . '")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("' . $city . '")')
                        ->whereRaw('t.status_id LIKE "' . $status_id . '"')
                        ->whereRaw('t.type LIKE "' . $type . '"') // para ver las pagas
                        ->orderBy('t.created_at', 'desc')
                        ->get();
                } else {
                    $trainings = DB::table('training_requests AS t')
                        ->join('users AS u', 'u.id', '=', 't.user_id')
                        ->join('parameters AS p', 'p.id', '=', 't.status_id')
                        ->select(
                            't.*',
                            'u.lastname',
                            'u.area',
                            'u.city',
                            'u.position',
                            'p.name AS status_name',
                            DB::raw('DATE_FORMAT(t.created_at,"%b %d de %Y") AS date'),
                            DB::raw('null AS users')
                        )
                        ->where('t.deleted', 'N')
                        ->whereRaw('(t.user_id LIKE "' . $user_id . '" OR COALESCE(t.create_user_id, "") LIKE UPPER("' . $create_user_id . '") OR t.id IN(select tr.training_request_id from training_request_users as tr where tr.user_id LIKE "' . $user_id . '"))')
                        ->whereRaw('(UPPER(u.name) LIKE UPPER("' . $user_name . '") OR UPPER(u.lastname) LIKE UPPER("' . $user_name . '"))')
                        ->whereRaw('(UPPER(t.name) LIKE UPPER("' . $name . '") OR UPPER(t.shortname) LIKE UPPER("' . $name . '"))')
                        ->whereRaw('UPPER(u.area) LIKE UPPER("' . $area . '")')
                        ->whereRaw('UPPER(COALESCE(u.group, "")) LIKE UPPER("' . $group . '")')
                        ->whereRaw('UPPER(u.position) LIKE UPPER("' . $position . '")')
                        ->whereRaw('UPPER(u.city) LIKE UPPER("' . $city . '")')
                        ->whereRaw('t.status_id LIKE "' . $status_id . '"')
                        ->whereRaw('t.type LIKE "' . $type . '"') // para ver las pagas
                        ->orderBy('t.created_at', 'desc')
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
            if ($input['range'] !== null) {
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
            } else {
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
        } catch (\Exception $e) {
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
            ->whereRaw('UPPER(t.name) LIKE UPPER("' . $_val . '") OR UPPER(t.shortname) LIKE UPPER("' . $_val . '")')
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

        log::info('Entra show');
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
        if ($lstFile->count() > 0) $data->file = $lstFile[0];

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
            ->orderBy('t.created_at', 'desc')
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
            ->orderBy('l.created_at', 'asc')
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
            ->orderBy('c.created_at', 'desc')
            ->get();

        // --------------------------------------------------------------------------------------
        // FILE
        // --------------------------------------------------------------------------------------
        foreach ($lstTrainingRequestsComments as $item) {
            $lstFile = FileController::privateGetByTableAndId('training_requests_comments', $item->id);
            if ($lstFile->count() > 0) $item->file = $lstFile[0];
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

            $input = json_decode($data['data'], true);

            // CONVERTIR A JSON LOS DATOS DEL FORMULARIO
            $file = $request->file('file'); // OBTENER EL DOCUMENTO

            $createorUser = User::find($input['user_id']);

            $users = null;

            // VALIDAR SI ES GRUPAL O INDIVIDUAL
            $group = $input['group'];

            if ($group === 'S') {
                if ($input['users'] == null) {
                    $response = [
                        'success' => false,
                        'data' => null,
                        'message' => 'No ha seleccionado ningun usuario'
                    ];
                    return response()->json($response, 404);
                } else $users = $input['users'];
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

            $trainingRequest->name = $input['shortname'];
            $trainingRequest->shortname = $input['shortname'];
            $trainingRequest->institute = $input['institute'];
            $trainingRequest->category = "Capacitación Externa - " . $input['type'];
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

            // Luego de guardar generamos el code y guardamos
            $firstLetterOfType = strtoupper(substr($trainingRequest->type, 0, 1));
            $code = "CE" . $firstLetterOfType . "-" . $trainingRequest->id;
            $trainingRequest->code = $code;
            $trainingRequest->save();


            // GUARDAR LA SOLICITUD GRUPAL
            if ($users != null) {
                // BORRAR LA RELACIÓN
                TrainingRequestUser::where('training_request_id', $trainingRequest->id)->delete();

                // CREAR LA RELACIÓN
                foreach ($users as $user) {

                    $trainingRequestUser = new TrainingRequestUser;
                    $trainingRequestUser->user_id = $user['id'];
                    $trainingRequestUser->training_request_id = $trainingRequest->id;
                    $trainingRequestUser->save();

                    // Datos para el template del reporte
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
                        $trainingRequest->methodology == 'P' ? 'Presencial' : 'Virtual';
                    $mailData["url_training"] = config('app.url') . '/config/trainingrequests/';

                    // Enviar el correo

                    $this->sendEmail($data->template, $mailData);
                }
            } else {
                // Datos para el template del reporte

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
                    $trainingRequest->methodology == 'P' ? 'Presencial' : 'Virtual';
                $mailData["url_training"] = config('app.url') . '/config/trainingrequests/';

                // Enviar el correo
                $this->sendEmail($data->template, $mailData);
            }

            // LOG
            $trainingRequestsLog = new TrainingRequestsLog;
            $trainingRequestsLog->training_request_id = $trainingRequest->id;
            $trainingRequestsLog->user_id = $this->user->id;
            $trainingRequestsLog->comments = "Se creó";
            $trainingRequestsLog->save();

            // GUARDAR EL ARCHIVO
            if ($file) {
                $table = $this->table;
                $table_id = $trainingRequest->id;
                $data = null;

                // GUARDAR EL DOCUMENTO
                FileController::privateStore($file, $table, $table_id, $data);
            }



            $this->notificationSendingRules($createorUser, $trainingRequest);


            DB::commit();

            $response = [
                'success' => true,
                'data' => $trainingRequest,
                'message' => 'Capacitación externa creada correctamente.'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::info($e->getMessage());
            $response = [
                'success' => false,
                'data' => $e->getMessage(),
                'message' => 'Error al crear la capacitación externa.'
            ];
            return response()->json($response, 400);
        }
    }


    /**
     * Se declaran las reglas de envío de notificaciones 
     */
    private function notificationSendingRules(User $user, TrainingRequest $training)
    {

        $creator_name = $user->lastname;
        $authorizedUsers = $this->getAuthorization();

        Log::info("Información con secretaria");
        Log::info($training);

        $authorizedUsersGod = $authorizedUsers->filter(function ($user) {
            return $user->rol_id == 3;
        });

        // Capacitación gratuita / pago (filtra ciudad (Mexico) / area / Rol 5)
        if ($user->city === 'Mexico') {
            $users = $authorizedUsers
                ->when($user->city, function ($query) use ($user) {
                    return $query->where('city', $user->city);
                })
                ->when($user->area, function ($query) use ($user) {
                    return $query->where('area', $user->area);
                })
                ->where('rol_id', 5);
        } else {
            // Capacitación gratuita (filtra ciudad (Foraneo) / area / Rol 4)
            $users = $authorizedUsers
                ->when($user->city, function ($query) use ($user) {
                    return $query->where('city', $user->city);
                })
                ->when($user->area, function ($query) use ($user) {
                    return $query->where('area', $user->area);
                })
                ->where('rol_id', 4);
        }

        $users = $users->concat($authorizedUsersGod);

        if(($training->create_user_id!=$training->user_id)||($training->type=="G")) {
            $creator_user = User::find($training->create_user_id);    
            $users = $users->concat(collect([$creator_user]));
        }        

        Log::info("ciudad ");
        Log::info($users);

        // Creamos el correo de envio: 

        $month = config('constants.meses');
        $now = date('d') . ' de ' . $month[date('m')] . ' de ' . date('Y');

        foreach ($users as $user) {

            $mailData["subject"] = "CAPACITACION EXTERNA - REVISIÓN";
            $mailData["mail"] = $user->email;
            $mailData["fecha_actual"] = $now;
            $mailData["user_name"] = $creator_name;
            $mailData["training_requests_name"] = $training->shortname;
            $mailData["training_requests_start_date"] = $training->start_date;
            $mailData["training_requests_end_date"] = $training->end_date;
            $mailData["training_requests_value"] = $training->fee == null ? 0 : $training->fee;
            $mailData["training_requests_methodology"] =         $training->methodology == 'P' ? 'Presencial' : 'Virtual';
            $mailData["url_training"] = config('app.url') . '/config/trainingrequests/';

            // Enviar el correo
            $this->sendEmail("emails.trainingrequest", $mailData);
        }
    }

    /**
     * Encontrar todos los usuarios involucrados en los mensajes de 
     * Capacitaciones
     */

    private function getAuthorization()
    {
        $roles = [3, 4, 5, 8, 6, 7];
        $users = User::whereIn('rol_id', $roles)->get();

        return $users;
    }


    /**
     * Esta funcion envía el email 
     */
    private function sendEmail($template, $mailData)
    {
        try {
            Mail::send($template, $mailData, function ($message) use ($mailData) {
                $message->to($mailData['mail'])->subject($mailData['subject']);
            });
        } catch (\Exception $e) {
            // Aquí puedes registrar el error si es necesario
            \Log::error('Error al enviar correo: ' . $e->getMessage());
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

            if ($group === 'S') {
                if (count($input['users']) == 0) {
                    $response = [
                        'success' => false,
                        'data' => null,
                        'message' => 'No ha seleccionado ningun usuario'
                    ];
                    return response()->json($response, 404);
                } else $users = $input['users'];
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
            if ($users != null) {
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
            if ($file) {

                // BUSCAR Y ELIMINAR EL DOCUMENTO ANTERIOR
                $lstFiles = FileController::privateGetByTableAndId($this->table, $trainingRequest->id);
                if ($lstFiles->count() > 0) {
                    foreach ($lstFiles as $item) {
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
        } catch (\Exception $e) {
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
            $budgetsCheck = collect();
            $budgetsAnnio = null;
            Log::info('Cambiar de estado');
            Log::info($input);


            $trainingRequest = TrainingRequest::find($id);

            $statusId = $input['status_id'];
            $changeStatusUserId = $input['user_id'];

            // Tomar los usuarios asociados a la capacitación

            $users = TrainingRequestUser::where('training_request_id', $id)->get();
            $usersChange = User::find($changeStatusUserId);
            // Unir userOwner y users en un solo array llamado userTraining


            // Primera validación para statusId 3 
            if ($statusId == 3) {

                Log::info($trainingRequest->start_date);
                $start_date = $trainingRequest->start_date;

                // Extraer el año, el mes y el día de la fecha de inicio.
                $year = substr($start_date, 0, 4);
                $month = substr($start_date, 5, 2);
                $day = substr($start_date, 8, 2);

                // Convertir a enteros para comparar.
                $year = (int) $year;
                $month = (int) $month;
                $day = (int) $day;
                Log::info($year);

                // Comprobar si la fecha es hasta el 31 de Julio incluido.
                if ($month <= 7) {
                    // Si es hasta el 31 de Julio, se usa el año anterior.
                    Log::info("Restar");
                    $year = $year - 1;
                }

                Log::info($year);

                $budgetsAnnio = Budget::where('anio', $year)
                    ->where('is_main', true)
                    ->first();

                Log::info('año ');
                Log::info($budgetsAnnio);

                if ($budgetsAnnio === null) {
                    $response = [
                        'success' => false,
                        'data' => $trainingRequest,
                        'message' => 'El Ciclo no tiene un presupuesto '
                    ];

                    return response()->json($response, 400);
                }

                $userOwner = User::find($trainingRequest->user_id);

                // Obtener los presupuestos como una colección y luego convertirlos en un array
                $budgetsOwner = Budget::where('anio', $year)
                    ->where('city', $userOwner->city)
                    ->where('area', $userOwner->area)
                    ->get();

                $budgetsCheck = $budgetsCheck->merge($budgetsOwner);

                if ($budgetsOwner->isEmpty()) {

                    $response = [
                        'success' => false,
                        'data' => $trainingRequest,
                        'message' => 'La ciudad.' . $userOwner->city . " en área " . $userOwner->area . " No cuenta con presupuesto, contacte con el área de capacitaciones."
                    ];

                    return response()->json($response, 400);
                }

                foreach ($users as $userTraining) {
                    $user = User::find($userTraining->user_id);

                    // Obtener presupuestos y convertirlos directamente en un array
                    $budgets = Budget::where('anio', $year)
                        ->where('city', $user->city)
                        ->where('area', $user->area)
                        ->get();



                    if ($budgets->isEmpty()) {

                        $response = [
                            'success' => false,
                            'data' => $trainingRequest,
                            'message' => 'La ciudad.' . $user->city . " en área " . $user->area . " No cuenta con presupuesto, contacte con el área de capacitaciones."
                        ];

                        return response()->json($response, 400);
                    }

                    $budgetsCheck = $budgetsCheck->merge($budgets);
                }
            }


            $tr = TrainingRequestsLog::where('training_request_id', $id)->where('before_status_id', $statusId)->get();

            if ($tr->count() > 0) {
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

            Log::info("Estado");
            Log::info($statusId);

            if ($statusId == 2 || $statusId == 4) {


                $course = new Course;
                $course->code = $trainingRequest->code;
                $course->name = $trainingRequest->name;
                $course->shortname = $trainingRequest->shortname;
                $course->category = $trainingRequest->category;
                $course->hours = $trainingRequest->hours;
                $course->start_date = $trainingRequest->start_date;
                $course->required = "S";
                $course->end_date = $trainingRequest->end_date;
                $course->provider_id = 4; // 4: Capacitación externa
                $course->training_request_id = $trainingRequest->id;
                $course->category = 'Capacitación Exterma';
                $course->status_id = 12; // 12: En curso
                $course->save();

                // CÓDIGO
                // DTX-anio-id
                $code = 'DTX-' . date("Y") . '-' . $course->id;

                $course->code = $code;
                $course->save();

                // ASOCAIR LOS USUARIOS AL CURSO

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
            } else if ($statusId == 11) {

                Log::info("entro 11");

                $course = Course::where('training_request_id', $trainingRequest->id)->first();
                Log::info($course);
                if ($course) {
                    // 2. Cambiar el estado del curso
                    $course->status_id = 13;
                    $course->save();

                    // 3. Encontrar y actualizar los UserCourse
                    $userCourses = UserCourse::where('course_id', $course->id)->get();
                    Log::info($userCourses);

                    foreach ($userCourses as $userCourse) {
                        // Aquí actualizas las propiedades que necesitas
                        $userCourse->hours = $course->hours;
                        $userCourse->qualification = '10';
                        $userCourse->progress = '100';

                        // Guardar los cambios
                        $userCourse->save();
                    }
                }
            }

            /**
             * se modifica la distribución
             */

            if ($statusId == 3) {
                // Convertir $budgetsCheck a colección para manipulación

                Budget::where('id', $budgetsAnnio->id)
                    ->update([
                        'spent' => DB::raw("IF(spent IS NULL, $trainingRequest->fee, spent + $trainingRequest->fee)")
                    ]);

                // Dividir fee por el tamaño de $budgetsCheck
                $amountToAdd = $trainingRequest->fee / $budgetsCheck->count();


                // Iterar sobre $budgetsCheck y actualizar cada presupuesto
                foreach ($budgetsCheck as $budget) {
                    // Actualizamos el presupuesto por Año / Area / Ciudad

                    Budget::where('id', $budget->id)
                        ->update([
                            'spent' => DB::raw("IF(spent IS NULL, $amountToAdd, spent + $amountToAdd)")
                        ]);
                }
            }


            DB::commit();

            // ***************************************************************************************
            // INICIO NOTIFICACIONES DE CAMBIO DE ESTADO DE UNA CAPACITACIÓN EXTERNA
            // ***************************************************************************************


            $this->statusNotifyMail($trainingRequest, $oldStatus, $newStatus, $usersChange);
            // ***************************************************************************************
            // FIN NOTIFICACIONES DE CAMBIO DE ESTADO DE UNA CAPACITACIÓN EXTERNA
            // ***************************************************************************************

            $response = [
                'success' => true,
                'data' => $trainingRequest,
                'message' => 'Actualización de estado realizada con éxito.'
            ];

            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollback();
            Log::alert($e->getMessage());
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => 'Lo sentimos, hubo un error, por favor intente nuevamente: '
            ];
            return response()->json($response, 400);
        }
    }

    private function getUserNotify($userOwner, $finanzas, $scapacitaciones, $director)
    {
        // Obtener usuarios autorizados
        $authorizedUsers = $this->getAuthorization();

        // Inicializar la colección de usuarios notificados
        $notifiedUsers = collect();

        // Determinar el rol basado en la ciudad del usuario propietario
        $officeRoleId = str_contains($userOwner->city, $this->cityMx) ? 5 : 4;

        // Filtrar por director de oficina si es necesario
        $notifiedUsers = $notifiedUsers->concat($authorizedUsers->filter(function ($authorizedUser) use ($userOwner, $officeRoleId) {
            return ($userOwner->city ? $authorizedUser->city == $userOwner->city : true) &&
                ($userOwner->area ? $authorizedUser->area == $userOwner->area : true) &&
                in_array($authorizedUser->rol_id, [$officeRoleId]);
        }));

        // Filtrar por Capacitaciones si es necesario
        $notifiedUsers = $notifiedUsers->concat($authorizedUsers->where('rol_id', 3));

        // Filtrar por Finanzas para autorizar pago
        if ($finanzas) {
            $notifiedUsers = $notifiedUsers->concat($authorizedUsers->where('rol_id', 8));
        }

        // Filtrar por Socio de Capacitaciones si es necesario
        if ($scapacitaciones) {
            $notifiedUsers = $notifiedUsers->concat($authorizedUsers->where('rol_id', 6));
        }

        // Filtrar por Socio Director si es necesario
        if ($director) {
            $notifiedUsers = $notifiedUsers->concat($authorizedUsers->where('rol_id', 7));
        }

        // Eliminar duplicados y devolver la colección de usuarios notificados
        return $notifiedUsers->unique('id');
    }


    private function statusNotifyMail($trainingRequest, $oldStatus, $newStatus, $usersChange)
    {
        try {

            $usersId = array();
            $inRoles = array();
            $users = collect();
            $userOwner = User::find($trainingRequest->user_id);
            Log::info($userOwner);
            $users = $users->concat(collect([$userOwner]));
            Log::info($users);
            // Si la capacitación es grupal encontramos todos los estudiantes y para enviar las notificaciones
            if ($trainingRequest->group == 'S') { // Grupal

                // Tomar los usuarios asociados a la capacitación
                $usersTraining = TrainingRequestUser::where('training_request_id', $trainingRequest->id)->get();

                // LLENAR LOS ID DE USAURIOS
                foreach ($usersTraining as $user) {
                    $usersId[] = $user->user_id;
                }

                $userStudents =  User::whereIn('id', $usersId)->get();
                $users = $users->concat($userStudents);
            }

            // Determinar el rol basado en la ciudad del usuario propietario
            $fee = floatval($trainingRequest->fee);
            $trueDirector = $fee >= $this->feeLimit ? true : false;

            switch ($newStatus->id) {
                case 2:
                case 3:

                    $usersAutorization = $this->getUserNotify($userOwner, true, true, $trueDirector);
                    break;
                case 5:
                case 7:
                case 8:
                    $usersAutorization = $this->getUserNotify($userOwner, false, true, $trueDirector);
                    break;
                case 6:
                    $usersAutorization = $this->getUserNotify($userOwner, false, true, true);
                    break;
                default:
                    $usersAutorization = $this->getUserNotify($userOwner, false, false, false);
                    break;
            }


            $users = $users->concat($usersAutorization);

            $month = config('constants.meses');
            $now = date('d') . ' de ' . $month[date('m')] . ' de ' . date('Y');

            // RECORRER TODA LA LISTA DE USUARIOS Y ENVIAR EL EMAIL
            $users->each(function ($u) use ($trainingRequest, $oldStatus, $newStatus, $now, $usersChange) {

                // Preparar los datos para el template del email
                $mailData = [
                    "subject" => "ACTUALIZACIÓN DE ESTADOS",
                    "mail" => $u->email,
                    "fecha_actual" => $now,
                    "user_name" => $usersChange->lastname,
                    "training_requests_name" => $trainingRequest->shortname,
                    "old_status" => $oldStatus->name,
                    "new_status" => $newStatus->name,
                    "url_training" => config('app.url') . '/config/trainingrequests/'
                ];

                // Enviar el correo
                Mail::send("emails.trainingrequeststatus", $mailData, function ($message) use ($mailData) {
                    $message->to($mailData['mail'])->subject($mailData['subject']);
                });
            });
        } catch (\Exception $e) {
            Log::alert($e->getMessage());
            $response = [
                'success' => false,
                'data' => 'Exception Error.',
                'message' => 'Lo sentimos, hubo un error al enviar los correos, por favor intente nuevamente'
            ];
            return response()->json($response, 400);
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

        if ($type == 1) {
            $resp->subject = "REGISTRO DE CAPACITACIÓN EXTERNA";
            $resp->template = "emails.trainingrequest";
        } elseif ($type == 2) {
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
