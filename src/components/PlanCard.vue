<template>
  <v-card
    :color="!plan.best ? 'transparent elevation-0' : undefined"
    :light="plan.best"
    class="v-card--plan mx-auto pt-3 pb-4 px-2 text-center"
    max-width="100%"
  >
    <div
      class="text-body-2 text-uppercase grey--text mb-10"
      v-text="plan.heading"
    />

    <v-avatar
      class="mb-8"
      size="130"
    >
      <v-icon
        :color="!plan.best ? undefined : 'primary'"
        size="64"
        v-text="plan.icon"
      />
    </v-avatar>

    <h2
      class="text-h3 font-weight-light"
      v-text="plan.title"
    />

    <v-card-text
      class="text-body-1 font-weight-light pa-1 mb-12"
      v-text="plan.text"
    />

    <v-btn
      :color="!plan.best ? 'white' : 'primary'"
      :light="!plan.best"
      min-width="140"
      rounded
    >
      <i18n path="plan" />
    </v-btn>
  </v-card>
</template>

<script>
  export default {
    name: 'PlanCard',

    props: {
      plan: {
        type: Object,
        default: () => ({}),
      },
    },
  }
</script>

<style lang="sass">
.v-card--plan
  .v-avatar
    border: 1px solid #E5E5E5
</style>
