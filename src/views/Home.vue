<template>
  <v-container
    id="usuarios-view"
    fluid
    tag="section"
  >
    <v-row align="center">
      <v-col
        cols="6"
      >
        <p class="mb-0 d-inline-block text-h4">
          Informe individual - Home
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <v-btn
            small
            color="primary"
            min-width="100"
          >
            <v-icon left>
              mdi-arrow-down
            </v-icon>
            Descargar
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-divider class="mb-6 secondary" />
    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <material-card
      icon="mdi-filter"
      icon-small
      color="error"
      title="Filtros de informe"
      class="mb-6"
    >
      <v-card-text>
        <v-row align="center">
          <v-col>
            <v-menu
              v-model="displayDate"
              :close-on-content-click="false"
              transition="scale-transition"
              offset-y
              max-width="290px"
              min-width="auto"
            >
              <template v-slot:activator="{ on, attrs }">
                <v-text-field
                  v-model="filter.range"
                  label="Rango de fechas"
                  persistent-hint
                  prepend-icon="mdi-calendar"
                  v-bind="attrs"
                  clearable
                  v-on="on"
                  @blur="loadData()"
                />
              </template>
              <v-date-picker
                v-model="filter.range"
                no-title
                range
                @change="displayDate = false; loadData();"
              />
            </v-menu>
          </v-col>
          <v-col>
            <v-autocomplete
              v-model="filter.user_email"
              :items="lstUsers"
              :loading="isLoading"
              :search-input.sync="searchUsers"
              hide-no-data
              hide-selected
              clearable
              item-text="lastname"
              item-value="email"
              label="Usuarios"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
            />
          </v-col>
          <v-col>
            <v-text-field
              v-model="filter.name"
              label="Buscar curso"
              type="text"
              prepend-icon="mdi-magnify"
              clearable
              @blur="loadData()"
            />
          </v-col>
        </v-row>
        <v-row align="center">
          <v-col>
            <v-select
              v-model="filter.area"
              :items="lstAreas"
              label="Área"
              item-text="area"
              item-value="area"
              clearable
              @change="getPositionsByArea(); loadData();"
            />
          </v-col>
          <v-col>
            <v-select
              v-model="filter.position"
              :items="lstPositions"
              label="Cargo"
              item-text="position"
              item-value="position"
              :disabled="filter.area == null"
              clearable
              @change="loadData()"
            />
          </v-col>
          <v-col>
            <v-autocomplete
              v-model="filter.city"
              :items="lstCities"
              :loading="isLoadingCities"
              :search-input.sync="searchCities"
              cache-items
              hide-no-data
              hide-selected
              clearable
              item-text="city"
              item-value="city"
              label="Buscar ciudad"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
            />
          </v-col>
          <v-col>
            <v-select
              v-model="filter.status_id"
              :items="lstStatus"
              label="Estado"
              item-text="name"
              item-value="id"
              prepend-icon="mdi-check-circle"
              clearable
              @change="loadData()"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </material-card>
    <v-row class="bar-content">
      <v-col
        cols="12"
        class="bar-content-col"
      >
        <material-card
          id="multiple-bar"
          icon-small
          color="accent"
          icon="mdi-chart-timeline-variant"
        >
          <template #title>
            <div class="text-h4 font-weight-light">
              Resumen promedio por plataforma
            </div>
          </template>

          <chartist
            :data="bar.data"
            :options="bar.options"
            type="Bar"
            style="max-height: 250px;"
            class="mt-3"
          />
        </material-card>
      </v-col>
    </v-row>
    <material-card
      icon="mdi-account-school"
      icon-small
      color="orange"
      title="Resumen por curso"
    >
      <v-card-text>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          class="ml-auto"
          hide-details
          label="Buscar registros"
          single-line
          style="max-width: 250px;"
        />

        <v-divider class="mt-3" />

        <v-data-table
          :headers="headers"
          :items="items"
          :search.sync="search"
          multi-sort
          must-sort
          :footer-props="{
            showFirstLastPage: true,
            'itemsPerPageText':'Cursos por página'
          }"
        >
          <template slot="no-data">
            No hay datos para mostrar
          </template>
          <template slot="no-results">
            No hay resultados para mostrar
          </template>
          <template v-slot:[`item.actions`]="data">
            <div>
              <app-btn
                color="success"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                @click="viewData(data.item)"
              >
                <v-icon
                  small
                  v-text="'mdi-eye'"
                />
              </app-btn>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </material-card>
    <v-dialog
      v-model="displayUser"
      persistent
      max-width="600"
    >
      <material-card
        full-header
        light
        color="info"
        class="mx-auto"
      >
        <template #heading>
          <div class="text-center pa-5">
            <div class="text-h4 white--text">
              {{ item.user_name }}
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.position"
                  label="Cargo"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.user_email"
                  label="Correo"
                  type="text"
                  :disabled="true"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.city"
                  label="Ciudad"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.area"
                  label="Área"
                  type="text"
                  :disabled="true"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.role_name"
                  label="Rol"
                  type="text"
                  :disabled="true"
                />
              </v-col>
            </v-row>
          </v-form>
          <div class="pa-3 text-center">
            <v-btn
              small
              color="error"
              min-width="100"
              @click="closeDialog()"
            >
              Cerrar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <v-overlay
      class="v-overlay-custom"
      :value="overlay"
    >
      <v-progress-circular
        indeterminate
        size="64"
      />
    </v-overlay>
    <material-snackbar
      v-model="snackbar.display"
      :type="snackbar.type"
      timeout="10000"
      v-bind="{
        ['top']: true,
        ['right']: true
      }"
    >
      <div>
        <span class="font-weight-bold">&nbsp;{{ snackbar.title }}&nbsp;</span> <span v-html="snackbar.message" />
      </div>
    </material-snackbar>
  </v-container>
