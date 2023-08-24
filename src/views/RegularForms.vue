<template>
  <v-container
    id="regular-forms-view"
    fluid
    tag="section"
  >
    <view-intro
      heading="Regular Forms"
      link="components/forms"
    />

    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <material-card
          icon="mdi-email-outline"
          icon-small
          title="Stacked Form"
          color="accent"
        >
          <v-card-text>
            <v-form>
              <v-text-field
                v-for="n in 2"
                :key="n"
                class="mb-n3"
                :label="['Email Address', 'Password'][n - 1]"
                :type="[undefined, 'password'][n - 1]"
              />

              <v-checkbox label="Subscribe to newsletter" />
            </v-form>

            <v-btn
              color="accent"
              min-width="100"
            >
              Submit
            </v-btn>
          </v-card-text>
        </material-card>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <material-card
          icon="mdi-contacts"
          icon-small
          title="Horizontal Form"
          color="accent"
        >
          <v-card-text>
            <v-form>
              <v-row no-gutters>
                <v-col
                  cols="5"
                  md="3"
                  class="text-right grey--text"
                >
                  <div
                    v-for="field in ['Email', 'Password']"
                    :key="field"
                    class="py-6 px-3"
                    v-text="field"
                  />
                </v-col>

                <v-col
                  cols="7"
                  md="9"
                >
                  <v-text-field
                    v-for="type in ['text', 'password']"
                    :key="type"
                    :type="type"
                  />

                  <v-checkbox
                    label="Remember me"
                    class="mt-0"
                  />
                </v-col>
              </v-row>
            </v-form>

            <v-btn
              color="accent"
              min-width="100"
            >
              Sign In
            </v-btn>
          </v-card-text>
        </material-card>
      </v-col>

      <v-col cols="12">
        <material-card
          color="accent"
          heading="Form Elements"
        >
          <v-card-text>
            <v-form>
              <div>
                <v-subheader>
                  Text fields
                </v-subheader>

                <v-text-field
                  v-for="n in 2"
                  :key="n"
                  :label="['With help', 'Password'][n - 1]"
                  :type="['password', undefined][n - 1]"
                  outlined
                />
              </div>

              <div>
                <v-subheader>
                  Checkboxes
                </v-subheader>

                <v-row
                  v-for="j in 2"
                  :key="j"
                >
                  <v-col
                    v-for="k in 3"
                    :key="k"
                    cols="2"
                  >
                    <v-checkbox
                      :disabled="j === 2"
                      :indeterminate="k === 3"
                      :input-value="k === 1"
                      :label="`${k === 1 ? 'On' : k === 2 ? 'Off' : 'Indeterminate'}${j === 2 ? ' Disabled' : ''}`"
                    />
                  </v-col>
                </v-row>
              </div>

              <div>
                <v-row>
                  <v-col
                    v-for="n in 2"
                    :key="n"
                    cols="12"
                  >
                    <v-subheader>
                      {{ n === 2 ? 'Inline ' : '' }}Radios
                    </v-subheader>

                    <v-radio-group
                      :column="n === 1"
                      :row="n === 2"
                    >
                      <v-radio
                        v-for="k in 2"
                        :key="k"
                        :label="`${n === 1 ? 'Column' : 'Row'} Option ${n}`"
                        :value="`radio-${n}`"
                      />
                    </v-radio-group>
                  </v-col>
                </v-row>
              </div>

              <div>
                <v-subheader>
                  Switches
                </v-subheader>

                <v-row>
                  <v-col
                    v-for="n in 2"
                    :key="n"
                    cols="2"
                  >
                    <v-switch
                      :input-value="n === 1"
                      :label="`${n === 1 ? 'On' : 'Off'}`"
                    />
                  </v-col>

                  <v-col
                    v-for="k in 2"
                    :key="`${k}-switch`"
                    cols="2"
                  >
                    <v-switch
                      :input-value="k === 1"
                      :label="`${k === 1 ? 'On' : 'Off'} Disabled`"
                      disabled
                    />
                  </v-col>

                  <v-col cols="2">
                    <v-switch
                      input-value="true"
                      label="Loading"
                      loading="primary"
                      value
                    />
                  </v-col>
                </v-row>
              </div>
            </v-form>
          </v-card-text>
        </material-card>
      </v-col>

      <v-col cols="12">
        <material-card
          heading="Input Variants"
          color="accent"
        >
          <v-card-text>
            <v-subheader>
              Selects
            </v-subheader>

            <v-row>
              <v-col
                v-for="n in 4"
                :key="n"
                cols="12"
                sm="6"
              >
                <v-select
                  :items="items"
                  :outlined="n === 3"
                  :filled="n === 2"
                  :solo="n === 4"
                  :label="['Standard', 'Filled style', 'Outlined style', 'Solo field'][n - 1]"
                />
              </v-col>
            </v-row>

            <v-subheader>
              Sliders
            </v-subheader>

            <v-slider
              color="secondary"
              prepend-icon="mdi-volume-plus"
              track-color="secondary lighten-3"
            />

            <v-slider append-icon="mdi-alarm" />

            <v-slider
              v-model="zoom"
              append-icon="mdi-magnify-plus-outline"
              prepend-icon="mdi-magnify-minus-outline"
              @click:append="zoomIn"
              @click:prepend="zoomOut"
            />

            <v-subheader>
              Textareas
            </v-subheader>

            <v-row>
              <v-col
                v-for="n in 4"
                :key="n"
                cols="12"
                sm="6"
              >
                <v-textarea
                  :filled="n === 3"
                  :label="['Default style', 'Solo textarea', 'Filled textarea', 'Outlined textarea'][n - 1]"
                  :outlined="n === 4"
                  :solo="n === 2"
                  value="The Woodman set to work at once, and so sharp was his axe that the tree was soon chopped nearly through."
                />
              </v-col>
            </v-row>
          </v-card-text>
        </material-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  export default {
    name: 'RegularFormsView',

    data: () => ({
      items: ['Foo', 'Bar', 'Fizz', 'Buzz'],
      zoom: 0,
    }),

    methods: {
      zoomIn () {
        this.zoom = (this.zoom + 10) || 100
      },
      zoomOut () {
        this.zoom = (this.zoom - 10) || 0
      },
    },
  }
</script>
