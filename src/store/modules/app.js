// Pathify
import { make } from 'vuex-pathify';

// Data
const state = {
  drawer: null,
  drawerImage: true,
  mini: false,
  items: [
    {
      title: 'Informe Individual',
      icon: 'mdi-book-open',
      to: '/',
      permit: 'INDIVIDUAL_REPORT',
    },
    {
      title: 'Informe Global',
      icon: 'mdi-view-dashboard',
      to: '/dashboard',
      permit: 'GENERAL_REPORT',
    },
    {
      title: 'Capacitaciones Externas',
      icon: 'mdi-map-marker-radius',
      to: '/config/trainingrequests/',
      permit: 'MENU_TRAININGS',
    },
    {
      title: 'Manual de Aprobaciones',
      icon: 'mdi-book',
      href: '/pdf/manual_aprobacion.pdf',
      permit: 'APPROVAL_MANUAL',
      external: false,
    },
    {
      title: 'Cursos',
      icon: 'mdi-account-school',
      to: '/config/courses',
      permit: 'MENU_COURSES',
    },
    {
      title: 'Objetivos',
      icon: 'mdi-stairs-up',
      to: '/objectives/general',
      permit: 'INDIVIDUAL_REPORT',
    },
    {
      title: 'Especialidades',
      icon: 'mdi-school',
      to: '/config/specialties',
      permit: 'MENU_SPECIALTIES',
    },
    {
      title: 'Presupuesto',
      icon: 'mdi-cash',
      to: '/config/budget',
      permit: 'MENU_BUDGET',
    },
    {
      title: 'Usuarios',
      icon: 'mdi-account-circle',
      to: '/config/users',
      permit: 'MENU_USERS',
    },
    {
      title: 'Instructores',
      icon: 'mdi-account-circle',
      to: '/instructores/report',
      permit: 'MENU_USERS',
    },
    {
      title: 'Plataformas',
      icon: 'mdi-briefcase',
      to: '/config/providers',
      permit: 'MENU_PROVIDERS',
    },
  ],
};

const mutations = make.mutations(state);

const actions = {
  ...make.actions(state),
  init: async ({ dispatch }) => {
    //
  },
};

const getters = {};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
  getters,
};
