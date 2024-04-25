<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use App\Models\Parameter;
use App\Models\TrainingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParameterController extends Controller
{
    /**
     * @var
     */
    protected $user;
    protected $fee_limit;
    protected $payment;
    protected $free;
    protected $city_mx;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
        $this->fee_limit = 50000;
        $this->payment = 'P';
        $this->free = 'G';
        $this->city_mx = 'Mexico';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getByType($tp_parameter_id)
    {
        $data = Parameter::where('tp_parameter_id', $tp_parameter_id)->orderBy('id', 'asc')->get();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Parameters Successfully'
        ];

        return response()->json($response, 200);
    }


    /**
     * Display a listing of the users by filter name.
     *
     * @param  String  $val
     * @return \Illuminate\Http\Response
     */
    public function filterByNameUser($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('users AS u')
            ->select('u.*')
            ->where(function ($query) use ($_val) {
                $query->where(DB::raw('UPPER(u.name)'), 'LIKE', strtoupper($_val))
                    ->orWhere(DB::raw('UPPER(u.lastname)'), 'LIKE', strtoupper($_val));
            })
            ->orderBy('name', 'asc')
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data
        ];

        return response()->json($response, 200);
    }

    /**
     * Display a listing of the users by filter and rol.
     *
     * @param  String  $val
     * @return \Illuminate\Http\Response
     */
    public function filterUsers($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';
        $city = $this->user->rol_id == 4 ? $this->user->city : '%%'; // PARA EL ROL 4: Encargado de oficina, Cargar solo lo de su ciudad

        $data = DB::table('users AS u')
            ->select('u.*')
            ->whereRaw('(UPPER(u.name) LIKE UPPER("' . $_val . '") OR UPPER(u.lastname) LIKE UPPER("' . $_val . '"))')
            ->whereRaw('u.city LIKE "' . $city . '"')
            ->orderBy('name', 'asc')
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data
        ];

        return response()->json($response, 200);
    }

    public function filterCities($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('users AS u')
            ->select('u.city')
            ->whereRaw('UPPER(u.city) LIKE UPPER("' . $_val . '")')
            ->where('u.position', 'not like', 'cuenta%')
            ->orderBy('city', 'asc')
            ->distinct()
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data,
        ];

        return response()->json($response, 200);
    }

    public function filterAreas($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('users AS u')
            ->select('u.area')
            ->whereRaw('UPPER(u.area) LIKE UPPER("' . $_val . '")')
            ->orderBy('area', 'asc')
            ->distinct()
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data,
        ];

        return response()->json($response, 200);
    }

    public function filterPositions($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('users AS u')
            ->select('u.position')
            ->whereRaw('UPPER(u.position) LIKE UPPER("' . $_val . '")')
            ->orderBy('position', 'asc')
            ->distinct()
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data,
        ];

        return response()->json($response, 200);
    }

    public function filterLevels($val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('users AS u')
            ->select('u.level')
            ->whereRaw('UPPER(u.level) LIKE UPPER("' . $_val . '")')
            ->orderBy('level', 'asc')
            ->distinct()
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data,
        ];

        return response()->json($response, 200);
    }

    public function filterAreasByCity($city, $val)
    {
        $_val = $val === null ? '%%' :  $val . '%';

        $data = DB::table('users')
            ->select('area')
            ->whereRaw('UPPER(city) = UPPER(?)', [$city])
            ->whereRaw('UPPER(area) LIKE UPPER(?)', ["%$_val%"])
            ->orderBy('area', 'asc')
            ->distinct()
            ->get();

        $response = [
            'count' => $data->count(),
            'entries' => $data,
        ];

        return response()->json($response, 200);
    }

    public function getAllAreas()
    {
        $data = DB::table('users AS u')
            ->select('u.area')
            ->whereNotNull('area')
            ->orderBy('area', 'asc')
            ->distinct()
            ->get();

        $response = [
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    public function getPositionsByArea($area)
    {
        $data = DB::table('users AS u')
            ->select('u.position')
            ->whereRaw('UPPER(u.area) = UPPER("' . $area . '")')
            ->whereNotNull('position')
            ->orderBy('position', 'asc')
            ->distinct()
            ->get();

        $response = [
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    public function getLevelsByPosition($position)
    {
        $data = DB::table('users AS u')
            ->select('u.level')
            ->whereRaw('UPPER(u.position) = UPPER("' . $position . '")')
            ->whereNotNull('level')
            ->orderBy('level', 'asc')
            ->distinct()
            ->get();

        $response = [
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    public function getStatesByTrainingAndRole($training_request_id)
    {
        // crear servicio para listar los estados de las capacitaciones segun el rol y la capacitación externa
        // HACER UN SWICH
        $trainingRequest = TrainingRequest::find($training_request_id);
        $user = User::find($trainingRequest->user_id);
        $city_name = strtoupper($user->city);

        Log::info($city_name);

        // VALIDAR LOS ESTADOS PREDECESORES A EN CURSO
        // 3: Pagadausers courses
        // 4: Autorizada
        // 7: Autorizada sin pago
        $pre_en_curso = DB::table('training_requests_logs')
            ->where('training_request_id', $training_request_id)
            ->whereIn('after_status_id', [3, 4, 7])
            ->get();

        // VALIDAR LOS ESTADOS PREDECESORES A COMPLETADA PARA REVISIÓN
        // 3: Pagada
        // 4: Autorizada
        // 7: Autorizada sin pago
        // 9: En curso
        $pre_completada_revision = DB::table('training_requests_logs')
            ->where('training_request_id', $training_request_id)
            ->whereIn('after_status_id', [3, 4, 7, 9])
            ->get();



        // VALIDAR LOS ESTADOS PREDECESORES A COMPLETADA
        // 3: Pagada
        // 4: Autorizada
        // 7: Autorizada sin pago
        // 9: En curso
        // 10: Completada para revisión
        $pre_completada = DB::table('training_requests_logs')
            ->where('training_request_id', $training_request_id)
            ->whereIn('after_status_id', [3, 4, 7, 9, 10])
            ->get();

        $states = array();

        switch ($this->user->rol_id) {
            case 1: // 1	Administrador
                if ($trainingRequest->status_id != 11) {
                    $states[] = 1;
                    if ($trainingRequest->type == $this->payment) $states[] = 2;
                    $states[] = 3;
                    if ($trainingRequest->type == $this->free) $states[] = 4; // SOLO SI ES GRATIS
                    $states[] = 5;
                    // SOLO SI ES MAYOR AL LIMITE
                    if ($trainingRequest->type == $this->payment && $trainingRequest->fee > $this->fee_limit) $states[] = 6;
                    if ($trainingRequest->type == $this->payment) $states[] = 7; // SOLO PAGAS
                    $states[] = 8;
                    if ($pre_en_curso->count() > 0) $states[] = 9;
                    if ($pre_completada_revision->count() > 0) $states[] = 10;
                    if ($pre_completada->count() > 0) $states[] = 11;
                }
                break;

            case 2: // 2	Usuario general
                if ($trainingRequest->status_id != 11) {
                    if ($pre_en_curso->count() > 0) $states[] = 9;
                    if ($pre_completada_revision->count() > 0) $states[] = 10;
                }
                break;

            case 3: // 3	Capacitaciones

                if ($trainingRequest->status_id != 11) {
                    // SOLO PAGAS MENORES O IGUALES AL LIMITE
                    if ($trainingRequest->type == $this->payment && $trainingRequest->fee <= $this->fee_limit) $states[] = 2;
                    if ($pre_en_curso->count() > 0) $states[] = 9;
                    if ($pre_completada->count() > 0) $states[] = 11;
                    $states[] = 1;
                }

                break;

            case 4: // 4	Encargado de oficina
                if ($trainingRequest->status_id != 11) {
                    $states[] = 5;
                    $states[] = 8;
                }
                if ($trainingRequest->type == $this->free) $states[] = 4;
                break;

            case 5: // 5	Socio
                if ($trainingRequest->status_id != 11) {
                    $states[] = 0;
                    log::info("Hola ");
                    log::info($city_name. " " .$this->city_mx);
                    // CURSOS DE LA CIUDAD DE MEXICO
                    if (str_contains(mb_strtoupper($city_name), mb_strtoupper($this->city_mx))) {
                        $states[] = 5;
                        $states[] = 8;
                    }
                }
                if ($trainingRequest->type == $this->free) $states[] = 4;
                break;

            case 6: // 6	Socio de capacitaciones

                if ($trainingRequest->status_id != 11) {
                    $states[] = 8;
                    if ((str_contains(strtolower($city_name), strtolower($this->city_mx))
                    ==true)) {
                        if(($trainingRequest->status_id == 5)) {
                            if ($trainingRequest->type == $this->free || ($trainingRequest->type == $this->payment && $trainingRequest->fee <= $this->fee_limit)) $states[] = 4; // SOLO SI ES GRATIS
                        }
                        
                    } else {
                        if(($trainingRequest->status_id == 5)) {
                            if ($trainingRequest->type == $this->payment && $trainingRequest->fee <= $this->fee_limit) $states[] = 2; // SOLO PAGAS MENORES O IGUALES AL LIMITE
                            if ($trainingRequest->type == $this->free) $states[] = 4; // SOLO SI ES GRATIS
                            if ($trainingRequest->type == $this->payment && $trainingRequest->fee <= $this->fee_limit) $states[] = 7; // SOLO PAGAS MENORES O IGUALES AL LIMITE
                        }
                        if ($trainingRequest->type == $this->payment && $trainingRequest->fee > $this->fee_limit) $states[] = 6; // SOLO SI ES MAYOR AL LIMITE
                    }    


                }
                break;

            case 7: // 7	Socio Director
                // CURSOS DE LA CIUDAD DE MEXICO
                if ($trainingRequest->status_id != 11) {
                    if (str_contains(strtolower($city_name), strtolower($this->city_mx))==true) {
                        if ($trainingRequest->type == $this->payment && $trainingRequest->fee > $this->fee_limit) {
                            $states[] = 4;
                            $states[] = 8;
                        }
                    } else {
                        if ($trainingRequest->type == $this->payment && $trainingRequest->fee > $this->fee_limit) $states[] = 2;
                        if ($trainingRequest->type == $this->payment && $trainingRequest->fee > $this->fee_limit) $states[] = 7; // SOLO PAGAS MAYOR AL LIMITE
                        $states[] = 8;
                    }
                }
                break;

            case 8: // 8	Finanzas
                $states[] = 3;
                break;
        }

        // ESTADO DE LA CAPACITACIÓN EXTERNA
        $states[] = $trainingRequest->status_id;

        Log::info($states);

        $data = DB::table('parameters')
            ->whereIn('id', $states)
            ->orderBy('id', 'asc')
            ->get();


        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Parameters Successfully'
        ];

        return response()->json($response, 200);
    }
}
