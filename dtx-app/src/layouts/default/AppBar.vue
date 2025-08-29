<template>
  <v-app-bar
    id="default-app-bar"
    app
    absolute
    class="v-bar--underline"
    color="transparent"
    :clipped-left="$vuetify.rtl"
    :clipped-right="!$vuetify.rtl"
    height="70"
    flat
  >
    <v-app-bar-nav-icon
      class="hidden-md-and-up"
      @click="drawer = !drawer"
    />

    <default-drawer-toggle class="hidden-sm-and-down" />

    <v-toolbar-title
      class="font-weight-light text-h5"
      v-text="name"
    />

    <v-spacer />
    {{ userInfo ? userInfo.lastname : null }}
    <!-- <default-notifications /> -->
    <default-logout />
  </v-app-bar>
</template>

<script>
  // Utilities
  import { get, sync } from 'vuex-pathify';

  export default {
    name: 'DefaultBar',
    components: {
      DefaultDrawerToggle: () => import('./widgets/DrawerToggle'), /* webpackChunkName: "default-drawer-toggle" */
      DefaultLogout: () => import('./widgets/Logout'), /* webpackChunkName: "default-logout" */
      // DefaultNotifications: () => import('./widgets/Notifications'), /* webpackChunkName: "default-notifications" */
    },
    computed: {
      ...sync('app', [
        'drawer',
        'mini',
      ]),
      ...get('session', [
        'userInfo',
      ]),
      name: get('route/name'),
    },
  };
</script>
