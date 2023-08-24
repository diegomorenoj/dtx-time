<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\User;
use App\Models\Specialty;
use App\Models\UserSpecialty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the Specialties.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Specialty::all();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Specialties Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Get list Specialties in storage, by Filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getByFilter(Request $request)
    {
        $input = $request->all();      
        $name = $input['name'] === null ? '%%' : $input['name'];

        $data = DB::table('specialties AS s')
            ->select(
                's.*'
                , DB::raw('(SELECT COUNT(*) FROM user_specialties u WHERE u.specialty_id = s.id) AS users_count') // ajustar
                , DB::raw('null AS users')
            )
            ->whereRaw('UPPER(s.name) LIKE UPPER("'.$name.'")')
            ->orderBy('s.created_at','desc')
            ->get();

        // AJUSTAR
        foreach ($data as $item) {
            // --------------------------------------------------------------------------------------
            // USUARIOS ASOCIADOS
            // --------------------------------------------------------------------------------------
            $users = DB::table('user_specialties AS us')
                ->join('users AS u', 'u.id', '=', 'us.user_id')
                ->select('u.*')
                ->where('us.specialty_id', $item->id)
                ->get();   
            
            $item->users = $users;
        }
        
        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List Specialties Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Store a newly created Specialties in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
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

        $specialty = new Specialty;
        $specialty->name = $input['name'];
        $data = $specialty->save();

        // AJUSTAR
        // RELACIÓN DE USAURIOS
        if($input['users'] != null)
        {
            foreach ($input['users'] as $item) {
                $userSpecialty = new UserSpecialty;
                $userSpecialty->user_id = $item['id'];
                $userSpecialty->specialty_id = $specialty->id;
                $userSpecialty->save();
            }
        }
        
        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Especialidad guardada correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Update the specified Specialties in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Specialty  $specialty
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Specialty $specialty)
    {
        $input = $request->all();

        $rules = [
            'name' => 'required',
        ];

        $messages = [
            'name.required' => 'El nombre es obligatorio.',
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

        $specialty->name = $input['name'];
        $data = $specialty->save();

        // RELACIÓN DE USAURIOS
        // BUSCAR LOS USUARIOS ANTERIORES Y NULEARLOS, SOLO ACTUALIZAR LOS QUE VIENEN EN LA LISTA
        // BORRAR LA RELACIÓN
        UserSpecialty::where('specialty_id', $specialty->id)->delete();

        if($input['users'] != null)
        {
            foreach ($input['users'] as $item) {
                $userSpecialty = new UserSpecialty;
                $userSpecialty->user_id = $item['id'];
                $userSpecialty->specialty_id = $specialty->id;
                $userSpecialty->save();
            }
        }

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Especialidad actualizada correctamente.'
        ];

        return response()->json($response, 200);
    }

    public function storeExcel(Request $request, $id)
    {
        $input = $request->all();

        $rules = [
            'email' => 'required',
        ];

        $messages = [
            'email.required' => 'El correo del usuario es obligatorio.',
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

        $user = User::where('email', $input['email'])->first();

        // RELACIÓN DE USAURIOS
        if(is_null($user))
        {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'No existe el usuario con el correo => ' . $input['email']
            ];

            return response()->json($response, 200);
        }
        else
        {
            // VALIDAR SI ES IMPORTACIÓN INDIVIDUAL O GRUPAL
            $_id = $id == null || $id == 'null' ? isset($input['specialty_id']) ? $input['specialty_id'] : null : $id;

            if($_id == null)
            {
                $response = [
                    'success' => false,
                    'data' => null,
                    'message' => 'No ha diligenciado la espacialidad para el usuario con el correo => ' . $input['email']
                ];
    
                return response()->json($response, 200);
            }


            // VALIDAR QUE EL USUARIO NO ESTA REGISTRADO EN LA MISMA ESPECIALIDAD
            $resp = DB::table('user_specialties AS us')
                ->select('us.*')
                ->where('us.user_id', $user->id)
                ->where('us.specialty_id', $_id)
                ->get();

            if($resp->count() > 0)
            {
                $response = [
                    'success' => false,
                    'data' => null,
                    'message' => 'El usuario con el correo => ' . $input['email'] . ' ya esta relacionado con la especialidad'
                ];
    
                return response()->json($response, 200);
            }
            else
            {

                $userSpecialty = new UserSpecialty;
                $userSpecialty->user_id = $user->id;
                $userSpecialty->specialty_id = $_id;
                $userSpecialty->save();
            }
        }

        $response = [
            'success' => true,
            'data' => $user,
            'message' => 'Usuario => ' . $input['email'] .', importado correctamente.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified Specialties from storage.
     *
     * @param  \App\Models\Specialty  $specialty
     * @return \Illuminate\Http\Response
     */
    public function destroy(Specialty $specialty)
    {

        // BORRAR LA RELACIÓN
        UserSpecialty::where('specialty_id', $specialty->id)->delete();
        
        $specialty->delete();
        $data = null;

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Especialidad eliminada correctamente.'
        ];

        return response()->json($response, 200);
    }
}
