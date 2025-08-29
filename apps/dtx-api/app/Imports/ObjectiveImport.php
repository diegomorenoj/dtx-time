<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Log;
use App\Models\Objective;
use App\Models\Specialty;
use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ObjectiveImport implements ToModel, WithHeadingRow
{
    
    protected $notFoundRows = [];

    public function getNotFoundRows()
    {
        return $this->notFoundRows;
    }
    
    /**
     * Handle the imported Excel row.
     *
     * @param array $row Excel row.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $user = User::where('email', $row['user_email'])->first();

        if (!$user) {
            $msg = "No se encontró el usuario con email {$row['user_email']}";
            $this->notFoundRows[] = $msg;
            Log::error($msg);
            return null; // Retorna null para continuar con la siguiente fila.
        }

        // Verificar si el specialty_id existe en la base de datos
        if (isset($row['specialty_id']) && !Specialty::find($row['specialty_id'])) {
            $msg = "No se encontró la especialidad con ID {$row['specialty_id']}";
            $this->notFoundRows[] = $msg;
            Log::error($msg);
            return null; // Retorna null para continuar con la siguiente fila.
        }        
     
        $attributes = [
            'user_id'         => $user->id,
            'start_date'         => $row['start_date'],
            'end_date'     => $row['end_date'],
            'specialty_id' => $row['specialty_id'],
            'hours'        => $row['hours'],
            'area'   => $user->area,
            'position'   => $user->position,

        ];
        


        // Intenta actualizar el registro con 'code' dado, si no existe, lo crea
        //return Objective::updateOrCreate(['user_id' => $row['user_id']], $attributes);

        // Intenta actualizar el registro con 'user_id', 'start_date', y 'end_date' dados, si no existe, lo crea
        return Objective::updateOrCreate(
            [
                'user_id' => $user->id,
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'specialty_id' => $row['specialty_id']
            ], 
            $attributes
        );

    }
}

