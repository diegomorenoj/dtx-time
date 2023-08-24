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
          Gestion de objetivos
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
            Nuevo objetivo
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
                  label="Seleccione un rango de fechas"
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
              v-model="filter.area"
              :items="lstAreas"
              :loading="isLoadingAreas"
              :search-input.sync="searchAreas"
              cache-items
              hide-no-data
              hide-selected
              clearable
              item-text="area"
              item-value="area"
              label="Buscar área"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
            />
          </v-col>
          <v-col>
            <v-autocomplete
              v-model="filter.position"
              :items="lstPositionsFilter"
              :loading="isLoadingPositions"
              :search-input.sync="searchPositions"
              cache-items
              hide-no-data
              hide-selected
              clearable
              item-text="position"
              item-value="position"
              label="Buscar cargo"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
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
      color="primary"
      title="Listado de objetivos"
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
              v-if="data.item.users_count > 0"
              class="cursor-pointer"
              href="javascript:void(0);"
              style="text-decoration: none;"
              @click="openDialogUsers(data.item)"
            >
              {{ data.item.users_count }}
            </a>
            <span v-else>
              {{ data.item.users_count }}
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
    <!-- PRESUPUESTO ANUAL -->
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
              Objetivo general
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-menu
                  ref="display_start_date"
                  v-model="display_start_date"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="item.start_date"
                      label="Fecha de inicio"
                      persistent-hint
                      prepend-icon="mdi-calendar"
                      v-bind="attrs"
                      v-on="on"
                    />
                  </template>
                  <v-date-picker
                    v-model="item.start_date"
                    no-title
                    @input="display_start_date = false"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="6"
              >
                <v-menu
                  ref="display_end_date"
                  v-model="display_end_date"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  max-width="290px"
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="item.end_date"
                      label="Fecha de fin"
                      persistent-hint
                      prepend-icon="mdi-calendar"
                      v-bind="attrs"
                      v-on="on"
                    />
                  </template>
                  <v-date-picker
                    v-model="item.end_date"
                    no-title
                    :min="item.start_date"
                    @input="display_end_date = false"
                  />
                </v-menu>
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-select
                  v-model="item.area"
                  :items="lstAreas"
                  label="Área"
                  item-text="area"
                  item-value="area"
                  @change="getPositionsByArea()"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-select
                  v-model="item.position"
                  :items="lstPositions"
                  label="Cargo"
                  item-text="position"
                  item-value="position"
                  :disabled="item.area == null"
                  @change="getLevelsByPosition()"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="6"
              >
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
  import ObjetiveService from '../services/ObjetiveService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'ObjetivosGeneralesView',
    data: () => ({
      headers: [
        {
          text: 'Código',
          value: 'id',
          width: '6%',
        },
        {
          text: 'Fecha inicio',
          value: '_start_date',
          width: '9%',
        },
        {
          text: 'Fecha fin',
          value: '_end_date',
          width: '9%',
        },
        {
          text: 'Area',
          value: 'area',
          width: '10%',
        },
        {
          text: 'Cargo',
          value: 'position',
          width: '17%',
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
      filter: {
        range: null,
        user_id: null,
        area: null,
        position: null,
        role_id: null,
      },
      items: [],
      item: {
        id: null,
        start_date: null,
        end_date: null,
        area: null,
        position: null,
        hours: null,
      },
      display_start_date: false,
      display_end_date: false,
      displayDate: false,
      dialog: false,
      displayDialogUsers: false,
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
      this.objetiveService = new ObjetiveService();
      this.userService = new UserService();
      this.parameterService = new ParameterService();
    },
    mounted () {
      this.loadData();
      this.getAreas();
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
        this.parameterService.getPositionsByArea(this.item.area).then(response => {
          this.lstPositions = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      getLevelsByPosition () {
        this.overlay = true;
        this.parameterService.getLevelsByPosition(this.item.position).then(response => {
          this.lstLevels = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      addData () {
        this.overlay = true;
        this.item = {
          id: null,
          start_date: null,
          end_date: null,
          area: null,
          position: null,
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
    },

  };
</script>
