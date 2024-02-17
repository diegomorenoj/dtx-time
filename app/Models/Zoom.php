<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Log;

class Zoom extends Model
{
    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'fecha',
        'curso_id',
        'nombre_sesion',
        'duracion',
        'nombre_usuario',
        'email',
        'tiempo_en_linea',
        'area',
        'cargo',
        'ciudad',
    ];

    // Campos que se deben ocultar en las respuestas JSON
    protected $hidden = ['created_at', 'updated_at'];


    /**
     * Recoge todos los datos de ZOOM para ser mostrados en el sistema
     * se debe tener en cuenta que saca información por curos y por usuarios individuales
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getZoom($email, $course_id)
    {
        try {

            // Construcción de la consulta SQL con el condicional
            $query = "SELECT 
                                date_format(FROM_UNIXTIME(zmd.start_time),'%d-%m-%Y') as fecha,
                                z.course as curso_id,
                                z.name as nombre_sesion,
                                SEC_TO_TIME(zmd.duration*60) as duracion,
                                zmp.name as nombre_usuario,
                                zmp.user_email as email,
                                SEC_TO_TIME(sum(zmp.duration)) as tiempo_en_linea
                        FROM mdl_zoom z
                        LEFT JOIN mdl_zoom_meeting_details zmd ON zmd.zoomid=z.id
                        LEFT JOIN mdl_zoom_meeting_participants zmp ON zmp.detailsid=zmd.id
                        WHERE z.course = ".$course_id;

            if ($email) {
                $query .= " AND zmp.user_email='".$email."'";
            }

            $query .= " GROUP BY z.id, zmp.user_email, fecha, z.course, z.name, zmd.duration, zmp.name";

            $zoom = DB::connection('mysql_aux')->select($query);
            
            $zoomEmails = collect($zoom)->pluck('email')->unique()->toArray();
            // Cargamos solo los usuarios que necesitamos
            $users = User::whereIn('email', $zoomEmails)->get()->keyBy(function ($user) {
                // Convertir los emails a minúsculas antes de indexar
                return strtolower($user->email);
            });
  
            // Mapear los resultados al modelo Zoom
            $lstZoom = collect($zoom)->map(function ($zm) use ($users) {
                $zm = new Zoom((array) $zm);
                $user = $users[$zm->email] ?? null;
                if ($user) {
                    $zm->area = $user->area;
                    $zm->cargo = $user->position;
                    $zm->ciudad = $user->city;
                }
                return $zm;
            });

            return $lstZoom;
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo los cursos: ' . $e->getMessage());
            // Puedes decidir si retornar algo específico en caso de error o simplemente dejar que el log maneje el registro del error.
            return null;
        }
    }
}
