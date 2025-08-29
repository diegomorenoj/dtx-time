<template>
  <v-main>
    <v-img
      :key="src"
      :src="require(`@/assets/${src}`)"
      dark
      gradient="to top, #00000080, #00000080"
      min-height="100vh"
      :height="$vuetify.breakpoint.mdAndUp ? '100vh' : undefined"
    >
      <div
        :class="[$vuetify.breakpoint.mdAndUp && 'fill-height']"
        class="d-block d-md-flex"
      >
        <router-view />

        <page-footer />
      </div>
    </v-img>
  </v-main>
</template>

<script>
  // Utilities
  import { get } from 'vuex-pathify';

  export default {
    name: 'PageView',

    components: {
      PageFooter: () => import('./Footer'),
    },

    data: () => ({
      srcs: {
        '/pages/lock/': 'lock.jpg',
        '/pages/login/': 'login.jpg',
        '/pages/pricing/': 'pricing.jpg',
        '/pages/register/': 'register.jpg',
      },
    }),

    computed: {
      path: get('route/path'),
      src () {
        return this.srcs[this.path] || 'login.jpg';
      },
    },
  };
</script>
