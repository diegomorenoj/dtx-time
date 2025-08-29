# DTX Time API

API REST para el sistema de gestión de tiempo DTX Time, desarrollada con Laravel 8 y autenticación JWT.

## Tecnologías principales

- **Framework**: Laravel 8.x
- **Autenticación**: JWT (JSON Web Tokens)
- **Base de datos**: MySQL
- **Integración LDAP**: Para autenticación corporativa
- **PHP**: 7.4+

## Instalación

### Requisitos previos

- PHP 7.4 o superior
- Composer
- MySQL
- Extensión LDAP de PHP (opcional para desarrollo)

### Pasos de instalación

1. **Clonar el repositorio**
   ```bash
   git clone [repository-url]
   cd dtx-api
   ```

2. **Instalar dependencias**
   
   ⚠️ **Nota importante**: Si no tienes la extensión LDAP instalada, usa:
   ```bash
   composer install --ignore-platform-reqs
   ```
   
   Si tienes LDAP configurado correctamente:
   ```bash
   composer install
   ```

3. **Configurar el archivo .env**
   
   Copia la configuración desde tu entorno existente o crea un nuevo archivo:
   ```bash
   cp .env.example .env
   ```
   
   Configura las variables principales:
   ```env
   APP_NAME="App DTX TIME"
   APP_URL=http://localhost:8000
   
   # Base de datos principal
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=dtx_time
   DB_USERNAME=root
   DB_PASSWORD=rootroot
   
   # Base de datos auxiliar (Moodle)
   DB_HOST_AUX=localhost
   DB_PORT_AUX=3306
   DB_DATABASE_AUX=dtx_moodle
   DB_USERNAME_AUX=root
   DB_PASSWORD_AUX=rootroot
   
   # Configuración LDAP
   LDAP_HOSTS=189.254.29.155
   LDAP_BASE_DN=dc=ssgt,dc=corp
   LDAP_PORT=389
   ```

4. **Configurar JWT**
   ```bash
   # Publicar configuración de JWT
   php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
   
   # Generar clave JWT (se hace automáticamente)
   php artisan jwt:secret --show
   
   # Limpiar configuración cache
   php artisan config:clear
   php artisan config:cache
   ```

5. **Verificar instalación**
   ```bash
   # Probar conexión a base de datos
   php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection successful!';"
   
   # Iniciar servidor de desarrollo
   php artisan serve --port=8000
   ```

## Problemas comunes y soluciones

### Error de extensión LDAP

**Problema**: `adldap2/adldap2 requires ext-ldap`

**Solución**:
```bash
# Instalar OpenLDAP (macOS)
brew install openldap

# Usar flag de ignorar requisitos de plataforma
composer install --ignore-platform-reqs
```

### Error de dependencias bloqueadas

**Problema**: `Your lock file does not contain a compatible set of packages`

**Solución**:
```bash
composer update
# o
composer install --ignore-platform-reqs
```

### Problemas con JWT

**Problema**: Tokens JWT no funcionan entre diferentes instalaciones

**Solución**: Asegúrate de usar el mismo `JWT_SECRET` en todos los entornos o genera uno nuevo:
```bash
php artisan jwt:secret --show
```

## Entornos de desarrollo

### Laravel Herd
Si usas Laravel Herd, las extensiones PHP pueden estar limitadas. En este caso, siempre usa `--ignore-platform-reqs` para la instalación.

### MAMP
Si migras desde MAMP, asegúrate de:
1. Copiar el archivo `.env` existente
2. Verificar que las bases de datos estén accesibles
3. Ajustar el `APP_URL` al puerto correcto

## Estructura del proyecto

- **Autenticación**: JWT con integración LDAP corporativa
- **Base de datos dual**: `dtx_time` (principal) y `dtx_moodle` (auxiliar)
- **APIs**: Endpoints RESTful para gestión de tiempo

## Comandos útiles

```bash
# Limpiar cache
php artisan config:clear
php artisan cache:clear

# Ver configuración JWT actual
php artisan jwt:secret --show

# Probar conexión DB
php artisan tinker --execute="DB::connection()->getPdo();"

# Servidor de desarrollo
php artisan serve --port=8000
```

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](https://opensource.org/licenses/MIT).
