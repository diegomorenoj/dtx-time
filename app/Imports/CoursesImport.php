<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\Course;
use App\Models\Provider;
use App\Models\Specialty;
use App\Models\Parameter;  

class CoursesImport implements ToCollection
{
    /**
     * Handle the imported Excel rows.
     *
     * @param Collection $rows Excel rows.
     */
    public function collection(Collection $rows)
    {
        // Iterar a través de las filas del archivo Excel.
        foreach ($rows as $key => $row) {

            // Omitir la primera fila, asumiendo que es el encabezado del archivo Excel.
            if ($key === 0) continue;

            // Buscar el proveedor basado en el nombre proporcionado en la fila actual.
            $provider = Provider::where('name', $row[6])->first();

            // Buscar la especialidad basada en el nombre proporcionado en la fila actual.
            $specialty = Specialty::where('name', $row[7])->first();

            // Buscar el estado (status) en la tabla t_p_parameters basado en el nombre proporcionado en la fila actual.
            $status = Parameter::where('name', $row[8])->first();

            // Convertir el valor de la columna REQUERIDO en un booleano.
            $required = strtolower($row[9]) === 'sí' || strtolower($row[9]) === 'si' || $row[9] === '1';

            // Crear (o actualizar, si es necesario) un curso en la base de datos usando los datos de la fila actual.
            Course::create([
                'code'         => $row[0],
                'shortname'    => $row[1],
                'category'     => $row[2],
                'hours'        => $row[3],
                'start_date'   => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[4]),
                'end_date'     => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[5]),
                'provider_id'  => $provider ? $provider->id : null,
                'specialty_id' => $specialty ? $specialty->id : null,
                'status_id'    => $status ? $status->id : null,  // Usando el ID de la tabla t_p_parameters
                'required'     => $required
            ]);
        }
    }
}
