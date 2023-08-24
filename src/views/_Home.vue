<template>
  <v-container
    id="home-view"
    fluid
    tag="section"
  >
    <v-row>
      <div class="text-h3">
        Hola <span class="font-weight-bold">{{ userInfo.lastname }}</span>, te damos la bienvenida a nuestro sistema de control de tiempos de capacitación de Salles Sainz Grant Thornton.
      </div>
    </v-row>
  </v-container>
</template>

<script>
  import { get } from 'vuex-pathify';
  import AuthService from '../services/AuthService';
  export default {
    name: 'HomeView',
    data: () => ({
      msg: null,
      authService: null,
    }),
    computed: {
      ...get('session', [
        'userInfo',
      ]),
    },
    created () {
      this.authService = new AuthService();
    },
    mounted () {
      this.authService.start().then(response => {
        console.log(response);
      }).catch((error) => {
        console.log(error);
      });
    },
  };
</script>
