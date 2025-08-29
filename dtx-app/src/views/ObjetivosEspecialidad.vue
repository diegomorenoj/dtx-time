<template>
  <v-container
    id="providers-view"
    fluid
    tag="section"
  >
    <v-row align="center">
      <v-col
        cols="6"
      >
        <p class="mb-0 d-inline-block text-h4">
          Gestion de objetivos por especialidades
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <v-btn
            v-if="userInfo.permits.CREATE_OBJETIVES"
            small
            color="success"
            min-width="100"
            class="mr-2"
            @click="addData()"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Crear objetivo
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
      title="Filtros"
      class="mb-0"
    >
      <v-card-text>
        <v-row align="center">
          <v-col
            cols="4"
          >
            <v-select
              v-model="filter.cycle_id"
              :items="lstCycles"
              label="Ciclo"
              item-text="name"
              item-value="id"
              clearable
              @change="loadData()"
            />
          </v-col>
          <v-col
            cols="4"
          >
            <v-select
              v-model="filter.specialty_id"
              :items="lstSpecialities"
              label="Especialdiad"
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
    <div class="m-0 p-0">
      &nbsp;
    </div>
    <material-card
      icon="mdi-stairs-up"
      icon-small
      color="info"
      title="Listado de objetivos por especialidad"
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
            'itemsPerPageText':'Objetivos por página'
          }"
        >
          <template slot="no-data">
            No hay datos para mostrar
          </template>
          <template slot="no-results">
            No hay resultados para mostrar
          </template>
          <template v-slot:[`item.value`]="data">
            {{ formatPrice(data.item.value) }}
          </template>
          <template v-slot:[`item.users_count`]="data">
            <a
              v-if="data.item.users.length > 0"
              class="cursor-pointer"
              href="javascript:void(0);"
              style="text-decoration: none;"
              @click="openDialogUsers(data.item)"
            >
              {{ data.item.users.length }}
            </a>
            <span v-else>
              {{ data.item.users.length }}
            </span>
          </template>
          <template v-slot:[`item.courses_count`]="data">
            <a
              v-if="data.item.courses.length > 0"
              class="cursor-pointer"
              href="javascript:void(0);"
              style="text-decoration: none;"
              @click="openDialogCourses(data.item)"
            >
              {{ data.item.courses.length }}
            </a>
            <span v-else>
              {{ data.item.courses.length }}
            </span>
          </template>
          <template v-slot:[`item.actions`]="data">
            <div>
              <v-tooltip top>
                <template v-slot:activator="{ on, attrs }">
                  <app-btn
                    color="info"
                    class="px-2 ml-1"
                    elevation="0"
                    min-width="0"
                    small
                    v-bind="attrs"
                    :disabled="!userInfo.permits.UPDATE_OBJETIVES"
                    v-on="on"
                    @click="editData(data.item)"
                  >
                    <v-icon
                      small
                      v-text="'mdi-pencil'"
                    />
                  </app-btn>
                </template>
                <span>Editar objetivo</span>
              </v-tooltip>
              <v-tooltip top>
                <template v-slot:activator="{ on, attrs }">
                  <app-btn
                    color="error"
                    class="px-2 ml-1"
                    elevation="0"
                    min-width="0"
                    small
                    v-bind="attrs"
                    :disabled="!userInfo.permits.DELETE_OBJETIVES"
                    v-on="on"
                    @click="openConfirm(data.item)"
                  >
                    <v-icon
                      small
                      v-text="'mdi-delete'"
                    />
                  </app-btn>
                </template>
                <span>Eliminar objetivo</span>
              </v-tooltip>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </material-card>
    <!-- OBJETIVO POR ESPECIALIDAD -->
    <v-dialog
      v-model="displayDialog"
      persistent
      max-width="500"
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
              Objetivo por especialidad
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col>
                <v-select
                  v-model="item.cycle_id"
                  :items="lstCycles"
                  label="Ciclo"
                  item-text="name"
                  item-value="id"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col>
                <v-select
                  v-model="item.specialty_id"
                  :items="lstSpecialities"
                  label="Especialidad"
                  item-text="name"
                  item-value="id"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col>
                <v-text-field
                  v-model="item.hours"
                  label="Horas"
                  type="number"
                  placeholder="Ingrese las horas"
                />
              </v-col>
            </v-row>
          </v-form>
          <div class="pa-3 text-center mt-2">
            <v-btn
              small
              color="error"
              min-width="100"
              @click="closeDialog()"
            >
              Cancelar
            </v-btn>
            <v-btn
              small
              color="success"
              class="ml-2"
              min-width="100"
              @click="store()"
            >
              Guardar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <!-- DIALOGO USUARIOS -->
    <v-dialog
      v-model="displayDialogUsers"
      persistent
      max-width="800"
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
              Usuarios asociados
            </div>
          </div>
        </template>
        <v-card-text>
          <v-data-table
            :headers="headersUsersList"
            :items="item.users"
            :items-per-page="5"
            class="elevation-1"
            :footer-props="{
              showFirstLastPage: true,
              'itemsPerPageText':'Usuarios por página'
            }"
          />
          <div class="pa-3 text-center mt-2">
            <v-btn
              small
              color="success"
              min-width="100"
              @click="displayDialogUsers = false"
            >
              Cerrar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <!-- DIALOGO CURSOS -->
    <v-dialog
      v-model="displayDialogCourses"
      persistent
      max-width="800"
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
              Cursos Asociados
            </div>
          </div>
        </template>
        <v-card-text>
          <v-data-table
            :headers="headersCoursesList"
            :items="item.courses"
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
              @click="displayDialogCourses = false"
            >
              Cerrar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <!-- LOADER -->
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
    <!-- DIALOGO DE CONFIRMACIÓN -->
    <v-dialog
      v-model="confirm"
      persistent
      max-width="350"
    >
      <v-card>
        <v-card-title class="text-h4">
          Confirmación
        </v-card-title>
        <v-card-text>
          <span class="text-h5">
            ¿Esta seguro de eliminar el objetivo?
          </span>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="warning"
            small
            @click="confirm = false"
          >
            No
          </v-btn>
          <v-btn
            color="success"
            small
            @click="deleteData()"
          >
            Si
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
  import ObjectiveSpecialtyService from '../services/ObjectiveSpecialtyService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'ObjetivosEspecialidadView',
    data: () => ({
      headers: [
        {
          text: 'Ciclo',
          value: 'cycle_name',
          width: '9%',
        },
        {
          text: 'Fecha fin',
          value: 'end_date',
          width: '12%',
        },
        {
          text: 'Especialidad',
          value: 'specialty_name',
        },
        {
          text: 'Cursos',
          value: 'courses_count',
          width: '10%',
        },
        {
          text: 'Usuarios',
          value: 'users_count',
          width: '10%',
        },
        {
          text: 'Horas',
          value: 'hours',
          width: '10%',
        },
        {
          sortable: false,
          text: 'Opciones',
          value: 'actions',
          width: '11%',
          align: 'center',
        },
      ],
      headersUsersList: [
        {
          text: 'Usuario',
          value: 'lastname',
        },
        {
          text: 'Cargo',
          value: 'position',
        },
        {
          text: 'Ciudad',
          value: 'city',
        },
        {
          text: 'Área',
          value: 'area',
        },
      ],
      headersCoursesList: [
        {
          text: 'Nombre Curso',
          value: 'course_name',
        },
        {
          text: 'Horas Requeridas',
          value: 'hours',
        },
      ],
      filter: {
        cycle_id: null,
        specialty_id: null,
      },
      items: [],
      item: {
        id: null,
        cycle_id: null,
        specialty_id: null,
        hours: null,
      },
      display_start_date: false,
      display_end_date: false,
      displayDate: false,
      dialog: false,
      displayDialogUsers: false,
      displayDialogCourses: false,
      objetiveService: null,
      search: undefined,
      overlay: false,
      displayDialog: false,
      disabled: false,
      isEdit: false,
      confirm: false,
      snackbar: {
        display: false,
        title: null,
        type: 'success',
        message: null,
      },
      userService: null,
      lstAreas: [],
      lstPositions: [],
      lstLevels: [],
      // filtro por areas
      isLoadingAreas: false,
      lstAreasFiler: [],
      searchAreas: null,
      // filtro por cargos
      isLoadingPositions: false,
      lstPositionsFilter: [],
      searchPositions: null,
      // filtro por niveles
      isLoadingLevels: false,
      lstLevelsFilter: [],
      searchLevels: null,
      // nuevas
      lstSpecialities: [],
      lstCycles: [],
    }),
    computed: {
      ...get('session', [
        'userInfo',
      ]),
    },
    watch: {
      searchAreas (val) {
        this.isLoadingAreas = true;
        this.parameterService.filterAreas(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstAreasFiler = entries;
          this.isLoadingAreas = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoadingAreas = false;
        });
      },
      searchPositions (val) {
        this.isLoadingPositions = true;
        this.parameterService.filterPositions(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstPositionsFilter = entries;
          this.isLoadingPositions = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoadingPositions = false;
        });
      },
      searchLevels (val) {
        this.isLoadingLevels = true;
        this.parameterService.filterLevels(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstLevelsFilter = entries;
          this.isLoadingLevels = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoadingLevels = false;
        });
      },
    },
    created () {
      this.objetiveService = new ObjectiveSpecialtyService();
      this.userService = new UserService();
      this.parameterService = new ParameterService();
    },
    mounted () {
      this.loadData();
      this.getSpecialities();
      this.getCycles();
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.objetiveService.getAllByFilter(this.filter).then(response => {
          this.items = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      getSpecialities () {
        this.overlay = true;
        this.parameterService.getSpecialties().then(response => {
          this.lstSpecialities = response.data;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      getCycles () {
        this.overlay = true;
        this.parameterService.getCycles().then(response => {
          this.lstCycles = response.data;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      addData () {
        this.overlay = true;
        this.item = {
          id: null,
          cycle_id: null,
          specialty_id: null,
          hours: null,
        };
        this.displayDialog = true;
        this.overlay = false;
        this.isEdit = false;
      },
      editData (item) {
        this.overlay = true;
        this.isEdit = true;
        this.item = Object.assign({}, item);
        this.overlay = false;
        this.displayDialog = true;
      },
      openConfirm (item) {
        this.item = Object.assign({}, item);
        this.confirm = true;
      },
      store () {
        if (!this.isEdit) this.saveData();
        else this.updateData();
      },
      saveData () {
        // GUARDAR SOLICTUD
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.objetiveService.create(this.item).then(response => {
          this.overlay = false;
          this.item = {};
          this.displayDialog = false;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          this.overlay = false;
          this.item = model;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
          this.displayDialog = true;
        });
      },
      updateData () {
        // ACTUALIZAR SOLICTUD
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.objetiveService.update(this.item, this.item.id).then(response => {
          this.overlay = false;
          this.displayDialog = false;
          this.file = null;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log(error);
          this.item = model;
          this.overlay = false;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
          this.displayDialog = true;
        });
      },
      deleteData () {
        // UPDATE
        this.overlay = true;
        this.objetiveService.delete(this.item.id).then(response => {
          this.overlay = false;
          this.item = {};
          this.confirm = false;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          this.confirm = false;
          this.overlay = false;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
        });
      },
      closeDialog () {
        this.item = {};
        this.disabled = true;
        this.displayDialog = false;
        this.overlay = false;
        this.loadData();
      },
      formatPrice (value) {
        let val = 0;
        val = (value / 1).toFixed(0).replace('.', ',');
        return '$ ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      },
      getAvaliable (budgets) {
        let _budget = 0;
        budgets.forEach(bg => {
          _budget = Number(_budget) + Number(bg.value);
        });

        this.item.available_budget = Number(this.item.value) - _budget;
      },
      openDialogUsers (item) {
        this.item = Object.assign({}, item);
        this.displayDialogUsers = true;
      },
      openDialogCourses (item) {
        this.item = Object.assign({}, item);
        this.displayDialogCourses = true;
      },
    },

  };
</script>
