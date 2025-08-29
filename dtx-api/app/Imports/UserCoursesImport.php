<?php 

namespace App\Imports;

use App\Models\Course;
use App\Models\User;
use App\Models\Objective;
use App\Models\UserCourse;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserCoursesImport implements ToModel, WithHeadingRow
{
    protected $notFoundRows = [];

    public function getNotFoundRows()
    {
        return $this->notFoundRows;
    }

    public function model(array $row)
    {
        $course = Course::where('code', $row['course_code'])->first();
        $user = User::where('email', $row['user_email'])->first();

        if (!$course) {
            $msg = "No se encontró el curso con código {$row['course_code']}";
            $this->notFoundRows[] = $msg;
            Log::error($msg);
            return null; // Retorna null para continuar con la siguiente fila.
        }
        
        if (!$user) {
            $msg = "No se encontró el usuario con email {$row['user_email']}";
            $this->notFoundRows[] = $msg;
            Log::error($msg);
            return null; // Retorna null para continuar con la siguiente fila.
        }
        
        // Puedes hacer lo mismo para el objetivo si es necesario.
        $objective = null; 
        if(isset($row['objective_id']) && !empty($row['objective_id'])) {
            
            $objective = Objective::find($row['objective_id']);
            
            if(!$objective) {
                $msg = "No se encontró el objetivo con ID {$row['objective_id']}";
                $this->notFoundRows[] = $msg;
                Log::error($msg);
                return null; // Retorna null para continuar con la siguiente fila.
            }
        }

        $attributes = [
            'course_id' => $course->id,
            'user_id' => $user->id,
            'attend_how' => $row['attend_how'],
            'progress' => $row['progress'],
            'qualification' => $row['qualification'],
            'hours' => $row['hours'],
            'status' => $row['status'],
            'objective_id' => $row['objective_id'] ?? null, 
        ];

        // Actualiza o Crea el registro
        return UserCourse::updateOrCreate(['course_id' => $course->id, 'user_id' => $user->id], $attributes);
    }
}
