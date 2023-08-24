<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\LdapRepository;
use Illuminate\Support\Facades\Hash;

class LdapController extends Controller
{
    /**
     * @var LdapRepository
     */
    private $ldapRepository;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->ldapRepository = new LdapRepository;
    }


    /**
     * 
     *  Import Ldap Users
     */
    public function importLdapUsers()
    {
        $ldap_users = $this->ldapRepository->getUsers();
        $password = config('app.default_password');
        $user_count = 0;
        
        foreach ($ldap_users['users'] as $item) {
            
            try {

                $users = User::where('email', $item->email)->get();
                
                // Si no existe se lo debe crear en la base de datos local
                if ($users->isEmpty()){

                    $user = new User;
                    $user->lastname = $item->lastname;
                    $user->name = $item->name;
                    $user->username = $item->username;
                    $user->email = $item->email;
                    $user->area = $item->area;
                    $user->position = $item->position;
                    $user->city = $item->city;
                    $user->status = 'A';
                    $user->origin = 'ldap';
                    $user->rol_id = 2;
                    $user->password = Hash::make($password);
                    
                    // Save User
                    $user->save();
                    $user_count++;
                }
                
            } catch (\Exception $e) {
                // guardar el log en una tabla
                echo $e->getMessage();
            }
        }

        $response = [
            'success' => true,
            'data' => 'Users imported: '. $user_count,
            'message' => $user_count .' Usuarios importados Exitosamente'
        ];
        
        return response()->json($response, 200);
    }

    public function validateStatus()
    {
        try {
            $user_count = 0;
            $users = User::all();

            foreach ($users as $user) {
                if(!$this->ldapRepository->userExist($user->email))
                {
                    if($user->origin == 'ldap') 
                    {
                        $user->status = 'I';
                        $user->save();
                        $user_count++;
                    }
                }
                else
                {
                    if($user->origin == 'ldap') 
                    {
                        $user->status = 'A';
                        $user->save();
                        $user_count++;
                    }
                }
            }

            $response = [
                'success' => true,
                'data' => 'Users validated: '. $user_count,
                'message' => 'Users validated Successfully'
            ];

            return response()->json($response, 200);
            
        } catch (\Exception $e) {

            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $e->getMessage()
            ];

            return response()->json($response, 404);

        }
    }
}