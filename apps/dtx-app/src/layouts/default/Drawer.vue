<template>
  <v-navigation-drawer
    id="default-drawer"
    v-model="drawer"
    :dark="dark"
    :right="$vuetify.rtl"
    :src="drawerImage ? image : ''"
    :mini-variant.sync="mini"
    mini-variant-width="80"
    app
    width="260"
  >
    <template
      v-if="drawerImage"
      #img="props"
    >
      <v-img
        :key="image"
        :gradient="gradient"
        v-bind="props"
      />
    </template>

    <div class="px-1">
      <default-drawer-header />

      <v-divider class="mx-3 mb-2" />

      <default-list :items="menu" />
    </div>

    <div class="pt-12" />
  </v-navigation-drawer>
</template>

<script>
  // Utilities
  import { get, sync } from 'vuex-pathify';

  export default {
    name: 'DefaultDrawer',
    components: {
      DefaultDrawerHeader: () => import('./widgets/DrawerHeader'), /* webpackChunkName: "default-drawer-header" */
      DefaultList: () => import('./List'), /* webpackChunkName: "default-list" */
    },
    data: () => ({
      menu: [],
    }),
    computed: {
      ...get('user', [
        'dark',
        'gradient',
        'image',
      ]),
      ...get('app', [
        'items',
        'version',
      ]),
      ...get('session', [
        'userInfo',
      ]),
      ...sync('app', [
        'drawer',
        'drawerImage',
        'mini',
      ]),
    },
    created () {
      console.log('Menu::::', this.items);
      console.log('userInfo::::', this.userInfo);
      // VALIDAR LOS PERMISOS AL MENÚ
      this.items.forEach(item => {
        if (this.havePermit(item, this.userInfo.permits.MENU)) this.menu.push(item);
      });
    },
    methods: {
      havePermit (option, permits) {
        const found = permits.find(p => p === option.permit);
        console.log('havePermit', found !== undefined);
        return found !== undefined;
      },
    },
  };
</script>

<style lang="sass">
#default-drawer
  .v-list-item
    margin-bottom: 8px

  .v-list-item::before,
  .v-list-item::after
    display: none

  .v-list-group__header__prepend-icon,
  .v-list-item__icon
    margin-top: 12px
    margin-bottom: 12px
    margin-left: 4px

  &.v-navigation-drawer--mini-variant
    .v-list-item
      justify-content: flex-start !important
</style>
