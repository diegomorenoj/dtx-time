<template>
  <v-menu
    v-model="menu"
    :transition="transition"
    offset-y
    v-bind="$attrs"
  >
    <template #activator="{ attrs, on }">
      <v-btn
        :color="color"
        min-width="200"
        rounded
        v-bind="attrs"
        v-on="on"
      >
        <slot />

        <v-icon>
          mdi-menu-{{ menu ? 'up' : 'down' }}
        </v-icon>
      </v-btn>
    </template>

    <v-sheet>
      <v-list dense>
        <v-list-item
          v-for="(item, i) in items"
          :key="i"
          link
        >
          <v-list-item-content>
            <v-list-item-title v-text="item.text" />
          </v-list-item-content>
        </v-list-item>
      </v-list>
    </v-sheet>
  </v-menu>
</template>

<script>
  export default {
    name: 'MaterialDropdown',

    props: {
      color: {
        type: String,
        default: 'primary',
      },
      items: {
        type: Array,
        default: () => ([
          {
            id: undefined,
            text: undefined,
          },
        ]),
      },
      transition: {
        type: String,
        default: 'scale-transition',
      },
    },

    data: () => ({ menu: false }),
  }
</script>
