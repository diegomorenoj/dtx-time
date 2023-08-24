<template>
  <v-app-bar
    :absolute="$vuetify.breakpoint.mdAndUp"
    color="transparent"
    dark
    flat
    height="80"
  >
    <v-container class="px-0 d-flex align-center">
      <v-toolbar-title
        class="font-weight-light hidden-xs-only"
        v-text="`${name} Page`"
      />

      <v-spacer />

      <v-btn
        v-for="(item, i) in items"
        :key="i"
        :to="item.to"
        class="ml-1 ml-md-7"
        min-height="48"
        min-width="40"
        text
      >
        <v-icon
          :left="$vuetify.breakpoint.mdAndUp"
          size="20"
          v-text="item.icon"
        />

        <span
          class="hidden-sm-and-down"
          v-text="item.text"
        />
      </v-btn>
    </v-container>
  </v-app-bar>
</template>

<script>
  // Utilities
  import { get } from 'vuex-pathify'

  export default {
    name: 'PageAppBar',

    data: () => ({
      items: [
        {
          icon: 'mdi-view-dashboard',
          text: 'Home',
          to: '/',
        },
        {
          icon: 'mdi-currency-usd',
          text: 'Pricing',
          to: '/pages/pricing/',
        },
        {
          icon: 'mdi-account-multiple-plus',
          text: 'Register',
          to: '/pages/register/',
        },
        {
          icon: 'mdi-fingerprint',
          text: 'Login',
          to: '/pages/login/',
        },
        {
          icon: 'mdi-lock-open-outline',
          text: 'Lock',
          to: '/pages/lock/',
        },
      ],
    }),

    computed: { name: get('route/name') },
  }
</script>
