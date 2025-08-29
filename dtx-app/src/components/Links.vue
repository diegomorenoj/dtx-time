<template>
  <v-container>
    <v-row align="center">
      <v-col
        v-for="(link, i) in links"
        :key="i"
        class="text-center"
        cols="6"
        md="auto"
      >
        <a
          :href="link.href"
          class="text-decoration-none text-uppercase text-caption font-weight-regular"
          rel="noopener"
          target="_blank"
          v-text="link.text"
        />
      </v-col>

      <v-spacer class="hidden-sm-and-down" />

      <v-col
        cols="12"
        md="auto"
      >
        <div class="text-body-1 font-weight-light pt-6 pt-md-0 text-center">
          Copyright <v-icon>mdi-copyright</v-icon> Salles Sainz - {{ (new Date()).getFullYear() }} <v-icon>mdi-handshake</v-icon>
          <a
            href="https://www.grantthornton.mx"
            class="text-decoration-none font-weight-bold"
          >Grant Thornton</a>
        </div>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  export default {
    name: 'Links',

    data: () => ({
      links: [
        // {
        //   href: '#',
        //   text: 'About Us',
        // },
        // {
        //   href: '#',
        //   text: 'Blog',
        // },
        // {
        //   href: '#',
        //   text: 'Licenses',
        // },
      ],
    }),
  };
</script>

<style lang="sass" scoped>
  a
    color: inherit !important
</style>
