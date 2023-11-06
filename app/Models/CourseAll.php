<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Support\Facades\Log;

class CourseAll extends Model
{
    protected $table = 'courses'; // Nombre de la tabla en la base de datos de Laravel

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'course_id',
        'user_id',
        'username',
        'provider',
        'name',
        'code',
        'progress',
        'qualification',
        'hours',
        'specialty_id',
        'start_date',
        'end_date',
    ];



    // Campos que se deben ocultar en las respuestas JSON
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Método para obtener todos los cursos desde la base de datos de Moodle y mapearlos a la estructura de la tabla 'courses'.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getAllCourses($email, $userId, $startDate, $endDate, $specialtyId)
    {
        try {

            $query = "SELECT 
                        concat(c.id,'-1') AS course_id, 
                        $userId as user_id,
                        concat (firstname, ' ', lastname) as username,  
                        c.fullname AS name,
                        c.shortname as code,
                        'DTX' AS provider,
                        COALESCE(
                            (
                                (
                                    SELECT count(completionstate) 
                                    FROM mdl_course_modules_completion 
                                    WHERE userid=u.id AND completionstate<>0 AND coursemoduleid IN (
                                        SELECT id 
                                        FROM mdl_course_modules 
                                        WHERE course=c.id AND completion>1
                                    )
                                ) / (
                                    SELECT count(id) 
                                    FROM mdl_course_modules 
                                    WHERE course=c.id AND completion>1
                                )
                            )*100,
                            0
                        ) AS progress,
                        COALESCE(
                            (
                                SELECT gg.finalgrade 
                                FROM mdl_grade_grades gg 
                                LEFT JOIN mdl_grade_items gi ON gi.id = gg.itemid 
                                WHERE gg.userid = u.id  AND gi.courseid=c.id AND gi.itemtype = 'course'
                            ),
                            0
                        ) AS qualification,  
                        COALESCE(
                            (
                                SELECT cd.value
                                FROM mdl_customfield_data cd
                                LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid
                                WHERE  cf.shortname='especialidad' AND cd.value !='' AND cd.instanceid=c.id
                            ),
                            0
                        ) as specialty_id,
                        COALESCE(
                            (
                                SELECT cd.value
                                FROM mdl_customfield_data cd
                                LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid
                                WHERE cf.shortname='horascurso' AND cd.instanceid=c.id
                            ),
                            0
                        ) as hours,
                        DATE(FROM_UNIXTIME(c.startdate)) as start_date,
                        c.enddate AS end_date 
                    FROM mdl_user u
                    JOIN mdl_user_enrolments ue ON ue.userid = u.id
                    JOIN mdl_enrol e ON e.id = ue.enrolid
                    JOIN mdl_course c ON c.id = e.courseid
                    WHERE u.email = '$email' 
                    AND DATE(FROM_UNIXTIME(c.startdate)) BETWEEN '$startDate' AND '$endDate' 
                    " . ($specialtyId ? "AND (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' AND cd.instanceid=c.id) = '$specialtyId'" : "") . " ";

            log::info($query);

            $courses = DB::connection('mysql_aux')->select($query);

            // Mapear los resultados al modelo CourseAll
            $mappedCourses = collect($courses)->map(function ($course) {
                return new CourseAll((array) $course);
            });


            // Obtener cursos locales
            $localCourses = DB::table('user_courses')
                ->leftJoin('courses', 'courses.id', '=', 'user_courses.course_id')
                ->leftJoin('users', 'users.id', '=', 'user_courses.user_id')
                ->leftJoin('providers', 'providers.id','=','courses.provider_id')
                ->where('user_courses.user_id', $userId)
                ->where('user_courses.attend_how', 'S')
                ->when($specialtyId, function ($query) use ($specialtyId) {
                    return $query->where('specialty_id', $specialtyId);
                })
                ->whereBetween('courses.start_date', [$startDate, $endDate])
                ->select([
                    'courses.id as course_id',
                    'user_courses.user_id',
                    'users.lastname as username',
                    'courses.name',
                    'courses.code',
                    'providers.name as provider',
                    'user_courses.progress',
                    'user_courses.qualification',
                    'courses.hours',
                    'courses.specialty_id',
                    'courses.start_date',
                    'courses.end_date',
                ])
                ->get()
                ->map(function ($course) {
                    return new CourseAll((array) $course);
                });

            // Si se proporciona specialty_id, agregarlo al filtro
            if ($specialtyId) {
                $localCourses->where('courses.specialty_id', $specialtyId);
            }

            // Unir las colecciones de cursos de Moodle y locales
            $allCourses = $mappedCourses->merge($localCourses);

            return $allCourses;
        } catch (\Exception $e) {
            Log::error('Error obteniendo los cursos: ' . $e->getMessage());
            // Puedes decidir si retornar algo específico en caso de error o simplemente dejar que el log maneje el registro del error.
            return null;
        }
    }

    public static function getTotalCompletedHours($startDate, $endDate, $email, $userId, $specialty_id)
    {
        // Consulta a Moodle
        $moodleQuery = "SELECT 
                            user_id,
                            COALESCE(SUM(hours), 0) AS total_hours
                        FROM (
                            SELECT 
                                c.id AS course_id, 
                                u.id as user_id,
                                DATE(FROM_UNIXTIME(c.startdate)) as finicio,
                                (
                                    (
                                        SELECT count(completionstate) 
                                        FROM mdl_course_modules_completion 
                                        WHERE userid=u.id AND completionstate<>0 AND coursemoduleid IN (
                                            SELECT id 
                                            FROM mdl_course_modules 
                                            WHERE course=c.id AND completion>1
                                        )
                                    ) / (
                                        SELECT count(id) 
                                        FROM mdl_course_modules 
                                        WHERE course=c.id AND completion>1
                                    )
                                )*100 AS progress,
                                (
                                    SELECT gg.finalgrade 
                                    FROM mdl_grade_grades gg 
                                    LEFT JOIN mdl_grade_items gi ON gi.id = gg.itemid 
                                    WHERE gg.userid = u.id  AND gi.courseid=c.id AND gi.itemtype = 'course'
                                ) AS qualification,
                                (
                                    SELECT cd.value
                                    FROM mdl_customfield_data cd
                                    LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid
                                    WHERE cf.shortname='horascurso' AND cd.instanceid=c.id
                                ) as hours,
                                c.fullname AS course_name,
                                c.enddate AS end_date,  -- Aquí faltaba la coma
                                COALESCE(
                                    (
                                        SELECT cd.value
                                        FROM mdl_customfield_data cd
                                        LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid
                                        WHERE  cf.shortname='especialidad' AND cd.value !='' AND cd.instanceid=c.id
                                    ),
                                    0
                                ) as specialty_id
                            FROM mdl_user u
                            JOIN mdl_user_enrolments ue ON ue.userid = u.id
                            JOIN mdl_enrol e ON e.id = ue.enrolid
                            JOIN mdl_course c ON c.id = e.courseid
                            WHERE u.email = '$email' 
                            AND DATE(FROM_UNIXTIME(c.startdate)) BETWEEN '$startDate' AND '$endDate'
                            " . ($specialty_id ? "AND (SELECT cd.value FROM mdl_customfield_data cd LEFT JOIN mdl_customfield_field cf ON cf.id=cd.fieldid WHERE cf.shortname='especialidad' AND cd.instanceid=c.id) = '$specialty_id'" : "") . " 
                        ) AS derived_table
                        WHERE hours IS NOT NULL AND progress >= 99
                        GROUP BY 
                            user_id";


        $moodleHours = DB::connection('mysql_aux')->select($moodleQuery, [
            'email' => $email,
            'startDate' => $startDate,
        ]);

        $laravelHoursQuery = DB::table('user_courses')
            ->leftJoin('courses', 'courses.id', '=', 'user_courses.course_id')
            ->where('user_courses.user_id', $userId)
            ->where('courses.start_date', '>=', $startDate)
            ->where('user_courses.progress', '>=', 99)
            ->when($specialty_id, function ($query) use ($specialty_id) {
                return $query->where('specialty_id', $specialty_id);
            })
            ->where('user_courses.attend_how', 'S')
            ->whereBetween('courses.start_date', [$startDate, $endDate]);


        // Inicializar las horas de Moodle a 0
        $moodleHoursTotal = 0;

        // Verificar si la consulta a Moodle devolvió resultados
        if (!empty($moodleHours) && isset($moodleHours[0]->total_hours)) {
            $moodleHoursTotal = $moodleHours[0]->total_hours;
        }

        // Asegurarse de que las horas de Laravel no son NULL
        $laravelHours = $laravelHours ?? 0;

        // Sumar las horas de Moodle y Laravel
        $totalHours = $moodleHoursTotal + $laravelHours;

        return $totalHours;
    }

    public function specialty()
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}
