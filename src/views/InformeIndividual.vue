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
          Informe individual
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <download-excel
            class="ml-2 v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--small primary"
            :data="items"
            :fields="json_fields"
            worksheet="InformeGlobal"
            name="informeGlobal.xls"
          >
            <v-icon
              left
              dark
              small
            >
              mdi-arrow-down
            </v-icon>
            Descargar
          </download-excel>
        </div>
      </v-col>
    </v-row>
    <v-divider class="mb-6 secondary" />
    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <v-row>
      <v-col cols="8">
        <material-card
          id="chart-bar-gtmx"
          icon-small
          color="accent"
          icon="mdi-chart-timeline-variant"
        >
          <template #title>
            <div class="text-h4 font-weight-light">
              Resumen promedio por plataforma
            </div>
          </template>
          <chartjs-bar
            :chart-options="chartOptions"
            :chart-data="chartData"
            :chart-id="chartId"
            :dataset-id-key="datasetIdKey"
            :plugins="plugins"
            :css-classes="cssClasses"
            :styles="styles"
            :width="width"
            :height="height"
          />
        </material-card>
      </v-col>
      <v-col>
        <v-row>
          <v-col class="mb-0">
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
                </v-row>
              </v-card-text>
            </material-card>
          </v-col>
        </v-row>
        <v-row class="mt-0">
          <v-col>
            <material-card
              icon="mdi-filter"
              icon-small
              color="info"
              class="mb-6"
            >
              <v-card-text>
                <v-row align="center">
                  <v-col>
                    <p class="text-right mb-1">
                      Total horas de capacitación
                    </p>
                    <v-divider class="secondary" />
                    <p
                      class="text-h2 text-right mt-0 mb-0"
                      style="line-height: 1.2em !important;"
                    >
                      {{ summary.hours_aprove }}
                    </p>
                  </v-col>
                </v-row>
                <v-row align="center">
                  <v-col>
                    <p class="text-right mb-1">
                      Total horas como instructor
                    </p>
                    <v-divider class="secondary" />

                    <a
                      v-if="summary.hours_teach > 0"
                      class="cursor-pointer info"
                      href="javascript:void(0);"
                      style="text-decoration: none;"
                      @click="openDialogTeach()"
                    >
                      <p
                        class="text-h2 text-right mt-0"
                        style="line-height: 1.2em !important;"
                      >
                        {{ summary.hours_teach }}
                      </p>
                    </a>
                    <p
                      v-else
                      class="text-h2 text-right mt-0"
                      style="line-height: 1.2em !important;"
                    >
                      {{ summary.hours_teach }}
                    </p>
                  </v-col>
                </v-row>
              </v-card-text>
            </material-card>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <v-row>
      <v-col class="mb-1" />
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
          <template
            slot="item.course_name"
            slot-scope="props"
          >
            <div v-html="props.item.course_name" />
          </template>
          <template v-slot:[`item.actions`]="data">
            <div>
              <app-btn
                v-if="shouldShowButton(data.item.provider_name)"
                color="info"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                @click="lstZoom(data.item)"
              >
                <v-icon
                  small
                  v-text="'mdi-video-outline'"
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
    <v-dialog
      v-model="displayDialogTeach"
      persistent
      max-width="600"
    >
      <material-card
        full-header
        light
        inline
        color="info"
        class="mx-auto"
      >
        <template #heading>
          <div class="text-center pa-5">
            <div class="text-h4 white--text">
              Horas como instructor
            </div>
          </div>
        </template>
        <v-card-text>
          <v-data-table
            :headers="headersTeachList"
            :items="teach"
            :items-per-page="5"
            class="elevation-1"
            :footer-props="{
              showFirstLastPage: true,
              'itemsPerPageText':'Cursos por página'
            }"
          />
          <div class="pa-3 text-center mt-2">
            <v-btn
              small
              color="success"
              min-width="100"
              @click="displayDialogTeach = false"
            >
              Cerrar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <!-- Listar las sesiones de zoom por curso  -->
    <v-dialog
      v-model="displayZoom"
      persistent
      max-width="1024"
    >
      <material-card
        full-header
        light
        inline
        color="info"
        class="mx-auto"
      >
        <template #heading>
          <div class="text-center pa-5">
            <div class="text-h4 white--text">
              Listado de Asistencia Zoom para <br> <span v-html="filterZoom.course" />
            </div>
          </div>
        </template>
        <v-card-text>
          <v-data-table
            :headers="headersZoom"
            :items="assistants"
            :search.sync="search"
            multi-sort
            must-sort
          >
            <template v-slot:no-data>
              <v-alert
                type="info"
                color="info"
                icon="mdi-alert"
              >
                Asistencia no Confirmada
              </v-alert>
            </template>
          </v-data-table>
          <div style="width: 100%; text-align: center; color: black;">
            <strong>Nota</strong>: El presente informe solo corresponde a la asistencia del usuario en la sesión en vivo de Zoom
          </div>
          <div class="pa-3 text-center mt-2">
            <v-btn
              small
              color="error"
              min-width="100"
              @click="displayZoom = false"
            >
              Cancelar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>

    <!-- -->
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
    name: 'InformeIndividualView',
    props: {
      chartId: {
        type: String,
        default: 'bar-chart',
      },
      datasetIdKey: {
        type: String,
        default: 'label',
      },
      width: {
        type: Number,
        default: 400,
      },
      height: {
        type: Number,
        default: 400,
      },
      cssClasses: {
        default: '',
        type: String,
      },
      styles: {
        type: Object,
        default: () => {},
      },
      plugins: {
        type: Object,
        default: () => {},
      },
    },
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
        chartData: {
          labels: [],
          datasets: [
            {
              label: 'Cursos',
              backgroundColor: '#9c27b0',
              data: [],
            },
          ],
        },
        chartOptions: {
          responsive: true,
          maintainAspectRatio: false,
        },
        headers: [
          {
            text: 'Nombre del curso',
            value: 'course_name',
          },
          {
            text: 'Obligatorio',
            value: 'required',
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
            text: 'Avance',
            value: 'progress',
          },
          {
            text: 'Calificación',
            value: 'qualification',
          },
          {
            text: 'Plataforma',
            value: 'provider_name',
          },
          {
            text: 'Horas',
            value: 'hours',
          },
          {
            text: 'Estatus del curso',
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
        headersTeachList: [
          {
            text: 'Nombre Curso',
            value: 'course_name',
          },
          {
            text: 'Horas',
            value: 'hours',
          },
          {
            text: 'Calificación',
            value: 'qualification',
          },
        ],
        headersZoom: [
          {
            text: 'Sesión',
            value: 'nombre_sesion',
          },
          {
            text: 'Duración',
            value: 'duracion',
          },
          {
            text: 'Tiempo en Línea',
            value: 'tiempo_en_linea',
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
          tipo: null,
        },
        filterZoom: {
          course_id: null,
          email: null,
          course: null,
        },
        summary: {
          hours_aprove: 0,
          hours_teach: 0,
          total_courses: '0',
          providers: [],
        },
        lstStatus: [],
        items: [],
        assistants: [],
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
        displayZoom: false,
        displayDialogTeach: false,
        teach: [],
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
      shouldShowButton (origin) {
        return origin === 'DTX';
      },
      lstZoom (filter) {
        console.log(filter);
        this.filterZoom.course_id = filter.course_id;
        this.filterZoom.course = filter.course_name;
        this.filterZoom.email = filter.user_email.toLowerCase();
        this.parameterService.getZoomData(this.filterZoom).then(response => {
          console.log(response.data);
          this.assistants = response.data;
          this.displayZoom = true;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      loadData () {
        this.overlay = true;
        this.filter.tipo = 2; // INFORME INDIVIDUAL
        this.courseService.getDashboardByFilter(this.filter).then(response => {
          console.log('Datos::::::::::::', response.data);
          this.items = response.data.data;
          this.summary = response.data.summary;
          this.teach = response.data.teach;
          // OLD CHART
          this.bar.data.labels = response.data.chart.labels;
          this.bar.data.series = response.data.chart.series;
          this.bar.options.high = response.data.chart.high;
          // NUEVO CHART BAR
          this.chartData.labels = response.data.chart.labels;
          this.chartData.datasets[0].data = response.data.chart.series[0];
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
      openDialogTeach () {
        console.log('Teach::::', this.teach);
        this.displayDialogTeach = true;
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
  #chart-bar-gtmx
    padding: 0px 10px 10px 10px !important
</style>
