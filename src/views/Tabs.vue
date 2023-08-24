<template>
  <v-container
    id="tabs-view"
    fluid
    tag="section"
  >
    <view-intro
      heading="Tabs System"
      link="components/tabs"
    />

    <v-row>
      <v-col
        cols="12"
        md="6"
      >
        <app-card class="pa-4">
          <div class="text-h5 mb-6">
            Navigation Pills
            <span class="grey--text text-body-2">- Horizontal Tabs</span>
          </div>
          <app-tabs
            color="warning"
          >
            <template v-for="(tab, i) in items1">
              <v-tab
                :key="i"
              >
                {{ tab }}
              </v-tab>

              <v-tab-item
                :key="`tab-item-${i}`"
              >
                <p
                  v-for="k in 2"
                  :key="k"
                  class="mt-4 text-body-2 font-weight-light"
                  v-text="paragraphs.slice(i * 2, i * 2 + 2)[k - 1]"
                />
              </v-tab-item>
            </template>
          </app-tabs>
        </app-card>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <app-card class="pa-4">
          <div class="text-h5 mb-6">
            Navigation Pills
            <span class="grey--text text-body-2">- Vertical Tabs</span>
          </div>
          <app-tabs
            color="accent"
            vertical
          >
            <template v-for="(tab, i) in items1">
              <v-tab
                :key="i"
              >
                {{ tab }}
              </v-tab>

              <v-tab-item :key="`tab-item-${i}`">
                <p
                  v-for="k in 2"
                  :key="k"
                  class="ml-4 text-body-2 font-weight-light"
                  v-text="paragraphs.slice(i * 2, i * 2 + 2)[k - 1]"
                />
              </v-tab-item>
            </template>
          </app-tabs>
        </app-card>
      </v-col>

      <v-col
        cols="12"
        md="6"
      >
        <app-card>
          <div class="text-h5 mb-6 px-4 pt-4">
            Collapsible Accordion
          </div>
          <v-expansion-panels
            :value="0"
            flat
          >
            <v-expansion-panel
              v-for="n in 5"
              :key="n"
            >
              <v-expansion-panel-header
                class="text-body-2 font-weight-light"
              >
                Collapsible Group Item #{{ n }}
              </v-expansion-panel-header>

              <v-expansion-panel-content class="text-body-2 font-weight-light">
                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
              </v-expansion-panel-content>
            </v-expansion-panel>
          </v-expansion-panels>
        </app-card>
      </v-col>

      <v-col
        class="mb-12"
        cols="12"
        md="6"
      >
        <app-card class="pa-4">
          <div class="text-h5 mb-6">
            Navigation Pills Icons
            <span class="grey--text text-body-2">- Vertical Tabs</span>
          </div>
          <app-tabs
            color="accent"
            icons-and-text
            vertical
          >
            <template v-for="(tab, i) in items2">
              <v-tab
                :key="i"
                class="mb-5"
              >
                {{ tab.text }}

                <v-icon v-text="tab.icon" />
              </v-tab>

              <v-tab-item :key="`tab-item-${i}`">
                <p
                  v-for="k in 2"
                  :key="k"
                  class="ml-4 text-body-2 font-weight-light"
                  v-text="paragraphs.slice(i * 2, i * 2 + 2)[k - 1]"
                />
              </v-tab-item>
            </template>
          </app-tabs>
        </app-card>
      </v-col>
    </v-row>

    <v-responsive
      max-width="1200"
      class="mx-auto"
    >
      <div class="text-center my-10">
        <div class="text-h3 mb-6">
          Page Subcategories
        </div>
        <app-tabs
          color="warning"
          centered
          background-color="transparent"
          icons-and-text
        >
          <template v-for="(tab, i) in (n === 1 ? items2 : items3)">
            <v-tab
              :key="i"
              class="mb-5"
            >
              {{ tab.text }}

              <v-icon v-text="tab.icon" />
            </v-tab>

            <v-tab-item
              :key="`tab-item-${i}`"
              class="pa-4 text-body-2 font-weight-light"
            >
              <p
                v-for="k in 2"
                :key="k"
                v-text="paragraphs.slice(i * 2, i * 2 + 2)[k - 1]"
              />
            </v-tab-item>
          </template>
        </app-tabs>
      </div>
    </v-responsive>
  </v-container>
</template>

<script>
  export default {
    name: 'TabsView',

    data: () => ({
      paragraphs: [
        'Collaboratively administrate empowered markets via plug-and-play networks. Dynamically procrastinate B2C users after installed base benefits.',
        'Dramatically visualize customer directed convergence without revolutionary ROI. Collaboratively administrate empowered markets via plug-and-play networks. Dynamically procrastinate B2C users after installed base benefits.',
        'Efficiently unleash cross-media information without cross-media value. Quickly maximize timely deliverables for real-time schemas.',
        'Dramatically maintain clicks-and-mortar solutions without functional solutions.',
        'Completely synergize resource taxing relationships via premier niche markets. Professionally cultivate one-to-one customer service with robust ideas.',
        'Dynamically innovate resource-leveling customer service for state of the art customer service.',
        'From the seamless transition of glass and metal to the streamlined profile, every detail was carefully considered to enhance your experience. So while its display is larger, the phone feels just right.',
        'Another Text. The first thing you notice when you hold the phone is how great it feels in your hand. The cover glass curves down around the sides to meet the anodized aluminum enclosure in a remarkable, simplified design.',
      ],
      items1: [
        'Profile',
        'Settings',
        'Options',
      ],
      items2: [
        {
          text: 'Home',
          icon: 'mdi-view-dashboard',
        },
        {
          text: 'Settings',
          icon: 'mdi-clock-outline',
        },
      ],
      items3: [
        {
          text: 'Description',
          icon: 'mdi-information',
        },
        {
          text: 'Location',
          icon: 'mdi-map-marker',
        },
        {
          text: 'Legal Info',
          icon: 'mdi-gavel',
        },
        {
          text: 'Help Center',
          icon: 'mdi-help-circle-outline',
        },
      ],
    }),
  }
</script>
