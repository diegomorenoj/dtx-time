<template>
  <app-card class="text-center px-4">
    <div class="pt-6">
      <v-icon
        color="black"
        x-large
      >
        mdi-format-quote-close
      </v-icon>
    </div>

    <v-card-text
      class="text-h5 font-italic mb-2 text--secondary"
      v-text="blurb"
    />

    <div
      class="text-h4"
      v-text="author"
    />

    <div
      class="text-h6 text--secondary mt-2 mb-2"
      v-text="handle"
    />

    <v-avatar
      class="elevation-12 mb-n10 v-sheet theme--light"
      size="100"
    >
      <v-img
        :alt="`${author} Testimonial`"
        :src="avatar"
        class="mb-n3"
      />
    </v-avatar>

    <div />
  </app-card>
</template>

<script>
  export default {
    name: 'TestimonyCard',

    props: {
      author: String,
      avatar: {
        type: String,
        default: require('@/assets/vmd.svg'),
      },
      blurb: String,
      handle: String,
    },
  };
</script>
