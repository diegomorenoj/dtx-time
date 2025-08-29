<template>
  <v-container
    id="login-view"
    class="fill-height"
    tag="section"
  >
    <v-row justify="center">
      <v-col cols="12">
        <v-slide-y-transition appear>
          <material-card
            light
            max-width="350"
            rounded
            class="mx-auto"
            color="accent"
            full-header
          >
            <v-card-text class="text-center">
              <div class="text-center">
                <v-img
                  key="-1"
                  :src="require('@/assets/logo.jpg')"
                />
              </div>

              <v-text-field
                v-model="user.email"
                color="secondary"
                placeholder="Usuario"
                prepend-icon="mdi-account"
              />

              <v-text-field
                v-model="user.password"
                :type="'password'"
                class="mb-8"
                color="secondary"
                placeholder="Contraseña"
                prepend-icon="mdi-lock-outline"
              />

              <v-btn
                color="primary"
                block
                @click="sendForm()"
              >
                Ingresar
              </v-btn>
              <div
                v-if="message"
                :style="{ color: 'red', fontWeight: 'bold', backgroundColor: '#dfdfdf', marginTop: '10px', padding:'10px', fontSize: '18px' }"
              >
                {{ message }}
              </div>
            </v-card-text>
          </material-card>
        </v-slide-y-transition>
      </v-col>
    </v-row>
    <v-overlay
      class="v-overlay-custom"
      :value="overlay"
    >
      <v-progress-circular
        indeterminate
        size="64"
      />
    </v-overlay>
  </v-container>
</template>

<script>

  import AuthService from '../services/AuthService';
  import { get, sync } from 'vuex-pathify';

  export default {
    name: 'LoginView',

    data: () => ({
      message: '',
      authService: null,
      resp: null,
      user: {
        email: null,
        password: null,
      },
      overlay: false,
    }),
    computed: {
      ...sync('session', [
        'logged',
        'token',
        'userInfo',
      ]),
      ...get('session', [
        'logged',
      ]),
    },
    created () {
      this.authService = new AuthService();
      console.log('VUE_APP_RUTA_API:::::', process.env.VUE_APP_RUTA_API);
    },
    mounted () {
      // validar si ya esta logueado enviar al home
      console.log('session@logged', this.logged);
      if (this.logged === true) this.$router.push('/');
    },
    methods: {
      sendForm () {
        this.overlay = true;
        const body = {
          email: this.user.email,
          password: this.user.password,
        };
        this.authService.login(body).then(data => {
          if (data.error) {
            this.message = 'Usuario ó contraseña incorrectos';
            return;
          }
          localStorage.setItem('session@token', data.token);// guardar en el storage
          localStorage.setItem('session@logged', true);
          localStorage.setItem('session@userInfo', JSON.stringify(data.user));

          this.$store.commit('session/SET_TOKEN', data.token);
          this.$store.commit('session/SET_LOGGED', true);
          this.$store.commit('session/SET_USER_INFO', data.user);
          this.overlay = false;
          this.$router.push('/');
        }).catch((error) => {
          console.error(error);
          this.message = 'Usuario ó contraseña incorrectos';
          this.overlay = false;
        });
      },
    },
  };
</script>
