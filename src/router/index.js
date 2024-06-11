// Imports
import Vue from 'vue';
import Router from 'vue-router';
import { trailingSlash } from '@/util/helpers';
import { layout, route } from '@/util/routes';
import store from '@/store';

Vue.use(Router);

const router = new Router({
  mode: 'history',
  base: process.env.BASE_URL,
  scrollBehavior: (to, from, savedPosition) => {
    if (to.hash) return { selector: to.hash };
    if (savedPosition) return savedPosition;

    return { x: 0, y: 0 };
  },
  routes: [
    layout('Default', [
      route('Informe Individual'),
      route('Informe Global', null, 'dashboard'),
      // USUARIOS
      route('Usuarios', null, 'config/users'),
      // CAPACIOTACIONES EXTERNAS
      route('Capacitaciones Externas', null, 'config/trainingrequests'),
      // PLATAFORMAS
      route('Plataformas', null, 'config/providers'),
      // CURSOS
      route('Cursos', null, 'config/courses'),
      // PRESUPUESTO
      route('Presupuesto', null, 'config/budget'),
      // OBJETIVOS
      route('Objetivos Generales', null, 'objectives/general'),
      route('Informe Objetivos', null, 'objectives/report'),
      // ESPECIALIDADES
      route('Especialidades', null, 'config/specialties'),
      // INSTRUCTORES
      route('Informe Instructores', null, 'instructores/report'),
    ]),
    layout('Page', [
      route('Error', null, 'error'),
      route('Lock', null, 'lock'),
      route('Login', null, 'login'),
      route('Pricing', null, 'pricing'),
      route('Register', null, 'register'),
    ], '/'),
  ],
});

router.beforeEach((to, from, next) => {
  // Verifica si el usuario está autenticado utilizando localStorage
  const isLogged = localStorage.getItem('session@logged') === 'true';

  // Si el usuario no está autenticado y la ruta a la que intenta acceder no es la página de inicio de sesión, redirige a la página de inicio de sesión
  if (!isLogged && to.path !== '/login') {
    next('/login');
  } else {
    next();
  }
});
export default router;
