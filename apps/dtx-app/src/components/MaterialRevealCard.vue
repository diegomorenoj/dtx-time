<template>
  <v-hover v-model="hover">
    <material-card
      :class="[
        ($slots.actions && hover) && 'v-card--material-reveal--hover',
        centered && 'text-center'
      ]"
      :title="title"
      class="v-card--material-reveal"
      reveal
      v-bind="$attrs"
      v-on="$listeners"
    >
      <template #heading>
        <slot name="heading" />
      </template>

      <template #subtitle>
        <slot name="subtitle" />
      </template>

      <template #title>
        <div
          :class="[!$slots['reveal-actions'] ? 'mt-n2' : 'mt-n12']"
          class="mb-4 v-card--material-reveal__actions"
        >
          <slot name="reveal-actions" />
        </div>
      </template>

      <v-card-text
        class="px-4 px-lg-7 pt-0 pb-0 mt-n2"
      >
        <slot />
      </v-card-text>

      <template #actions>
        <div
          v-if="$slots.actions"
          class="py-2 grow d-flex align-center"
        >
          <slot name="actions" />
        </div>
      </template>
    </material-card>
  </v-hover>
</template>

<script>
  export default {
    name: 'MaterialRevealCard',

    inheritAttrs: false,

    props: {
      centered: Boolean,
      title: String,
    },

    data: () => ({ hover: false }),
  };
</script>

<style lang="sass">
  .v-card.v-card--material.v-card--material-reveal
    > .v-card__title
      > .v-card--material__title
        padding-left: 0 !important

      > .v-card--material__sheet
        z-index: 1

    &.v-card--material-reveal--hover
      > .v-card__title > .v-sheet
        transform: translateY(-48px)

    .v-card--material-reveal__actions
      text-align: center

      .v-icon
        font-size: 1.125rem
</style>
