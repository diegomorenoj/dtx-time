import Vue from 'vue';
import App from './App.vue';
import router from './router';
import vuetify from './plugins/vuetify';
import './plugins';
import store from './store';
import i18n from './i18n';
import { sync } from 'vuex-router-sync';
import axios from 'axios';
import JsonExcel from 'vue-json-excel';
import { Bar } from 'vue-chartjs/legacy';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

Vue.config.productionTip = false;
Vue.component('downloadExcel', JsonExcel);
Vue.component('chartjsBar', Bar);

sync(store, router);
// ENVIAR EL TOKEN EN TODAS LAS PETICIONES HTTP
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('session@token');
  const _token = store.get('session@token');
  console.log('Token::::::::', _token);
  if (token) {
      config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, err => {
    console.log('err', err);
    return Promise.reject(err);
});

// VALIDAR EL ERROR DEL RESPONSE
axios.interceptors.response.use(
  response => {
    return response;
  },
  error => {
    // console.log('Error:::::', error.response?.data?.message);
    if (error.response?.status === 401 || (error.response?.status === 500 && error.response?.data?.message === 'Token has expired')) {
      store.commit('session/SET_TOKEN', undefined);
      store.commit('session/SET_LOGGED', false);
      store.commit('session/SET_USER_INFO', undefined);
      localStorage.setItem('session@token', undefined);
      localStorage.setItem('session@logged', false);
      localStorage.clear();
      location.href = `${location.origin}/login`;
    }
    return Promise.reject(error);
  },
);

new Vue({
  router,
  vuetify,
  store,
  i18n,
  render: h => h(App),
}).$mount('#app');
