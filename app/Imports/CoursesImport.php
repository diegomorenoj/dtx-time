<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Provider;
use App\Models\Specialty;
use App\Models\Parameter;

class CoursesImport implements ToModel
{
    /**
     * Handle the imported Excel row.
     *
     * @param array $row Excel row.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        
        $attributes = [
            'code'         => $row[0],
            'name'         => $row[1],
            'category'     => $row[2],
            'hours'        => $row[3],
            'start_date'   => $row[4],
            'end_date'     => $row[5],
            'provider_id'  => $row[6],
            'specialty_id' => $row[7],
            'status_id'    => $row[8],  // Usando el ID de la tabla t_p_parameters
            'required'     => $row[9]
        ];
        


        // Intenta actualizar el registro con 'code' dado, si no existe, lo crea
        return Course::updateOrCreate(['code' => $row[0]], $attributes);


    }
}

