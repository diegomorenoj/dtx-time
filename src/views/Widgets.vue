<template>
  <v-container
    id="widgets-view"
    fluid
    tag="section"
  >
    <view-intro heading="Widgets">
      Below is a collection of component examples for Vuetify Material Dashboard
    </view-intro>

    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <material-card color="orange">
          <template #heading>
            <div class="pa-8 white--text">
              <div class="text-h4 font-weight-light">
                Employees Stats
              </div>
              <div class="text-caption">
                New employees on 15th September, 2016
              </div>
            </div>
          </template>
          <v-card-text>
            <v-data-table
              :headers="headers"
              :items="items"
            />
          </v-card-text>
        </material-card>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <material-card color="accent">
          <template #heading>
            <v-tabs
              v-model="tabs"
              :background-color="$vuetify.dark ? 'white' : 'transparent'"
              slider-color="white"
              class="pa-8"
            >
              <span
                class="subheading font-weight-light mx-3"
                style="align-self: center"
              >Tasks:</span>
              <v-tab class="mr-3">
                <v-icon class="mr-2">
                  mdi-bug
                </v-icon>
                Bugs
              </v-tab>
              <v-tab class="mr-3">
                <v-icon class="mr-2">
                  mdi-code-tags
                </v-icon>
                Website
              </v-tab>
              <v-tab>
                <v-icon class="mr-2">
                  mdi-cloud
                </v-icon>
                Server
              </v-tab>
            </v-tabs>
          </template>
          <v-tabs-items
            v-model="tabs"
            background-color="transparent"
          >
            <v-tab-item
              v-for="n in 3"
              :key="n"
            >
              <v-card-text>
                <template v-for="(task, i) in tasks[tabs]">
                  <v-row
                    :key="i"
                    align="center"
                    class="flex-nowrap"
                  >
                    <v-col cols="1">
                      <v-list-item-action>
                        <v-simple-checkbox
                          v-model="task.value"
                          color="secondary"
                        />
                      </v-list-item-action>
                    </v-col>

                    <v-col
                      class="font-weight-light"
                      cols="8"
                      v-text="task.text"
                    />

                    <v-col
                      cols="auto"
                      class="text-right"
                    >
                      <v-icon class="mx-1">
                        mdi-pencil
                      </v-icon>

                      <v-icon
                        class="mx-1"
                        color="error"
                      >
                        mdi-close
                      </v-icon>
                    </v-col>
                  </v-row>
                </template>
              </v-card-text>
            </v-tab-item>
          </v-tabs-items>
        </material-card>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <v-row>
          <template v-for="(plan, i) in plans">
            <v-col
              :key="i"
              cols="12"
              md="6"
            >
              <plan-card :plan="plan" />
            </v-col>
          </template>

          <v-col cols="12">
            <testimony-card
              blurb="Lorem ipsum dolor sit amet consectetur adipisicing elit. Debitis doloremque sed ipsum, necessitatibus vitae blanditiis molestias natus officiis doloribus fuga."
              author="John Leider"
              handle="@vuetifyjs"
            />
          </v-col>
        </v-row>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <v-timeline
          align-top
          dense
        >
          <v-timeline-item
            v-for="(timeline, i) in timelines"
            :key="i"
            :color="colors[i]"
            :icon="timeline.icon"
            fill-dot
            large
          >
            <app-card class="pa-6">
              <v-chip
                :color="colors[i]"
                class="text-overline mb-3"
                small
              >
                {{ timeline.title }}
              </v-chip>

              <p
                class="text-subtitle-1"
                v-text="timeline.text"
              />

              <div
                class="text-uppercase text-body-2"
                v-text="timeline.subtext"
              />

              <template v-if="timeline.action">
                <v-divider class="mb-3" />

                <v-menu
                  v-model="menu"
                  bottom
                  offset-y
                  origin="top right"
                  left
                  transition="scale-transition"
                >
                  <template v-slot:activator="{ attrs, on }">
                    <app-btn
                      :color="timeline.action"
                      rounded
                      v-bind="attrs"
                      v-on="on"
                    >
                      <v-icon
                        left
                        v-text="timeline.aicon"
                      />

                      <v-icon right>
                        mdi-menu-{{ menu ? 'up' : 'down' }}
                      </v-icon>
                    </app-btn>
                  </template>

                  <v-sheet>
                    <v-list>
                      <v-list-item
                        v-for="a in timeline.actions"
                        :key="a"
                        link
                      >
                        <v-list-item-title v-text="a" />
                      </v-list-item>
                    </v-list>
                  </v-sheet>
                </v-menu>
              </template>
            </app-card>
          </v-timeline-item>
        </v-timeline>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  export default {
    name: 'WidgetsView',

    data: () => ({
      colors: [
        'error',
        'success',
        'info',
      ],
      headers: [
        {
          sortable: false,
          text: 'ID',
          value: 'id',
        },
        {
          sortable: false,
          text: 'Name',
          value: 'name',
        },
        {
          sortable: false,
          text: 'Salary',
          value: 'salary',
          align: 'right',
        },
        {
          sortable: false,
          text: 'Country',
          value: 'country',
          align: 'right',
        },
        {
          sortable: false,
          text: 'City',
          value: 'city',
          align: 'right',
        },
      ],
      items: [
        {
          id: 1,
          name: 'Dakota Rice',
          country: 'Niger',
          city: 'Oud-Tunrhout',
          salary: '$35,738',
        },
        {
          id: 2,
          name: 'Minerva Hooper',
          country: 'Curaçao',
          city: 'Sinaai-Waas',
          salary: '$23,738',
        },
        {
          id: 3,
          name: 'Sage Rodriguez',
          country: 'Netherlands',
          city: 'Overland Park',
          salary: '$56,142',
        },
        {
          id: 4,
          name: 'Philip Chanley',
          country: 'Korea, South',
          city: 'Gloucester',
          salary: '$38,735',
        },
        {
          id: 5,
          name: 'Doris Greene',
          country: 'Malawi',
          city: 'Feldkirchen in Kārnten',
          salary: '$63,542',
        },
      ],
      menu: false,
      plans: [
        {
          best: true,
          heading: 'Small Company',
          icon: 'mdi-home',
          title: '$29',
          text: 'This is good if your company size is between 2 and 10 Persons.',
        },
        {
          heading: 'Freelancer',
          icon: 'mdi-sofa',
          title: 'FREE',
          text: 'This is good if your company size is between 2 and 10 Persons.',
        },
      ],
      tabs: 0,
      tasks: {
        0: [
          {
            text: 'Sign contract for "What are conference organizers afraid of?"',
            value: true,
          },
          {
            text: 'Lines From Great Russian Literature? Or E-mails From My Boss?',
            value: false,
          },
          {
            text: 'Flooded: One year later, assessing what was lost and what was found when a ravaging rain swept through metro Detroit',
            value: false,
          },
          {
            text: 'Create 4 Invisible User Experiences you Never Knew About',
            value: true,
          },
        ],
        1: [
          {
            text: 'Flooded: One year later, assessing what was lost and what was found when a ravaging rain swept through metro Detroit',
            value: true,
          },
          {
            text: 'Sign contract for "What are conference organizers afraid of?"',
            value: false,
          },
        ],
        2: [
          {
            text: 'Lines From Great Russian Literature? Or E-mails From My Boss?',
            value: false,
          },
          {
            text: 'Flooded: One year later, assessing what was lost and what was found when a ravaging rain swept through metro Detroit',
            value: true,
          },
          {
            text: 'Sign contract for "What are conference organizers afraid of?"',
            value: true,
          },
        ],
      },
      timelines: [
        {
          title: 'Some title',
          icon: 'mdi-briefcase',
          text: 'Wifey made the best Father\'s Day meal ever. So thankful so happy so blessed. Thank you for making my family We just had fun with the “future” theme !!! It was a fun night all together ... The always rude Kanye Show at 2am Sold Out Famous viewing @ Figueroa and 12th in downtown.',
          subtext: '11 hours ago via twitter',
        },
        {
          title: 'Another one',
          icon: 'mdi-puzzle',
          text: 'Thank God for the support of my wife and real friends. I also wanted to point out that it’s the first album to go number 1 off of streaming!!! I love you Ellen and also my number one design rule of anything I do from shoes to music to homes is that Kim has to like it....',
        },
        {
          title: 'Another title',
          icon: 'mdi-fingerprint',
          text: 'Called I Miss the Old Kanye That’s all it was Kanye And I love you like Kanye loves Kanye Famous viewing @ Figueroa and 12th in downtown LA 11:10PM. What if Kanye made a song about Kanye Royère doesn\'t make a Polar bear bed but the Polar bear couch is my favorite piece of furniture we own It wasn’t any Kanyes Set on his goals Kanye',
          action: 'info',
          aicon: 'mdi-wrench',
          actions: [
            'Action',
            'Another Action',
            'Something else here',
          ],
        },
      ],
    }),
  }
</script>
