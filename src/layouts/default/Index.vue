<template>
  <v-app>
    <default-bar />

    <default-drawer />

    <default-view />

    <default-footer />
  </v-app>
</template>

<script>
  export default {
    name: 'DefaultLayout',

    components: {
      DefaultBar: () => import('./AppBar'), /* webpackChunkName: "default-appp-bar" */
      DefaultDrawer: () => import('./Drawer'), /* webpackChunkName: "default-drawer" */
      DefaultFooter: () => import('./Footer'), /* webpackChunkName: "default-footer" */
      DefaultView: () => import('./View'), /* webpackChunkName: "default-view" */
    },
  };
</script>