</template>

<script>
  import CourseService from '../services/CourseService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import ProviderService from '../services/ProviderService';
  import { get } from 'vuex-pathify';
  export default {
    name: 'HomeView',
    data () {
      return {
        bar: {
          data: {
            labels: ['DTX', 'GT Learn', 'Externas'],
            series: [
              [0, 0, 0],
            ],
          },
          options: {
            seriesBarDistance: 10,
            lineSmooth: this.$chartist.Interpolation.none(),
            axisX: {
              showGrid: false,
            },
            low: 0,
            high: 50, // maximo valor mas 10
            chartPadding: {
              top: 0,
              right: 15,
              bottom: 0,
              left: 0,
            },
            width: '100%',
            height: '100%',
          },
        },
        headers: [
          {
            text: 'Usuario',
            value: 'user_name',
            width: '15%',
          },
          {
            text: 'Área',
            value: 'area',
          },
          {
            text: 'Ciudad',
            value: 'city',
          },
          {
            text: 'Cargo',
            value: 'position',
          },
          {
            text: 'Curso',
            value: 'course_name',
          },
          {
            text: 'Inicio',
            value: 'date',
          },
          {
            text: 'Fin',
            value: 'end_date',
          },
          {
            text: 'Progreso',
            value: 'progress',
          },
          {
            text: 'Calificación',
            value: 'qualification',
          },
          {
            text: 'Horas',
            value: 'hours',
          },
          {
            text: 'Estado',
            value: 'status_name',
          },
          {
            sortable: false,
            text: 'Ver',
            value: 'actions',
            width: '5%',
            align: 'center',
          },
        ],
        filter: {
          range: null,
          user_email: null,
          name: null,
          area: null,
          position: null,
          city: null,
          status_id: null,
        },
        summary: {
          total_courses: '0',
          providers: [],
        },
        lstStatus: [],
        items: [],
        displayDate: false,
        lstSpecialities: [],
        lstProvider: [],
        courseService: null,
        parameterService: null,
        userService: null,
        providerService: null,
        search: undefined,
        overlay: false,
        displayUser: false,
        disabled: false,
        isEdit: false,
        snackbar: {
          display: false,
          title: null,
          type: 'success',
          message: null,
        },
        json_fields: {
          Usuario: 'user_name',
          Correo: 'user_email',
          Área: 'area',
          Ciudad: 'city',
          Cargo: 'position',
          Curso: 'course_name',
          Prohgreso: 'progress',
          Calificación: 'qualification',
          Plataforma: 'provider_name',
          Horas: 'hours',
          Estado: 'status_name',
          Rol: 'role_name',
        },
        // para el filtro de usuario
        userSelected: {},
        lstUsers: [],
        isLoading: false,
        searchUsers: null,
        // filtro por ciudades
        isLoadingCities: false,
        lstCities: [],
        searchCities: null,
        //
        lstAreas: [],
        lstPositions: [],
        item: {},
      };
    },
    computed: {
      ...get('session', [
        'userInfo',
      ]),
    },
    watch: {
      searchUsers (val) {
        console.log('Filtro:', val);
        this.isLoading = true;
        this.parameterService.filterUsers(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstUsers = entries;
          console.log('lstUsers::::::', entries);
          this.isLoading = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoading = false;
        });
      },
      searchCities (val) {
        this.isLoadingCities = true;
        this.parameterService.filterCities(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstCities = entries;
          this.isLoadingCities = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoadingCities = false;
        });
      },
    },
    created () {
      this.courseService = new CourseService();
      this.userService = new UserService();
      this.parameterService = new ParameterService();
      this.providerService = new ProviderService();
    },
    mounted () {
      this.loadData();
      this.loadParameters();
      this.getAreas();
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.courseService.getDashboardByFilter(this.filter).then(response => {
          console.log('Load Data::::::::::::', response.data);
          this.items = response.data.data;
          this.summary = response.data.summary;

          this.bar.data.labels = response.data.chart.labels;
          this.bar.data.series = response.data.chart.series;
          this.bar.options.high = response.data.chart.high;

          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      loadParameters () {
        this.overlay = true;
        this.parameterService.getByType(2).then(response => {
          this.lstStatus = response.data;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
        this.providerService.all().then(response => {
          this.lstProvider = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
        this.parameterService.getSpecialties().then(response => {
          this.lstSpecialities = response.data;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      getAreas () {
        this.overlay = true;
        this.parameterService.getAllAreas().then(response => {
          this.lstAreas = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      getPositionsByArea () {
        this.overlay = true;
        this.parameterService.getPositionsByArea(this.filter.area).then(response => {
          this.lstPositions = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      viewData (item) {
        this.overlay = true;
        console.log('Item:::::::', item);
        this.item = Object.assign({}, item);
        setTimeout(() => {
          this.overlay = false;
          this.displayUser = true;
        }, 500);
      },
      closeDialog () {
        this.item = {};
        this.displayUser = false;
        this.overlay = false;
      },
    },

  };
</script>
<style lang="sass">
  .bar-content
    height: 395px !important

    .bar-content-col
      height: inherit !important

  #multiple-bar
    .ct-series-a .ct-bar
      stroke: #9c27b0 !important
      stroke-width: 30px !important
</style>
