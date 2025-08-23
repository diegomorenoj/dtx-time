<template>
  <v-container
    id="lock-view"
    class="fill-height text-center"
    tag="section"
  >
    <v-row justify="center">
      <v-col cols="12">
        <v-slide-y-transition appear>
          <v-card
            class="mx-auto"
            light
            max-width="240"
            width="100%"
          >
            <v-card-text>
              <v-avatar
                class="mt-n12 mb-6 elevation-12"
                color="grey darken-4"
                size="88"
              >
                <v-img
                  :src="require('@/assets/vmd.svg')"
                  class="mb-n2"
                  contain
                  height="72px"
                />
              </v-avatar>

              <div
                class="text-h4 mb-3"
                style="color: #3C4858;"
              >
                John Leider
              </div>

              <v-text-field
                class="mb-8"
                color="secondary"
                placeholder="Enter Password"
              />

              <v-btn
                color="primary"
                depressed
                min-width="120"
                rounded
              >
                Unlock
              </v-btn>
            </v-card-text>
          </v-card>
        </v-slide-y-transition>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  export default { name: 'LockView' };
</script>
