<template>
  <v-container
    id="dashboard-view"
    fluid
    tag="section"
  >
    <v-row>
      <v-col cols="12">
        <material-card
          color="success"
          icon="mdi-earth"
          icon-small
          inline
          title="Ventas globales por ubicaciones principales"
        >
          <v-row class="ma-0">
            <v-col
              cols="12"
              md="6"
              class="mt-10"
            >
              <v-simple-table
                class="ml-2"
              >
                <tbody>
                  <tr
                    v-for="(sale, i) in sales"
                    :key="i"
                  >
                    <td>
                      <v-img
                        :src="sale.flag"
                        width="18"
                      />
                    </td>
                    <td v-text="sale.country" />
                    <td
                      class="text-right"
                      v-text="sale.salesInM"
                    />
                    <td
                      class="text-right"
                      v-text="((sale.salesInM / totalSales) * 100).toFixed(2) + '%'"
                    />
                  </tr>
                </tbody>
              </v-simple-table>
            </v-col>

            <v-col
              cols="12"
              md="6"
            >
              <v-responsive max-height="400">
                <v-world-map
                  :country-data="countryData"
                  class="pa-4"

                  high-color="#838383"
                  low-color="#BBBBBB"
                />
              </v-responsive>
            </v-col>
          </v-row>
        </material-card>
      </v-col>

      <v-col cols="12">
        <v-row>
          <v-col
            v-for="(chart, i) in charts"
            :key="`chart-${i}`"
            cols="12"
            md="6"
            lg="4"
          >
            <material-chart-card
              :color="chart.color"
              :data="chart.data"
              :options="chart.options"
              :responsive-options="chart.responsiveOptions"
              :title="chart.title"
              :type="chart.type"
              hover-reveal
            >
              <template #subtitle>
                <div class="font-weight-light text--secondary">
                  <div v-html="chart.subtitle" />
                </div>
              </template>

              <template #reveal-actions>
                <v-tooltip bottom>
                  <template v-slot:activator="{ attrs, on }">
                    <v-btn
                      v-bind="attrs"
                      icon
                      v-on="on"
                    >
                      <v-icon>
                        mdi-view-split-vertical
                      </v-icon>
                    </v-btn>
                  </template>

                  <span>View</span>
                </v-tooltip>

                <v-tooltip
                  bottom
                >
                  <template v-slot:activator="{ attrs, on }">
                    <v-btn
                      v-bind="attrs"
                      class="mx-2"
                      icon
                      v-on="on"
                    >
                      <v-icon color="success">
                        $edit
                      </v-icon>
                    </v-btn>
                  </template>

                  <span>Edit</span>
                </v-tooltip>

                <v-tooltip bottom>
                  <template v-slot:activator="{ attrs, on }">
                    <v-btn
                      v-bind="attrs"
                      icon
                      v-on="on"
                    >
                      <v-icon
                        color="error"
                        size="18"
                      >
                        $clear
                      </v-icon>
                    </v-btn>
                  </template>

                  <span>Remove</span>
                </v-tooltip>
              </template>

              <template #actions>
                <v-icon
                  class="mr-1"
                  small
                >
                  mdi-clock-outline
                </v-icon>

                <span
                  class="text-caption grey--text font-weight-light"
                  v-text="chart.time"
                />
              </template>
            </material-chart-card>
          </v-col>
        </v-row>
      </v-col>

      <v-col
        v-for="({ actionIcon, actionText, ...attrs }, i) in stats"
        :key="i"
        cols="12"
        md="6"
        lg="3"
      >
        <material-stat-card v-bind="attrs">
          <template #actions>
            <v-icon
              class="mr-2"
              small
              v-text="actionIcon"
            />
            <div class="text-truncate">
              {{ actionText }}
            </div>
          </template>
        </material-stat-card>
      </v-col>

      <v-col cols="12" />

      <v-col
        v-for="(listing, i) in listings"
        :key="`listings-${i}`"
        cols="12"
        md="4"
      >
        <material-reveal-card
          class="text-center"
          :title="listing.title"
        >
          <template #heading>
            <v-img
              :src="`https://demos.creative-tim.com/vue-material-dashboard-pro/img/card-${listing.image}.jpg`"
            />
          </template>

          <template #reveal-actions>
            <v-tooltip bottom>
              <template v-slot:activator="{ attrs, on }">
                <v-btn
                  v-bind="attrs"
                  icon
                  v-on="on"
                >
                  <v-icon>
                    mdi-view-split-vertical
                  </v-icon>
                </v-btn>
              </template>

              <span>View</span>
            </v-tooltip>

            <v-tooltip
              bottom
            >
              <template v-slot:activator="{ attrs, on }">
                <v-btn
                  v-bind="attrs"
                  class="mx-2"
                  icon
                  v-on="on"
                >
                  <v-icon color="success">
                    $edit
                  </v-icon>
                </v-btn>
              </template>

              <span>Edit</span>
            </v-tooltip>

            <v-tooltip bottom>
              <template v-slot:activator="{ attrs, on }">
                <v-btn
                  v-bind="attrs"
                  icon
                  v-on="on"
                >
                  <v-icon
                    color="error"
                    size="18"
                  >
                    $clear
                  </v-icon>
                </v-btn>
              </template>

              <span>Remove</span>
            </v-tooltip>
          </template>

          {{ listing.text }}

          <template #actions>
            <span
              class="text-h4"
              v-text="listing.price"
            />

            <v-tooltip top>
              <template #activator="{ attrs, on }">
                <v-icon
                  class="ml-auto mr-1"
                  small
                  v-bind="!$vuetify.breakpoint.mobile ? {} : attrs"
                  v-on="!$vuetify.breakpoint.mobile ? {} : on"
                >
                  mdi-map-marker
                </v-icon>
              </template>

              {{ listing.location }}
            </v-tooltip>

            <span
              class="text-caption grey--text font-weight-light hidden-md-and-down"
              v-text="listing.location"
            />
          </template>
        </material-reveal-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
  // Utilities
  import { get } from 'vuex-pathify';
  import Vue from 'vue';

  const lineSmooth = Vue.chartist.Interpolation.cardinal({
    tension: 0,
  });

  export default {
    name: 'DashboardView',

    data: () => ({
      countryData: {
        US: 2920,
        DE: 1390,
        AU: 760,
        GB: 690,
        RO: 600,
        BR: 550,
      },
      charts: [{
        type: 'Bar',
        color: 'primary',
        title: 'Website Views',
        subtitle: 'Last Campaign Performance',
        time: 'updated 10 minutes ago',
        data: {
          labels: ['Ja', 'Fe', 'Ma', 'Ap', 'Mai', 'Ju', 'Jul', 'Au', 'Se', 'Oc', 'No', 'De'],
          series: [
            [542, 443, 320, 780, 553, 453, 326, 434, 568, 610, 756, 895],
          ],
        },
        options: {
          axisX: {
            showGrid: false,
          },
          low: 0,
          high: 1000,
          chartPadding: {
            top: 0,
            right: 5,
            bottom: 0,
            left: 0,
          },
        },
        responsiveOptions: [
          ['screen and (max-width: 640px)', {
            seriesBarDistance: 5,
            axisX: {
              labelInterpolationFnc: function (value) {
                return value[0];
              },
            },
          }],
        ],
      }, {
        type: 'Line',
        color: 'success',
        title: 'Daily Sales',
        subtitle: '<i class="mdi mdi-arrow-up green--text"></i><span class="green--text">55%</span>&nbsp;increase in today\'s sales',
        time: 'updated 4 minutes ago',
        data: {
          labels: ['12am', '3pm', '6pm', '9pm', '12pm', '3am', '6am', '9am'],
          series: [
            [230, 750, 450, 300, 280, 240, 200, 190],
          ],
        },
        options: {
          lineSmooth,
          low: 0,
          high: 1000, // creative tim: we recommend you to set the high sa the biggest value + something for a better look
          chartPadding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0,
          },
        },
      }, {
        type: 'Line',
        color: 'info',
        title: 'Completed Tasks',
        subtitle: 'Last Campaign Performance',
        time: 'campaign sent 26 minutes ago',
        data: {
          labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
          series: [
            [12, 17, 7, 17, 23, 18, 38],
          ],
        },
        options: {
          lineSmooth,
          low: 0,
          high: 50, // creative tim: we recommend you to set the high sa the biggest value + something for a better look
          chartPadding: {
            top: 0,
            right: 0,
            bottom: 0,
            left: 0,
          },
        },
      }],
      listings: [
        {
          image: 2,
          location: 'Barcelona, Spain',
          price: '$899/night',
          text: 'The place is close to Barceloneta Beach and bus stop just 2 min by walk and near to "Naviglio" where you can enjoy the life in Barcelona.',
          title: 'Cozy 5 Stars Apartment',
        },
        {
          image: 3,
          location: 'Office Studio',
          price: '$1.119/night',
          text: 'The place is close to Metro Station and bus stop just 2 min by walk and near to "Naviglio" where you can enjoy the night life in London, UK.',
          title: 'Office Studio',
        },
        {
          image: 1,
          location: 'Milan, Italy',
          price: '$459/night',
          text: 'The place is close to Metro Station and bus stop just 2 min by walk and near to "Naviglio" where you can enjoy the main night life in Milan.',
          title: 'Beautiful Castle',
        },
      ],
      stats: [
        {
          actionIcon: 'mdi-alert',
          actionText: 'Get More Space...',
          color: '#FD9A13',
          icon: 'mdi-sofa-single',
          title: 'Bookings',
          value: '184',
        },
        {
          actionIcon: 'mdi-tag',
          actionText: 'Tracked from Google Analytics',
          color: 'primary',
          icon: 'mdi-chart-bar',
          title: 'Website Visits',
          value: '75.521',
        },
        {
          actionIcon: 'mdi-calendar-range',
          actionText: 'Last 24 Hours',
          color: 'success',
          icon: 'mdi-store',
          title: 'Revenue',
          value: '$34,245',
        },
        {
          actionIcon: 'mdi-history',
          actionText: 'Just Updated',
          color: 'info',
          icon: 'mdi-twitter',
          title: 'Followers',
          value: '+245',
        },
      ],
    }),

    computed: {
      sales: get('sales/sales'),
      totalSales () {
        return this.sales.reduce((acc, val) => acc + val.salesInM, 0);
      },
    },
  };
</script>
