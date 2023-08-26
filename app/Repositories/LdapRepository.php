<?php

namespace App\Repositories;

use Adldap;
use App\Models\User;

class LdapRepository {

    public function getUsers() 
    {

        $data = [];
        $users = array();
        $count = 0;
        $pageSize = 500;
        $cookie = '';

        $filtro='(objectcategory=Person)';
        $attributes=array(
            'cn',
            'title',
            'physicaldeliveryofficename',
            'givenname',
            'department',
            'samaccountname',
            'mail',
        );

        try {
            
            // crear paginacion

            // conexion al servidor LDAP
            $ldapconn = ldap_connect(config('app.ldap_host'), config('app.ldap_port')) or die("Could not connect to LDAP server.");
            
            if ($ldapconn) {

                ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0); 
                ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);

                // realizando la autenticacion
                $ldapbind = ldap_bind($ldapconn, config('app.ldap_user'), config('app.ldap_password'));
                
                // verificacion del enlace
                if ($ldapbind) {

                    do {
                        
                        ldap_control_paged_result($ldapconn, $pageSize, true, $cookie);
                        $search = ldap_search($ldapconn, config('app.ldap_base_search'), $filtro, $attributes);

                        //validamos busqueda
                    if ($search) {
                        $data = ldap_get_entries($ldapconn, $search);
                        $count = ldap_count_entries($ldapconn, $search);
                        
                        for ($i=0; $i<$data["count"]; $i++) {
                            
                            if(isset($data[$i]["givenname"]) && isset($data[$i]["department"]) && isset($data[$i]["mail"])){
                                $user = new User();
                                $user->lastname = $data[$i]["cn"][0];
                                $user->name = $data[$i]["givenname"][0];
                                $user->username = $data[$i]["samaccountname"][0];
                                $user->email = $data[$i]["mail"][0];
                                $user->area = $data[$i]["department"][0];
                                $user->position = isset($data[$i]["title"]) ? $data[$i]["title"][0] : null;
                                $user->city = $data[$i]["physicaldeliveryofficename"][0];
                                
                                $users[] = $user;
                            }
                        }
                    }

                    ldap_control_paged_result_response($ldapconn, $search, $cookie);

                    }while($cookie !== null && $cookie != '');
                    
                }
                ldap_close($ldapconn);   
            }
            
            $response['users'] = $users;
            $response['count'] = count($users);

            return $response;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function userExist($username) {

        $user_ldap = Adldap::search()->select(
            ['cn',
            'title',
            'physicaldeliveryofficename',
            'givenname',
            'department',
            'samaccountname',
            'mail']
            )->where('mail', '=', $username)->get();
            
        return count($user_ldap) > 0;
    }
}

