<template>
  <v-container
    id="extended-forms-view"
    fluid
    tag="section"
  >
    <view-intro
      heading="Date Pickers"
      link="components/date-pickers"
    />

    <v-row>
      <v-col
        v-for="n in 3"
        :key="n"
        cols="12"
        sm="6"
        md="4"
      >
        <material-card
          :title="pickers[n - 1]"
          color="accent"
          icon="mdi-calendar-today"
          icon-small
        >
          <v-card-text>
            <v-menu
              ref="menu"
              v-model="menu[n]"
              :close-on-content-click="false"
              :return-value.sync="date[n]"
              min-width="290px"
              offset-y
              transition="scale-transition"
            >
              <template v-slot:activator="{ on }">
                <v-text-field
                  v-model="date[n]"
                  color="secondary"
                  label="Select date"
                  prepend-icon="mdi-calendar-outline"
                  readonly
                  v-on="on"
                />
              </template>

              <v-date-picker
                v-model="date[n]"
                color="warning"
                :landscape="n === 2"
                scrollable
                @change="() => n === 3 ? $refs.menu[n - 1].save(date[n]) : undefined"
              >
                <v-spacer />

                <v-btn
                  color="secondary"
                  @click="menu[n] = false"
                >
                  Cancel
                </v-btn>

                <v-btn
                  v-if="n !== 3"
                  color="secondary"
                  @click="$refs.menu[n - 1].save(date[n])"
                >
                  OK
                </v-btn>
              </v-date-picker>
            </v-menu>
          </v-card-text>
        </material-card>
      </v-col>

      <v-col cols="12">
        <v-card class="pa-6">
          <v-row>
            <v-col
              cols="12"
              md="6"
            >
              <div>
                <div class="text-h5 mb-3">
                  Switches
                </div>

                <v-switch
                  v-for="n in 2"
                  :key="n"
                  :label="`Toggle is ${n === 1 ? 'on' : 'off'}`"
                  :hide-details="n === 1"
                  :input-value="n === 1"
                />
              </div>

              <div>
                <div class="text-h5 mb-3">
                  Tags
                </div>

                <v-combobox
                  v-model="items"
                  color="secondary"
                  multiple
                >
                  <template v-slot:selection="{ attrs, item, select, selected }">
                    <v-chip
                      v-bind="attrs"
                      :input-value="selected"
                      color="secondary"
                      close
                      small
                      @click="select"
                      @click:close="remove(item)"
                    >
                      {{ item }}
                    </v-chip>
                  </template>
                </v-combobox>
              </div>

              <div>
                <div class="text-h5 mb-3">
                  Progress Bar
                </div>

                <v-progress-linear
                  v-for="n in 3"
                  :key="n"
                  :color="['secondary', 'info', 'warning'][n - 1]"
                  :value="[30, 60, 40][n - 1]"
                  :buffer-value="n === 3 ? 0 : undefined"
                  :stream="n === 3"
                  class="mt-2"
                />
              </div>

              <div>
                <v-subheader class="mb-6">
                  File Input
                </v-subheader>
              </div>

              <v-file-input
                :display-size="1000"
                color="deep-purple accent-4"
                counter
                label="File input"
                multiple
                outlined
                placeholder="Select your files"
                prepend-icon="mdi-paperclip"
              >
                <template v-slot:selection="{ index, text }">
                  <v-chip
                    v-if="index < 2"
                    color="deep-purple accent-4"
                    dark
                    label
                    small
                  >
                    {{ text }}
                  </v-chip>

                  <span
                    v-else-if="index === 2"
                    class="text-overline grey--text text--darken-3 mx-2"
                  >
                    +{{ files.length - 2 }} File(s)
                  </span>
                </template>
              </v-file-input>
            </v-col>

            <v-col
              cols="12"
              md="6"
            >
              <div>
                <div class="text-h5 mb-3">
                  Customizable Select
                </div>

                <v-select
                  v-for="n in 2"
                  :key="n"
                  :items="movies"
                  :multiple="n === 2"
                  color="secondary"
                  item-color="secondary"
                  label="Movie"
                  menu-props="offset-y"
                >
                  <template v-slot:item="{ attrs, item, on }">
                    <v-list-item
                      v-bind="attrs"
                      active-class="secondary elevation-4 white--text"
                      class="mx-3 mb-3"
                      elevation="0"
                      v-on="on"
                    >
                      <v-list-item-content>
                        <v-list-item-title v-text="item" />
                      </v-list-item-content>

                      <v-scale-transition v-if="n === 2">
                        <v-list-item-icon
                          v-if="attrs.inputValue"
                          class="my-3"
                        >
                          <v-icon>mdi-check</v-icon>
                        </v-list-item-icon>
                      </v-scale-transition>
                    </v-list-item>
                  </template>
                </v-select>
              </div>

              <div>
                <div class="text-h5 mb-3">
                  Dropdown & Dropup
                </div>

                <div
                  v-for="n in 3"
                  :key="n"
                  class="mb-3"
                >
                  <material-dropdown
                    :items="dropdown"
                    color="success"
                    :origin="[undefined, 'top right', 'bottom right'][n - 1]"
                    :right="n === 2"
                    :top="n === 3"
                  >
                    <template v-if="n !== 3">
                      {{ n === 1 ? 'MultiLevel' : '' }}
                      Dropdown
                    </template>

                    <span v-else>Dropup</span>
                  </material-dropdown>
                </div>
              </div>

              <div class="text-h5 mb-3">
                Sliders
              </div>

              <v-slider color="secondary" />

              <v-range-slider
                :value="[40, 60]"
                color="info"
                track-color="info lighten-4"
              />
            </v-col>
          </v-row>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  export default {
    name: 'DashboardFormsExtendedForms',

    data: () => ({
      date: {
        1: '',
        2: '2019-09-26',
        3: '',
      },
      menu: {
        1: false,
        2: false,
        3: false,
      },
      dropdown: [
        {
          id: 1,
          text: 'Action',
        },
        {
          id: 2,
          text: 'Another Action',
        },
        {
          id: 3,
          text: 'A Third Action',
        },
      ],
      items: [
        'Amsterdam',
        'Washington',
        'Sydney',
        'Beijing',
      ],
      movies: [
        'Fight Club',
        'Godfather',
        'Godfather II',
        'Godfather III',
        'Goodfellas',
        'Pulp Fiction',
        'Scarface',
      ],
      pickers: [
        'Date Picker',
        'Landscape Picker',
        'Time Picker',
      ],
    }),

    methods: {
      remove (item) {
        const index = this.items.indexOf(item);

        if (index >= 0) this.items.splice(index, 1);
      },
    },
  };
</script>
