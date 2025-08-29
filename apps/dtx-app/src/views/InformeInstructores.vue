<template>
  <v-container
    id="usuarios-view"
    fluid
    tag="section"
  >
    <v-row align="center">
      <v-col cols="6">
        <p class="mb-0 d-inline-block text-h4">
          Informe instrcutores
        </p>
      </v-col>
      <v-col cols="6">
        <div class="pb-2 text-right">
          <download-excel
            class="ml-2 v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--small primary"
            :data="items"
            :fields="json_fields"
            worksheet="InformeInstructores"
            name="InformeInstructores.xls"
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
        <v-row>
          <v-col>
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
                        @change="
                          displayDate = false;
                          loadData();
                        "
                      />
                    </v-menu>
                  </v-col>
                </v-row>
              </v-card-text>
            </material-card>
          </v-col>
        </v-row>
      </v-col>
      <v-col>
        <v-row>
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
                      Total horas
                    </p>
                    <v-divider class="secondary" />
                    <p
                      class="text-h2 text-right mt-0 mb-0"
                      style="line-height: 1.2em !important"
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
          style="max-width: 250px"
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
            itemsPerPageText: 'Cursos por página',
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
        </v-data-table>
      </v-card-text>
    </material-card>
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
        ['right']: true,
      }"
    >
      <div>
        <span class="font-weight-bold">&nbsp;{{ snackbar.title }}&nbsp;</span>
        <span v-html="snackbar.message" />
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
    name: 'InformeInstructoresView',
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
        default: () => { },
      },
      plugins: {
        type: Object,
        default: () => { },
      },
    },
    data () {
      return {
        headers: [
          {
            text: 'Área',
            value: 'area',
          },
          {
            text: 'Ciudad',
            value: 'city',
          },
          {
            text: 'Instructor',
            value: 'lastname',
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
            text: 'Evaluación',
            value: 'qualification',
          },
          {
            text: 'Horas',
            value: 'hours',
          },
        ],
        filter: {
          range: ['2024-08-01', '2025-05-31'],
          user_email: null,
          name: null,
        },
        summary: {
          hours_teach: 0,
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
        displayDialogTeach: false,
        teach: [],
      };
    },
    computed: {
      ...get('session', ['userInfo']),
    },
    watch: {
      searchUsers (val) {
        console.log('Filtro:', val);
        this.isLoading = true;
        this.parameterService
          .filterUsers(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstUsers = entries;
            console.log('lstUsers::::::', entries);
            this.isLoading = false;
          })
          .catch((error) => {
            console.log('Error::::::', error);
            this.isLoading = false;
          });
      },
      searchCities (val) {
        this.isLoadingCities = true;
        this.parameterService
          .filterCities(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstCities = entries;
            this.isLoadingCities = false;
          })
          .catch((error) => {
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
        this.filter.tipo = 2; // INFORME INDIVIDUAL
        this.courseService
          .getInstructorsByFilter(this.filter)
          .then((response) => {
            console.log('Load Data::::::::::::', response.data);
            this.items = response.data.data;
            this.summary = response.data.summary;
            this.overlay = false;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
      },
      loadParameters () {
        this.overlay = true;
        this.parameterService
          .getByType(2)
          .then((response) => {
            this.lstStatus = response.data;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
        this.providerService
          .all()
          .then((response) => {
            this.lstProvider = response.data;
            this.overlay = false;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
        this.parameterService
          .getSpecialties()
          .then((response) => {
            this.lstSpecialities = response.data;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
      },
      getAreas () {
        this.overlay = true;
        this.parameterService
          .getAllAreas()
          .then((response) => {
            this.lstAreas = response.data;
            this.overlay = false;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
      },
      getPositionsByArea () {
        this.overlay = true;
        this.parameterService
          .getPositionsByArea(this.filter.area)
          .then((response) => {
            this.lstPositions = response.data;
            this.overlay = false;
          })
          .catch((error) => {
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
