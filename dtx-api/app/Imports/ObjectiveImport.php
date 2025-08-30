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
        // Log para debugging
        Log::info('Fila procesada:', $row);
        
        // Verificar que existe la columna user_email
        if (!isset($row['user_email']) || empty($row['user_email'])) {
            $msg = "La columna 'user_email' no está presente o está vacía";
            $this->notFoundRows[] = $msg;
            Log::error($msg, ['row' => $row]);
            return null;
        }

        $user = User::where('email', $row['user_email'])->first();

        if (!$user) {
            $msg = "No se encontró el usuario con email {$row['user_email']}";
            $this->notFoundRows[] = $msg;
            Log::error($msg);
            return null; // Retorna null para continuar con la siguiente fila.
        }

        // Manejar specialty_id como opcional
        $specialtyId = null;
        if (isset($row['specialty_id']) && !empty($row['specialty_id'])) {
            if (!Specialty::find($row['specialty_id'])) {
                $msg = "No se encontró la especialidad con ID {$row['specialty_id']}";
                $this->notFoundRows[] = $msg;
                Log::error($msg);
                return null; // Retorna null para continuar con la siguiente fila.
            }
            $specialtyId = $row['specialty_id'];
        }        
     
        $attributes = [
            'user_id'         => $user->id,
            'start_date'         => $row['start_date'],
            'end_date'     => $row['end_date'],
            'specialty_id' => $specialtyId,
            'hours'        => $row['hours'],
            'area'   => $user->area,
            'position'   => $user->position,

        ];
        


        // Intenta actualizar el registro con 'user_id', 'start_date', y 'end_date' dados, si no existe, lo crea
        return Objective::updateOrCreate(
            [
                'user_id' => $user->id,
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date'],
                'specialty_id' => $specialtyId
            ], 
            $attributes
        );

    }
}

