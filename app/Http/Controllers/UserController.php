<?php

namespace App\Http\Controllers;

use Adldap;
use JWTAuth;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Repositories\LdapRepository;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DB::table('users AS u')
            ->join('rols AS r', 'r.id', '=', 'u.rol_id')
            ->select('u.*', 'r.name AS role_name')
            ->orderBy('name', 'desc')
            ->get();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'List User Successfully'
        ];
        
        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return null;
    }

    // @see: https://platzi.com/tutoriales/1467-curso-php-laravel/7629-api-rest-en-laravel-8-con-autenticacion-jwt/
    // https://github.com/adldap/adLDAP/blob/master/docs/SEARCH-FUNCTIONS.md
    public function authenticate(Request $request)
    {
        $input = $request->all();

        $username = $input['email'];
        $password = $input['password'];

        // VALIDAR EL ORIGEN DEL USAURIO PARA VER CONTRA QUE SE DEBE LOGUEAR
        $user = User::where('email', $username)->first();

        if($user) {
            if($user->origin == 'app') { // INGRESO POR LA APP
                $credentials = $request->only('email', 'password');
                try {
                    if (! $token = JWTAuth::attempt($credentials)) {
                        return response()->json(['error' => 'invalid_credentials'], 400);
                    }
                } catch (JWTException $e) {
                    return response()->json(['error' => 'could_not_create_token'], 500);
                }

                $user->permits = self::getPermits($user->rol_id);

                $response = [
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Login Successfully'
                ];
                
                return response()->json($response, 200);
                // return response()->json(compact('token'));
            }
        }
        
        // INGRESO POR LDAP
        try {
            if (Adldap::auth()->attempt($username, $password, $bindAsUser = true)) {
                
                // Validate user if exist
                $urer = self::validateUserLdap($input);
                
                // User has been Successfully authenticated.
                $credentials['email'] = $username;
                $credentials['password'] = config('app.default_password');

                try {
                    if (!$token = JWTAuth::attempt($credentials)) {
                        return response()->json(['error' => 'invalid_credentials'], 400);
                    }
                } catch (JWTException $e) {
                    return response()->json(['error' => 'could_not_create_token'], 500);
                }

                $user->permits = self::getPermits($user->rol_id);

                $response = [
                    'success' => true,
                    'token' => $token,
                    'user' => $user,
                    'message' => 'Login Successfully'
                ];
                
                return response()->json($response, 200);

            } else {
                // Failed.
                return response()->json(['error' => 'Fallo', 'username' => $username, 'password' => $password], 500);
            }
        } catch (Adldap\Auth\UsernameRequiredException $e) {
            // The user didn't supply a username.
            return response()->json(['error' => 'Usuario'], 500);
        } catch (Adldap\Auth\PasswordRequiredException $e) {
            // The user didn't supply a password.
            return response()->json(['error' => 'Contraseña'], 500);
        }
    }

    public function getCities() {
        $cities = User::distinct()->pluck('city');
        log::info("ciudades");
        log::info($cities);
        $response = [
            'success' => true,
            'data' => $cities,
            'message' => 'List Cities Successfully'
        ];
        
        return response()->json($response, 200);
    }

    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['token_absent'], $e->getStatusCode());
        }
        return response()->json(compact('user'));
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(compact('user', 'token'), 201);
    }

    /**
     * Update the specified User in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $input = $request->all();

        $rules = [
            'rol_id' => 'required',
        ];

        $messages = [
            'role_id.required' => 'El rol es obligatorio.',
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
        // SOLO SE ACTUALIZA EL ROL
        $user->rol_id = $input['rol_id'];      
        // GUARDAR
        $user->save();

        $response = [
            'success' => true,
            'data' => $user,
            'message' => 'User Updated Successfully'
        ];

        return response()->json($response, 200);
    }

    public function validateUserLdap($input)
    {

        $username = $input['email'];
        $password = config('app.default_password');

        $users = User::where('email', $username)->get();
         
        // Si no existe se lo debe crear en la base de datos local
        if ($users->isEmpty()){

            // Buscar en el directorio activo
            $user_ldap = Adldap::search()->select(
                ['cn',
                'title',
                'physicaldeliveryofficename',
                'givenname',
                'department',
                'samaccountname',
                'mail']
                )->where('mail', '=', $username)->get();

                $user = new User;
                $user->lastname = $user_ldap[0]["cn"][0];
                $user->name = isset($user_ldap[0]["givenname"]) ? $user_ldap[0]["givenname"][0] : null;
                $user->username = $user_ldap[0]["samaccountname"][0];
                $user->email = $user_ldap[0]["mail"][0];
                $user->area = isset($user_ldap[0]["department"]) ? $user_ldap[0]["department"][0] : null;
                $user->position = isset($user_ldap[0]["title"]) ? $user_ldap[0]["title"][0] : null;
                $user->city = $user_ldap[0]["physicaldeliveryofficename"][0];
                $user->status = 'A';
                $user->origin = 'ldap';
                $user->rol_id = 1;
                $user->password = Hash::make($password);

                $user->save();

            return $user; 
        }

        return $users->first();
    }

    public function getPermits($role_id) {

        $permits = new \stdClass();

        // VALIDAR LOS ROLES, PARA DETERMINAR LOS PERMISOS
        switch ($role_id) {
            case 1: // 1	Administrador
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = true;
                $permits->UPDATE_TRAINING = true;
                $permits->DELETE_TRAINING = true;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = true;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = true;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = true;
                $permits->SEARCH_GROUPS_TRAINING = true;
                $permits->SEARCH_AREAS_TRAINING = true;
                $permits->SEARCH_POSITIONS_TRAINING = true;
                $permits->SEARCH_CITIES_TRAINING = true;
                $permits->FEE_AVAILABLE_TRAINING = true;
                $permits->FEE_SPEND_TRAINING = true;
                $permits->APPROVED_TRAINING = true;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = true;
                $permits->SEARCH_GLOBAL_POSITION = true;
                $permits->SEARCH_GLOBAL_CITY = true;
                // PARA CURSOS
                $permits->CREATE_COURSES = true;
                $permits->IMPORT_COURSES = true;
                $permits->IMPORT_USERS_COURSES = true;
                $permits->UPDATE_COURSES = true;
                $permits->DELETE_COURSES = true;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = true;
                $permits->UPDATE_OBJETIVES = true;
                $permits->DELETE_OBJETIVES = true;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = true;
                $permits->UPDATE_BUDGETS = true;
                $permits->DELETE_BUDGETS = true;
                $permits->DISTRIBUTE_BUDGETS = true;
                $permits->READ_DISTRIBUTE_BUDGETS = true;

                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'MENU_COURSES';
                $menu[] = 'MENU_OBJETIVES_GENERAL';
                $menu[] = 'MENU_OBJETIVES_SPECIALTY';
                $menu[] = 'MENU_OBJETIVES_REPORT';
                $menu[] = 'MENU_SPECIALTIES';
                $menu[] = 'MENU_BUDGET';
                $menu[] = 'MENU_USERS';
                $menu[] = 'MENU_PROVIDERS';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;

                break;
        
            case 2: // 2	Usuario general
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = false;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = false;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = true;
                $permits->SEARCH_GLOBAL_POSITION = true;
                $permits->SEARCH_GLOBAL_CITY = true;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = true;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;
        
            case 3: // 3	Capacitaciones
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = true;
                $permits->UPDATE_TRAINING = true;
                $permits->DELETE_TRAINING = true;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = true;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = false;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = true;
                $permits->FEE_SPEND_TRAINING = true;
                $permits->APPROVED_TRAINING = true;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = true;
                $permits->SEARCH_GLOBAL_POSITION = true;
                $permits->SEARCH_GLOBAL_CITY = true;
                // PARA CURSOS
                $permits->CREATE_COURSES = true;
                $permits->IMPORT_COURSES = true;
                $permits->IMPORT_USERS_COURSES = true;
                $permits->UPDATE_COURSES = true;
                $permits->DELETE_COURSES = true;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = true;
                $permits->UPDATE_OBJETIVES = true;
                $permits->DELETE_OBJETIVES = true;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = true;
                $permits->UPDATE_BUDGETS = true;
                $permits->DELETE_BUDGETS = true;
                $permits->DISTRIBUTE_BUDGETS = true;
                $permits->READ_DISTRIBUTE_BUDGETS = true;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'MENU_COURSES';
                $menu[] = 'MENU_OBJETIVES_GENERAL';
                $menu[] = 'MENU_OBJETIVES_SPECIALTY';
                $menu[] = 'MENU_OBJETIVES_REPORT';
                $menu[] = 'MENU_SPECIALTIES';
                $menu[] = 'MENU_BUDGET';
                $menu[] = 'MENU_USERS';
                $menu[] = 'MENU_PROVIDERS';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;
        
            case 4: // 4	Encargado de oficina
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = false;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = true;
                $permits->SEARCH_AREAS_TRAINING = true;
                $permits->SEARCH_POSITIONS_TRAINING = true;
                $permits->SEARCH_CITIES_TRAINING = true;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = true;
                $permits->SEARCH_GLOBAL_POSITION = true;
                $permits->SEARCH_GLOBAL_CITY = false;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = true;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'MENU_BUDGET';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;
        
            case 5: // 5	Socio de Division
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = true;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = false;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = false;
                $permits->SEARCH_GLOBAL_POSITION = false;
                $permits->SEARCH_GLOBAL_CITY = false;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = true;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'MENU_BUDGET';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;

            case 11: // 11	Socio
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = false;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = false;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = false;
                $permits->SEARCH_GLOBAL_POSITION = false;
                $permits->SEARCH_GLOBAL_CITY = false;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = true;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;
        
            case 6: // 6	Socio de capacitaciones
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = true;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = true;
                $permits->SEARCH_AREAS_TRAINING = true;
                $permits->SEARCH_POSITIONS_TRAINING = true;
                $permits->SEARCH_CITIES_TRAINING = true;
                $permits->FEE_AVAILABLE_TRAINING = true;
                $permits->FEE_SPEND_TRAINING = true;
                $permits->APPROVED_TRAINING = true;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = true;
                $permits->SEARCH_GLOBAL_POSITION = true;
                $permits->SEARCH_GLOBAL_CITY = true;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;                
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = true;
                $permits->UPDATE_BUDGETS = true;
                $permits->DELETE_BUDGETS = true;
                $permits->DISTRIBUTE_BUDGETS = true;
                $permits->READ_DISTRIBUTE_BUDGETS = true;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'MENU_COURSES';
                $menu[] = 'MENU_OBJETIVES_GENERAL';
                $menu[] = 'MENU_OBJETIVES_SPECIALTY';
                $menu[] = 'MENU_OBJETIVES_REPORT';
                $menu[] = 'MENU_BUDGET';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;
        
            case 7: // 7	Socio Director
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = true;// Cambiar el estado del curso
                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = true;
                $permits->SEARCH_AREAS_TRAINING = true;
                $permits->SEARCH_POSITIONS_TRAINING = true;
                $permits->SEARCH_CITIES_TRAINING = true;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = true;
                $permits->SEARCH_GLOBAL_POSITION = true;
                $permits->SEARCH_GLOBAL_CITY = true;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = false;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'MENU_BUDGET';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;
        
            case 8: // 8	Finanzas
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = true;
                $permits->MANAGE_RECORDS = true;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = true;// Cambiar el estado del curso

                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = false;
                $permits->SEARCH_GLOBAL_POSITION = false;
                $permits->SEARCH_GLOBAL_CITY = false;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = false;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;

            case 9: // 9	Secretaria
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = true;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = false;
                $permits->MANAGE_RECORDS = false;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = true;
                $permits->CHANGE_STATUS_TRAINING = false;// Cambiar el estado del curso

                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = false;
                $permits->SEARCH_GLOBAL_POSITION = false;
                $permits->SEARCH_GLOBAL_CITY = false;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = false;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'MENU_TRAININGS';
                $menu[] = 'INDIVIDUAL_REPORT';
                $permits->MENU = $menu;
                break;

            case 10: // 10	Gerente
                $permits->VIEW_TRAINING = true;
                $permits->CREATE_TRAINING = true;
                $permits->CREATE_TRAINING_TERCERO = false;
                $permits->UPDATE_TRAINING = false;
                $permits->DELETE_TRAINING = false;
                $permits->MANAGE_COMMENTS = false;
                $permits->MANAGE_RECORDS = false;
                $permits->DELETE_RECORDS = false;
                $permits->VIEW_PAST_REQUESTS = true;
                $permits->VIEW_HISTORY = false;
                $permits->CHANGE_STATUS_TRAINING = false;// Cambiar el estado del curso

                // GRILLA Y FILTROS DE CAPACITACIONES EXTERNAS
                $permits->SEARCH_USERS_TRAINING = true;
                $permits->SEARCH_COURSES_TRAINING = false;
                $permits->SEARCH_GROUPS_TRAINING = false;
                $permits->SEARCH_AREAS_TRAINING = false;
                $permits->SEARCH_POSITIONS_TRAINING = false;
                $permits->SEARCH_CITIES_TRAINING = false;
                $permits->FEE_AVAILABLE_TRAINING = false;
                $permits->FEE_SPEND_TRAINING = false;
                $permits->APPROVED_TRAINING = false;
                // FILTROS INFORME GLOBAL
                $permits->SEARCH_GLOBAL_AREA = false;
                $permits->SEARCH_GLOBAL_POSITION = false;
                $permits->SEARCH_GLOBAL_CITY = false;
                // PARA CURSOS
                $permits->CREATE_COURSES = false;
                $permits->IMPORT_COURSES = false;
                $permits->IMPORT_USERS_COURSES = false;
                $permits->UPDATE_COURSES = false;
                $permits->DELETE_COURSES = false;
                // PARA OBJETIVOS
                $permits->CREATE_OBJETIVES = false;
                $permits->UPDATE_OBJETIVES = false;
                $permits->DELETE_OBJETIVES = false;
                // PARA PRESUPUESTOS
                $permits->CREATE_BUDGETS = false;
                $permits->UPDATE_BUDGETS = false;
                $permits->DELETE_BUDGETS = false;
                $permits->DISTRIBUTE_BUDGETS = false;
                $permits->READ_DISTRIBUTE_BUDGETS = false;
                // PERMISOS PARA EL MENÚ
                $menu = array();
                $menu[] = 'MENU_HOME';
                $menu[] = 'GENERAL_REPORT';
                $menu[] = 'INDIVIDUAL_REPORT';
                $menu[] = 'MENU_TRAININGS';
                $permits->MENU = $menu;
                break;
        }

        return $permits;
    }

}
