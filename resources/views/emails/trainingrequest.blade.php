<!DOCTYPE html>
<html lang="es">
  <head>
    <title>{{ $subject }}</title>
  </head>
  <body>
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: center;"><strong>SOLICITUD DE CAPACITACI&Oacute;N EXTERNA</strong></p>
    <p><strong>&nbsp;</strong></p>
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: justify;">
      El usuario <strong>{{ $user_name }} </strong>
      a creado una solicitud de capacitación externa con los siguientes detalles:
    </p>
    <p><strong>&nbsp;</strong></p>
    <table
      style="width: 60%; border-collapse: collapse;" 
      border="0" 
      cellspacing="0" 
      cellpadding="0"
    >
      <tbody>
        <tr>
          <td style="width: 46%;"><strong>Nombre de la capacitación:</strong></td>
          <td style="width: 38%;"> {{ $training_requests_name }}</td>
        </tr>
        <tr>
          <td style="width: 46%;"><strong>Fecha de inicio:</strong></td>
          <td style="width: 38%;"> {{ $training_requests_start_date }}</td>
        </tr>
        <tr>
          <td style="width: 46%;"><strong>Fecha de fin:</strong></td>
          <td style="width: 38%;"> {{ $training_requests_end_date }}</td>
        </tr>
        <tr>
          <td style="width: 46%;"><strong>Valor:</strong></td>
          <td style="width: 38%;">$ {{ $training_requests_value }}</td>
        </tr>
        <tr>
          <td style="width: 46%;"><strong>Modalidad:</strong></td>
          <td style="width: 38%;"> {{ $training_requests_methodology }}</td>
        </tr>
      </tbody>
    </table>
    <p><strong>&nbsp;</strong></p>
    <a target="_blank" href="{{ $url_training }}">
      <button style="
          padding: 12px;
          color: white;
          background-color: #4f2d7f;
          border: #4f2d7f;
          border-radius: 6px;
          font-size: 15px;
          cursor: pointer;"
        >
        VER CAPACITACIÓN
      </button>
    </a>
    <p>&nbsp;</p>
    
    <p>&nbsp;</p>
    <p>Cordialmente,</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p><strong>_______________________</strong></p>
    <p><strong>Equipo de Capacitaciones</strong></p>
    <p>Salles Sainz, Grant Thornton</p>
    <p><u>capacitacion@mx.gt.com</u></p>
  </body>
</html>
