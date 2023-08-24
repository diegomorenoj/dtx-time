<template>
  <v-container
    id="buttons-view"
    fluid
    tag="section"
  >
    <view-intro
      heading="Vuetify Buttons"
      link="components/buttons"
    />

    <app-card class="pa-4">
      <v-row>
        <v-col
          cols="12"
          md="6"
        >
          <div class="text-h5 mb-6">
            Pick your Color
          </div>

          <app-btn
            v-for="(color, i) in colors"
            :key="i"
            :color="color"
            class="mr-2 mb-2"
          >
            {{ color }}
          </app-btn>
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <div class="text-h5 mb-6">
            Buttons with Label
          </div>

          <app-btn
            v-for="(color, i) in colors"
            :key="i"
            :color="color"
            class="mr-2 mb-2"
          >
            <v-icon
              left
              v-text="icons[i]"
            />

            {{ color }}
          </app-btn>
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <div class="text-h5 mb-6">
            Pick your Size
          </div>

          <app-btn
            v-for="(size, i) in sizes"
            :key="i"
            class="mr-2"
            color="secondary"
            v-bind="{ [size]: true }"
          >
            {{ size }}
          </app-btn>
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <div class="text-h5 mb-6">
            Pick your Style
          </div>

          <app-btn
            v-for="(style, i) in styles"
            :key="i"
            class="mr-2 mb-2"
            color="secondary"
            v-bind="style.attrs"
          >
            <v-icon
              v-if="style.icon"
              :left="!!style.text"
              v-text="style.icon"
            />

            {{ style.text }}
          </app-btn>
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <div class="text-h5 mb-6">
            Pagination
          </div>

          <div class="d-flex flex-column align-start">
            <v-pagination
              :value="1"
              :length="5"
              circle
            />

            <v-pagination
              :value="2"
              :length="5"
              circle
            />
          </div>
        </v-col>

        <v-col
          cols="12"
          md="6"
        >
          <div class="text-h5 mb-6">
            Button Group
          </div>

          <v-btn-toggle
            v-for="(group, i) in groups"
            :key="i"
            :rounded="i !== 0"
            color="teal"
            class="mb-2"
          >
            <v-btn
              v-for="text in group"
              :key="text"
            >
              {{ text }}
            </v-btn>
          </v-btn-toggle>
        </v-col>

        <v-col cols="12">
          <div class="text-h5 mb-6">
            Social buttons
          </div>

          <div
            v-for="(s, i) in social"
            :key="i"
          >
            <div class="d-flex">
              <div
                v-for="n in 5"
                :key="n"
                :style="n === 1 && 'width: 250px'"
                class="mr-2 mb-2"
              >
                <app-btn
                  :color="s.color"
                  dark
                  v-bind="{
                    fab: n === 3,
                    small: n === 3,
                    text: n === 5,
                    icon: n === 4,
                  }"
                >
                  <v-icon
                    v-if="s.icon"
                    :left="n === 1 || n === 5"
                    v-text="s.icon"
                  />

                  <span
                    v-if="n === 1 || n === 5"
                    v-text="s.text"
                  />
                </app-btn>
              </div>
            </div>
          </div>
        </v-col>
      </v-row>
    </app-card>
  </v-container>
</template>

<script>
  export default {
    name: 'DashboardButtons',

    data: () => ({
      colors: [
        'default',
        'secondary',
        'info',
        'success',
        'warning',
        'error',
      ],
      groups: [
        ['left', 'middle', 'right'],
        [1, 2, 3, 4],
        [5, 6, 7],
      ],
      icons: [
        'mdi-chevron-left',
        'mdi-chevron-right',
        'mdi-exclamation',
        'mdi-check',
        'mdi-alert',
        'mdi-close',
      ],
      sizes: [
        'small',
        'regular',
        'large',
      ],
      social: [
        {
          color: '#55ACEE',
          icon: 'mdi-twitter',
          text: 'Connect with Twitter',
        },
        {
          color: '#3B5998',
          icon: 'mdi-facebook',
          text: 'Share - 2.2K',
        },
        {
          color: '#DD4b39',
          icon: 'mdi-google-plus',
          text: 'Share on Google+',
        },
        {
          color: '#0976B4',
          icon: 'mdi-linkedin',
          text: 'Connect with LinkedIn',
        },
        {
          color: '#CC2127',
          icon: 'mdi-pinterest',
          text: 'Pint It - 212',
        },
        {
          color: '#E52D27',
          icon: 'mdi-youtube',
          text: 'View on Youtube',
        },
        {
          color: '#333333',
          icon: 'mdi-github',
          text: 'Connect with Github',
        },
        {
          color: '#FF4500',
          icon: 'mdi-reddit',
          text: 'Repost - 232',
        },
      ],
      styles: [
        {
          attrs: {},
          text: 'Default',
        },
        {
          attrs: { rounded: true },
          text: 'Rounded',
        },
        {
          attrs: { rounded: true },
          text: 'Default',
          icon: 'mdi-heart',
        },
        {
          attrs: { fab: true, small: true },
          text: '',
          icon: 'mdi-heart',
        },
        {
          attrs: { text: true },
          text: 'Simple',
        },
      ],
    }),
  }
</script>
