<!DOCTYPE html>
<html lang="es">
  <head>
    <title>{{ $subject }}</title>
  </head>
  <body>
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: center;"><strong>ACTUALIZACI&Oacute;N DE ESTADOS</strong></p>
    <p><strong>&nbsp;</strong></p>
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: justify;">
      El usuario <strong>{{ $user_name }}</strong>
      a modificado el estado de la capacitación externa
      <strong>{{ $training_requests_name }}</strong> de
      <strong>{{ $old_status }}</strong> a <strong>{{ $new_status }}</strong>
    </p>
    <p><strong>&nbsp;</strong></p>
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
