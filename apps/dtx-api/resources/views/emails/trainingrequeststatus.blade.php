<!DOCTYPE html>
<html lang="es">
  <head>
    <title>{{ $subject }}</title>
  </head>
  <body style="background-color:#d8d8d8; padding: 40px;">
  <div style="max-width: 600px; margin: 10px auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);">
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: center; color: #4f2d7f; text-align: center;">
    <img src="https://dtx.grantthornton.mx/pluginfile.php/1/theme_learnr/logo/0x200/1690927393/logo_login.png" /><br/><br/>    
    <strong>ACTUALIZACI&Oacute;N DE ESTADOS</strong></p>
    <p><strong>&nbsp;</strong></p>
    <p style="text-align: justify;">
      El usuario <strong>{{ $user_name }}</strong>
      a modificado el estado de la capacitación externa
      <strong>{{ $training_requests_name }}</strong> de
      <strong>{{ $old_status }}</strong> a <strong>{{ $new_status }}</strong>
    </p>
    <p><strong>&nbsp;</strong></p>
    <p style="text-align:center;"><strong>&nbsp;</strong>
    <a target="_blank" href="{{ $url_training }}">
      <button style="
          padding: 12px;
          color: white;
          background-color: #4f2d7f;
          border: #4f2d7f;
          border-radius: 6px;
          font-size: 15px;
          cursor: pointer; "
        >
        VER CAPACITACIÓN
      </button>
    </a>
    </p>
    <p>&nbsp;</p>
    <p>Cordialmente,</p>
    <p>&nbsp;</p>
    <p><strong>Equipo de Capacitaciones</strong><br/>
    Salles Sainz, Grant Thornton<br/>
    capacitacion@mx.gt.com</p>
  </div>
  </body>
</html>
