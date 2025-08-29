# CLAUDE.md - DTX Time Project Instructions

## Proyecto Overview

Este es un proyecto legacy que unifica reportes de capacitaciones empresariales:
- **API Laravel 8** con conexión a dos bases de datos (Laravel + Moodle)
- **App Vue 2** con Vuetify Material Dashboard
- **Despliegue FTP** para producción
- **Entorno local** en macOS M3 con Herd

## REGLAS CRÍTICAS DE DESARROLLO

### ⚠️ NO PERMITIDO
- **NO instalar nuevos paquetes** (npm o composer)
- **NO actualizar dependencias existentes** 
- **NO usar `composer install` sin `--ignore-platform-reqs`**
- **NO usar `npm install` sin `--ignore-scripts`**
- **NO tocar package.json o composer.json** para cambiar versiones

### ✅ PERMITIDO
- Modificar código existente
- Crear nuevos archivos PHP/Vue
- Modificar configuraciones (.env, config files)
- Usar herramientas existentes del proyecto

## Estructura del Proyecto

```
dtx-time/
├── dtx-api/     # Laravel 8 API Backend
├── dtx-app/     # Vue 2 Frontend
└── CLAUDE.md    # Este archivo
```

## Configuración del Entorno Local

### API (dtx-api)
- **Framework:** Laravel 8.x
- **PHP:** 7.4 (con Herd)
- **Base de datos principal:** `dtx_time` (MySQL local)
- **Base de datos auxiliar:** `dtx_moodle` (MySQL local - datos de Moodle)
- **Autenticación:** JWT + LDAP

### App (dtx-app)
- **Framework:** Vue 2.6.11
- **UI:** Vuetify 2.3.13
- **Node:** 12.x (usar NVM)
- **Estado:** Vuex
- **HTTP:** Axios con interceptors JWT

## Comandos de Desarrollo

### API Laravel
```bash
cd dtx-api

# Instalación (SIEMPRE con este flag)
composer install --ignore-platform-reqs

# Servidor desarrollo
php artisan serve --port=8000

# JWT setup
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret --show
php artisan config:clear && php artisan config:cache

# Test conexión DB
php artisan tinker --execute="DB::connection()->getPdo(); echo 'OK';"
php artisan tinker --execute="DB::connection('mysql_aux')->getPdo(); echo 'Moodle OK';"
```

### App Vue
```bash
cd dtx-app

# Instalación (SIEMPRE con este flag)
npm install --ignore-scripts

# Desarrollo
npm run serve  # Puerto 8080

# Build producción
npm run build  # Genera dist/

# Lint
npm run lint
```

## Variables de Entorno

### dtx-api/.env
```env
APP_NAME="App DTX TIME"
APP_URL=http://localhost:8000

# Base de datos principal
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dtx_time
DB_USERNAME=root
DB_PASSWORD=rootroot

# Base de datos auxiliar (Moodle)
DB_HOST_AUX=127.0.0.1
DB_PORT_AUX=3306
DB_DATABASE_AUX=dtx_moodle
DB_USERNAME_AUX=root
DB_PASSWORD_AUX=rootroot

# LDAP Corporativo
LDAP_HOSTS=189.254.29.155
LDAP_BASE_DN=dc=ssgt,dc=corp
LDAP_PORT=389
```

### dtx-app/.env
```env
VUE_APP_RUTA_API=http://127.0.0.1:8000/api
VUE_APP_MES_INICIAL=AGOSTO
VUE_APP_MES_FINAL=JULIO
```

### dtx-app/.env.production
```env
VUE_APP_RUTA_API=https://dtx-api.grantthornton.mx/api
VUE_APP_MES_INICIAL=AGOSTO
VUE_APP_MES_FINAL=JULIO
```

## Arquitectura de Base de Datos

### Conexión Dual en Laravel
```php
// config/database.php
'connections' => [
    'mysql' => [...],        // DB principal Laravel
    'mysql_aux' => [...],    // DB auxiliar Moodle
]

// Uso en Modelos/Controladores
DB::connection('mysql')->select(...);     // Laravel
DB::connection('mysql_aux')->select(...); // Moodle
```

### Modelos Importantes
- `CourseAll.php` - Unifica cursos Laravel + Moodle
- `Zoom.php` - Datos desde Moodle
- `User.php` - Usuarios con LDAP

## Conexión Frontend-Backend

### Autenticación JWT
```js
// src/util/interceptors.js
axios.interceptors.request.use(function (config) {
    const token = store.getters.token;
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});
```

### Servicios API
```js
// src/services/AuthService.js
login(data) {
    return axios.post(`${process.env.VUE_APP_RUTA_API}/users/login`, data);
}
```

## Flujo de Despliegue a Producción

### 1. API (FTP Manual)
```bash
# En dtx-api/
# Subir archivos PHP por FTP excluyendo:
# - /vendor
# - /node_modules  
# - /storage/logs
# - /.env (usar configuración del servidor)
```

### 2. App (Build + FTP)
```bash
# En dtx-app/
npm run build  # Genera dist/

# Subir contenido de dist/ por FTP al servidor web
```

## Problemas Comunes y Soluciones

### Error LDAP Extension
```bash
# macOS con Herd
composer install --ignore-platform-reqs
```

### Error fsevents (Vue)
```bash
# Node 12 en macOS moderno
npm install --ignore-scripts
```

### Token JWT no funciona
```bash
# Regenerar secret
php artisan jwt:secret --show
php artisan config:clear
```

### Conexión DB Moodle falla
```bash
# Verificar conexión auxiliar
php artisan tinker --execute="DB::connection('mysql_aux')->getPdo();"
```

## Flujo de Trabajo Recomendado

### Para nuevas funciones:
1. **Planificar** la funcionalidad
2. **Backend:** Crear/modificar controladores, modelos, rutas en `dtx-api/`
3. **Frontend:** Crear/modificar componentes, servicios, vistas en `dtx-app/`
4. **Testing:** Verificar en desarrollo
5. **Build:** `npm run build` para generar dist/
6. **Deploy:** FTP manual de archivos

### Para debugging:
1. **API:** Logs en `dtx-api/storage/logs/laravel.log`
2. **App:** DevTools del navegador
3. **DB:** Verificar conexiones con tinker

## Contacto y Documentación

- **Entorno:** macOS M3 con Herd, PHP 7.4, Node 12
- **DB Local:** MySQL sin MAMP/Herd DB manager
- **Deploy:** FTP manual a servidor de producción
- **Restricciones:** NO actualizar dependencias por compatibilidad

---

*Última actualización: Proyecto legacy estable - mantener compatibilidad*