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
          Gestion de presupuesto
          <span
            v-if="userInfo.rol_id == 4 || userInfo.rol_id == 5"
            style="font-weight: bold;"
          >&nbsp;- Ciudad: {{ userInfo.city }}</span>
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <v-btn
            v-if="userInfo.permits.CREATE_BUDGETS"
            small
            color="success"
            min-width="100"
            class="mr-2"
            @click="addData()"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nuevo presupuesto
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-divider class="mb-6 secondary" />
    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <material-card
      icon="mdi-cash"
      icon-small
      color="orange"
      title="Listado de presupuestos"
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
            'itemsPerPageText':'Presupuestos por página'
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
          <template v-slot:[`item.used_budget`]="data">
            {{ formatPrice(data.item.used_budget) }}
          </template>
          <template v-slot:[`item.executed_budget`]="data">
            {{ formatPrice(data.item.executed_budget) }}
          </template>
          <template v-slot:[`item.available_budget`]="data">
            {{ formatPrice(data.item.available_budget) }}
          </template>
          <template v-slot:[`item.actions`]="data">
            <div>
              <v-tooltip top>
                <template v-slot:activator="{ on, attrs }">
                  <app-btn
                    color="success"
                    class="px-2 ml-1"
                    elevation="0"
                    min-width="0"
                    small
                    v-bind="attrs"
                    :disabled="!userInfo.permits.READ_DISTRIBUTE_BUDGETS"
                    v-on="on"
                    @click="distributeBudget(data.item)"
                  >
                    <v-icon
                      small
                      v-text="'mdi-currency-usd'"
                    />
                  </app-btn>
                </template>
                <span>Distribuir presupuesto</span>
              </v-tooltip>
              <v-tooltip top>
                <template v-slot:activator="{ on, attrs }">
                  <app-btn
                    color="info"
                    class="px-2 ml-1"
                    elevation="0"
                    min-width="0"
                    small
                    v-bind="attrs"
                    :disabled="!userInfo.permits.UPDATE_BUDGETS"
                    v-on="on"
                    @click="editData(data.item)"
                  >
                    <v-icon
                      small
                      v-text="'mdi-pencil'"
                    />
                  </app-btn>
                </template>
                <span>Editar presupuesto</span>
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
                    :disabled="!userInfo.permits.DELETE_BUDGETS"
                    v-on="on"
                    @click="openConfirm(data.item)"
                  >
                    <v-icon
                      small
                      v-text="'mdi-delete'"
                    />
                  </app-btn>
                </template>
                <span>Eliminar presupuesto</span>
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
              Presupuesto por año
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col
                cols="5"
              >
                <v-text-field
                  v-model="item.anio"
                  label="Año"
                  type="number"
                  placeholder="Ingrese el año"
                />
              </v-col>
              <v-col
                cols="7"
              >
                <v-text-field
                  v-model="item.value"
                  label="Valor"
                  type="number"
                  placeholder="Ingrese el valor"
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
    <!-- DISTRIBUCIÓN DE PRESUPUESTO -->
    <v-dialog
      v-model="displayBudget"
      persistent
      max-width="950"
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
              Distribución de presupuesto
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.anio"
                  label="Año"
                  type="number"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  :value="formatPrice(item.value)"
                  label="Total"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  :value="formatPrice(item.available_budget)"
                  label="Disponible"
                  type="text"
                  :disabled="true"
                />
              </v-col>
            </v-row>
            <v-row
              v-if="userInfo.permits.DISTRIBUTE_BUDGETS"
              align="center"
            >
              <v-col
                cols="4"
              >
                <v-autocomplete
                  v-model="budget.city"
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
                  placeholder="Empieza a escribir para buscar"
                  prepend-icon="mdi-database-search"
                  :disabled="!userInfo.permits.DISTRIBUTE_BUDGETS"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-autocomplete
                  v-model="budget.area"
                  :items="lstAreas"
                  :loading="isLoadingAreas"
                  :search-input.sync="searchAreas"
                  hide-no-data
                  hide-selected
                  clearable
                  item-text="area"
                  item-value="area"
                  label="Buscar área"
                  placeholder="Empieza a escribir para buscar"
                  prepend-icon="mdi-database-search"
                  :disabled="budget.city == null ? true : false"
                />
              </v-col>
              <v-col
                cols="3"
              >
                <v-text-field
                  v-model="budget.value"
                  label="Valor"
                  type="number"
                  placeholder="Ingrese el valor"
                  :disabled="!userInfo.permits.DISTRIBUTE_BUDGETS"
                />
              </v-col>
              <v-col
                cols="1"
              >
                <v-btn
                  class="float-right"
                  min-width="0"
                  icon
                  color="success"
                  :disabled="!userInfo.permits.DISTRIBUTE_BUDGETS"
                  @click="addBudget()"
                >
                  <v-icon>mdi-plus-circle</v-icon>
                </v-btn>
              </v-col>
            </v-row>
            <v-row
              v-for="(bg, i) in item.budgets"
              :key="i"
              align="center"
            >
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="bg.city"
                  label="Ciudad"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="bg.area"
                  label="Área"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="3"
              >
                <v-text-field
                  :value="formatPrice(bg.value)"
                  label="Valor"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="1"
              >
                <v-btn
                  v-if="userInfo.permits.DISTRIBUTE_BUDGETS"
                  class="float-right"
                  min-width="0"
                  icon
                  color="error"
                  @click="removeBudget(i)"
                >
                  <v-icon>mdi-minus-circle</v-icon>
                </v-btn>
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
              v-if="userInfo.permits.DISTRIBUTE_BUDGETS"
              small
              color="success"
              class="ml-2"
              min-width="100"
              @click="storeBudgets()"
            >
              Guardar
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
            ¿Esta seguro de eliminar el presupuesto y su distribución?
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
  import BudgetService from '../services/BudgetService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'PresupuestoView',
    data: () => ({
      headers: [
        {
          text: 'Año',
          value: 'anio',
          width: '8%',
        },
        {
          text: 'Total',
          value: 'value',
          width: '20%',
        },
        {
          text: 'Distribuido',
          value: 'used_budget',
          width: '20%',
        },
        {
          text: 'Ejecutado',
          value: 'executed_budget',
          width: '19%',
        },
        {
          text: 'Disponible',
          value: 'available_budget',
          width: '19%',
        },
        {
          sortable: false,
          text: 'Opciones',
          value: 'actions',
          width: '14%',
          align: 'center',
        },
      ],
      items: [],
      item: {
        id: null,
        anio: null,
        value: null,
        city: null,
        area: null,
        budgets: [],
      },
      budget: {
        anio: null,
        value: null,
        city: null,
        area: null,
      },
      displayDate: false,
      dialog: false,
      budgetService: null,
      search: undefined,
      overlay: false,
      displayDialog: false,
      displayBudget: false,
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
      // filtro por ciudades
      isLoadingCities: false,
      lstCities: [],
      searchCities: null,
      // filtro por areas
      isLoadingAreas: false,
      lstAreas: [],
      searchAreas: null,
    }),
    computed: {
      ...get('session', [
        'userInfo',
      ]),
    },
    watch: {
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
      searchAreas (val) {
        if (!this.budget.city) return false;
        this.isLoadingAreas = true;
        this.parameterService.filterAreasByCity(this.budget.city, val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstAreas = entries;
          this.isLoadingAreas = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoadingAreas = false;
        });
      },
    },
    created () {
      this.budgetService = new BudgetService();
      this.userService = new UserService();
      this.parameterService = new ParameterService();
    },
    mounted () {
      this.loadData();
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.budgetService.all().then(response => {
          this.items = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      addData () {
        this.overlay = true;
        this.item.anio = null;
        this.item.value = null;
        this.displayDialog = true;
        this.overlay = false;
        this.isEdit = false;
      },
      distributeBudget (item) {
        this.overlay = true;
        this.item = Object.assign({}, item);
        this.displayBudget = true;
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
        this.budgetService.create(this.item).then(response => {
          this.overlay = false;
          this.item = {};
          this.displayDialog = false;
          this.displayBudget = false;
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
      storeBudgets () {
        // GUARDAR DISTRIBUCIÓN
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.budgetService.store(this.item).then(response => {
          this.overlay = false;
          this.item = {};
          this.displayBudget = false;
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
          this.displayBudget = true;
        });
      },
      updateData () {
        // ACTUALIZAR SOLICTUD
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.budgetService.update(this.item, this.item.id).then(response => {
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
        this.budgetService.delete(this.item.id).then(response => {
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
        this.displayBudget = false;
        this.overlay = false;
        this.loadData();
      },
      removeBudget (index) {
        this.item.budgets.splice(index, 1);
        this.getAvaliable(this.item.budgets);
      },
      addBudget () {
        if (this.budget != null) {
          if (this.budget.city.includes('Mexico')) {
            this.snackbar = {
              display: true,
              title: 'ERROR: ',
              type: 'error',
              message: 'El area es requerida',
            };
          } else if (this.budget.value == null) {
            this.snackbar = {
              display: true,
              title: 'ERROR: ',
              type: 'error',
              message: 'El valor es requerido',
            };
          } else {
            this.item.budgets.push(this.budget);
            console.log('Distribución::::::', this.item.budgets);
            this.getAvaliable(this.item.budgets);
            this.budget = {};
          }
        }
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
    },

  };
</script>
