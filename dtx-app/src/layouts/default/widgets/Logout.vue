<template>
  <v-btn
    class="ml-2"
    min-width="0"
    icon
    @click="logout()"
  >
    <v-icon>mdi-power</v-icon>
  </v-btn>
</template>

<script>
  import AuthService from '../../../services/AuthService';
  export default {
    name: 'DefaultLogout',
    data: () => ({
      authService: null,
    }),
    created () {
      this.authService = new AuthService();
    },
    methods: {
      logout () {
        this.authService.logout().then(data => {
          localStorage.setItem('session@token', undefined);// guardar en el storage
          localStorage.setItem('session@logged', false);
          localStorage.clear();
          location.href = `${location.origin}/login`;
        });
      },
    },
  };
</script>
