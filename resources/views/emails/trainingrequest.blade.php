<!DOCTYPE html>
<html lang="es">
  <head>
    <title>{{ $subject }}</title>
  </head>
  <body  style="background-color:#d8d8d8; padding: 40px;">
    <div style="max-width: 600px; margin: 10px auto; padding: 20px;  background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: center; color: #4f2d7f; text-align: center; ">
      <img src="https://dtx.grantthornton.mx/pluginfile.php/1/theme_learnr/logo/0x200/1690927393/logo_login.png" /><br/><br/>
      <strong>SOLICITUD DE CAPACITACI&Oacute;N EXTERNA</strong></p>
      <p><strong>&nbsp;</strong></p>
      <p style="text-align: justify;">
      El usuario <strong>{{ $user_name }} </strong>
      a creado una solicitud de capacitación externa con los siguientes detalles:
    </p>
    <p><strong>&nbsp;</strong></p>
    <table
      style="width: 100%; border-collapse: collapse; border: 1px solid #dddddd;" 
      border="0" 
      cellspacing="0" 
      cellpadding="0"
    >
      <tbody>
        <tr>
          <td style="width: 50%; border: 1px solid #dddddd; padding: 10px; background-color: #4f2d7f; color:#ffffff;"><strong>Nombre de la capacitación:</strong></td>
          <td style="border: 1px solid #dddddd; padding: 10px;"> {{ $training_requests_name }}</td>
        </tr>
        <tr>
          <td style="width: 50%; border: 1px solid #dddddd; padding: 10px; background-color: #4f2d7f; color:#ffffff;"><strong>Fecha de inicio:</strong></td>
          <td style="border: 1px solid #dddddd; padding: 10px;"> {{ $training_requests_start_date }}</td>
        </tr>
        <tr>
          <td style="width: 50%; border: 1px solid #dddddd; padding: 10px; background-color: #4f2d7f; color:#ffffff;"><strong>Fecha de fin:</strong></td>
          <td style="border: 1px solid #dddddd; padding: 10px;"> {{ $training_requests_end_date }}</td>
        </tr>
        <tr>
          <td style="width: 50%; border: 1px solid #dddddd; padding: 10px; background-color: #4f2d7f; color:#ffffff;"><strong>Valor:</strong></td>
          <td style="border: 1px solid #dddddd; padding: 10px;">$ {{ $training_requests_value }}</td>
        </tr>
        <tr>
          <td style="width: 50%; border: 1px solid #dddddd; padding: 10px; background-color: #4f2d7f; color:#ffffff;"><strong>Modalidad:</strong></td>
          <td style="border: 1px solid #dddddd; padding: 10px;"> {{ $training_requests_methodology }}</td>
        </tr>
      </tbody>
    </table>
    <p><strong>&nbsp;</strong></p>
    <a target="_blank" href="{{ $url_training }}">
      <button style="
            display: inline-block;
            padding: 12px;
            color: #ce2c2c;
            background-color: transparent;
            border: 0.13rem solid #ce2c2c;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            width: 100%;"
        > 
        VER CAPACITACIÓN
      </button>
    </a>
    <p>&nbsp;</p>
    <p>Cordialmente,</p>
    <p>&nbsp;</p>
    <p><strong>Equipo de Capacitaciones</strong><br/>
    Salles Sainz, Grant Thornton<br/>
    capacitacion@mx.gt.com</p>
    </div>
  </body>
</html>
