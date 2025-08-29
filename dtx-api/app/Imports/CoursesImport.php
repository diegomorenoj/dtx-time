<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\Provider;
use App\Models\Specialty;
use App\Models\Parameter;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoursesImport implements ToModel, WithHeadingRow
{
    /**
     * Handle the imported Excel row.
     *
     * @param array $row Excel row.
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
     
        Log::info('Procesando fila: ' . print_r($row, true));
        $attributes = [
            'code'         => $row['code'],
            'name'         => $row['shortname'],
            'category'     => $row['category'],
            'hours'        => $row['hours'],
            'start_date'   => $row['start_date'],
            'end_date'     => $row['end_date'],
            'provider_id'  => $row['provider_id'],
            'specialty_id' => $row['specialty_id'],
            'status_id'    => $row['status_id'],  
            'required'     => $row['required']
        ];
        


        // Intenta actualizar el registro con 'code' dado, si no existe, lo crea
        return Course::updateOrCreate(['code' => $row['code']], $attributes);


    }
}

