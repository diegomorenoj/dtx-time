# DTX App - Frontend

![version](https://img.shields.io/badge/version-1.0.0-blue.svg)

Frontend para la aplicación DTX-Time desarrollado con Vue.js y Vuetify Material Dashboard.

## Requisitos previos

- Node.js 12.x (recomendado usar NVM para gestionar versiones de Node)
- npm 6.x
- API backend ejecutándose en `http://127.0.0.1:8000`

## Instalación

### 1. Clonar el repositorio

```bash
git clone [URL-del-repositorio]
cd dtx-app
```

### 2. Configurar el archivo .env

Asegúrate de tener el archivo `.env` en la raíz del proyecto con la siguiente configuración:

```
VUE_APP_RUTA_API=http://127.0.0.1:8000/api
VUE_APP_MES_INICIAL=AGOSTO
VUE_APP_MES_FINAL=JULIO
```

Si no existe, créalo manualmente con estos datos.

### 3. Instalar dependencias

Debido a la compatibilidad entre versiones antiguas de Node.js y sistemas operativos modernos, es necesario instalar las dependencias usando:

```bash
npm install --ignore-scripts
```

**IMPORTANTE**: NO uses simplemente `npm install` ya que puede generar errores con `fsevents` en sistemas macOS modernos y la versión de Node.js requerida.

### 4. Iniciar servidor de desarrollo

```bash
npm run serve
```

La aplicación estará disponible en [http://localhost:8080](http://localhost:8080)

## Solución de problemas comunes

### Error con fsevents y node-gyp

Si ves errores como:
```
ValueError: invalid mode: 'rU' while trying to load binding.gyp
gyp ERR! configure error
```

Este es un problema de compatibilidad entre Node.js 12 y macOS moderno. La solución es usar `--ignore-scripts` como se indica en la instalación.

### Error de conexión a la API

Asegúrate de que:
1. El backend API esté ejecutándose en `http://127.0.0.1:8000`
2. El archivo `.env` tenga la configuración correcta de `VUE_APP_RUTA_API`

## Comandos disponibles

- `npm run serve` - Inicia el servidor de desarrollo
- `npm run build` - Genera la versión de producción en la carpeta `dist/`
- `npm run lint` - Ejecuta el linter para verificar la calidad del código

## Tecnologías utilizadas

- Vue.js 2.x
- Vuetify Material Dashboard
- Vuex para gestión de estado
- Vue Router
- Axios para peticiones HTTP

## Estructura de directorios

La aplicación sigue la estructura estándar de un proyecto Vue.js con Vuetify Material Dashboard:

- `public/` - Archivos estáticos
- `src/` - Código fuente
  - `assets/` - Imágenes y recursos
  - `components/` - Componentes reutilizables
  - `layouts/` - Diseños de página
  - `plugins/` - Plugins de Vue.js
  - `router/` - Configuración de rutas
  - `store/` - Gestión de estado con Vuex
  - `views/` - Componentes de página

## Notas importantes

1. Este proyecto usa una versión legacy de Vue.js y dependencias.
2. No se recomienda actualizar las dependencias sin probar exhaustivamente.
3. La versión de Node.js 12.x es requerida para mantener compatibilidad.
4. En nuevos sistemas macOS puede haber problemas con `fsevents` que se resuelven con `--ignore-scripts`.

## Mantenimiento

Para mantener este proyecto en funcionamiento:

1. Usa NVM para mantener la versión correcta de Node.js:
   ```bash
   nvm use 12
   ```
2. Realiza copias de seguridad regulares del archivo `.env`
3. Documenta cualquier cambio o problema encontrado
4. Mantén la API backend actualizada y funcionando

## Archivos de configuración importantes

- `.env` - Variables de entorno (API URL, configuración de meses)
- `.env.production` - Variables de entorno para producción  
- `vue.config.js` - Configuración de Vue CLI
- `.eslintrc.js` - Configuración de ESLint
- `package.json` - Dependencias y scripts del proyecto

## Licencia

Este proyecto utiliza Vuetify Material Dashboard PRO bajo licencia comercial.

## Soporte

Para problemas relacionados con este proyecto DTX-App, documenta los issues encontrados y soluciones aplicadas.
