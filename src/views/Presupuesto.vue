<template>
  <v-container
    id="providers-view"
    tag="section"
  >
    <v-row align="center">
      <v-col
        cols="3"
      >
        <p class="mb-0 text-h4">
          Gestion de presupuesto
          <span
            v-if="userInfo.rol_id == 4 || userInfo.rol_id == 5"
            style="font-weight: bold;"
          >&nbsp;- Ciudad: {{ userInfo.city }} <br> {{ mesInicial }}  {{ filter.anio }}  /  {{ mesFinal }} {{ filter.anio + 1 }}  </span>
        </p>
      </v-col>
      <v-col
        cols="2"
      >
        <v-select
          v-model="filter.anio"
          :items="isMain"
          label="Seleccionar presupuesto"
          item-text="anio"
          item-value="anio"
          @change="changeAnio(); getMainBudget();"
        />
      </v-col>
      <v-col
        cols="3"
      >
        <div class="pb-6 text-right">
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
          <app-btn
            v-if="userInfo.permits.CREATE_BUDGETS"
            color="success"
            class="px-2 ml-1"
            elevation="0"
            min-width="0"
            small
            @click="editBudgetMain()"
          >
            <v-icon
              small
              v-text="'mdi-pencil'"
            />
          </app-btn>
        </div>
      </v-col>
      <v-col cols="2">
        <div class="pb-6 text-right">
          <v-btn
            v-if="userInfo.permits.UPDATE_BUDGETS"
            small
            color="info"
            min-width="100"
            class="mr-2"
            @click="openDistributeBudget()"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Distribuir
          </v-btn>
        </div>
      </v-col>
      <v-col cols="2">
        <div class="pb-6 text-right">
          <download-excel
            class="ml-2 v-btn mr-5 v-btn--is-elevated v-btn--has-bg theme--light v-size--small primary"
            :data="computedItems"
            :fields="json_fields"
            :header="`Resumen ${mesInicial + ' ' + filter.anio}  /  ${mesFinal} ${filter.anio + 1} `"
            :worksheet="`Presupuesto_${filter.anio}`"
            :name="`Presupuesto${filter.anio}.xls`"
          >
            <v-icon
              left
              dark
              small
            >
              mdi-file-excel
            </v-icon>
            Descargar
          </download-excel>
        </div>
      </v-col>
    </v-row>
    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <material-card
      v-if="userInfo.permits.UPDATE_BUDGETS"
      icon="mdi-cash"
      icon-small
      color="error"
      :title="`Resumen ${mesInicial + ' ' + filter.anio}  /  ${mesFinal} ${filter.anio + 1} `"
      class="mb-6"
    >
      <v-card-text>
        <template>
          <div>
            <table
              cellspacing="10px"
              style="width:800px; margin:0px auto; font-size:12pt; text-align: center; "
            >
              <thead
                class="v-data-table-header"
                style="font-weight: bold;"
              >
                <tr>
                  <th style="width: 25%; font-weight: bold; color:#000; border-bottom: #9c27b0 solid 1px; ">
                    Valor
                  </th>
                  <th style="width: 25%; font-weight: bold; color:#000; border-bottom: #9c27b0 solid 1px; ">
                    Distribuido
                  </th>
                  <th style="width: 25%; font-weight: bold; color:#000; border-bottom: #9c27b0 solid 1px; ">
                    Por Distribuir
                  </th>
                  <th style="width: 25%; font-weight: bold; color:#000; border-bottom: #9c27b0 solid 1px; ">
                    Gastado
                  </th>
                  <th style="width: 25%; font-weight: bold; color:#000; border-bottom: #9c27b0 solid 1px; ">
                    Disponible
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="selectedItem">
                  <td>{{ formatPrice(selectedItem.value) }}</td>
                  <td>{{ formatPrice(assigned) }}</td>
                  <td :class="{ 'negative': selectedItem.to_assign < 0 }">
                    {{ formatPrice(selectedItem.to_assign) }}
                  </td>
                  <td>{{ formatPrice(selectedItem.spent) }}</td>
                  <td>{{ formatPrice(selectedItem.executed_budget) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </template>
      </v-card-text>
    </material-card>

    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <material-card
      icon="mdi-division"
      icon-small
      color="orange"
      title="Distribución"
    >
      <v-card-text>
        <v-row>
          <v-col
            class="text-right"
            cols="7"
          />
          <v-col
            class="text-right"
            cols="3"
          >
            <v-text-field
              v-model="search"
              append-icon="mdi-magnify"
              class="ml-auto"
              hide-details
              label="Buscar registros"
              single-line
              style="width: 250px;"
            />
          </v-col>
          <v-col
            cols="2"
            class="text-right"
            style="line-height: 72px;"
          >
            <strong style="color:#000">Asignado:</strong> {{ formatPrice(totalFilteredValue) }}
          </v-col>
        </v-row>

        <v-divider class="mt-3" />

        <v-data-table
          :headers="headers"
          :items="computedItems"
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
          <template v-slot:[`item.spent`]="data">
            {{ formatPrice(data.item.spent) }}
          </template>
          <template v-slot:[`item.executed_budget`]="data">
            <span :class="{'negative': data.item.executed_budget < 0}">
              {{ formatPrice(data.item.executed_budget) }}
            </span>
          </template>
          <!-- Add by Lili: QF -->
          <template v-slot:[`item.available_budget`]="data">
            {{ formatPrice(data.item.available_budget) }}
          </template>
          <template
            v-if="userInfo.permits.UPDATE_BUDGETS"
            v-slot:[`item.actions`]="data"
          >
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
      width="500"
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
          <v-form ref="form">
            <v-row align="center">
              <v-col
                cols="5"
              >
                <v-text-field
                  v-model="item.anio"
                  label="Año (*)"
                  type="number"
                  placeholder="Ingrese el año"
                  :disabled="isEdit"
                  :rules="[v => !!v || 'El campo Año es obligatorio']"
                  required
                />
              </v-col>
              <v-col
                cols="7"
              >
                <v-text-field
                  v-model="item.value"
                  label="Valor (*)"
                  type="number"
                  placeholder="Ingrese el valor"
                  :rules="[v => !!v || 'El campo Valor es obligatorio']"
                  required
                  @blur="actualizarFechaCompuesta"
                />
              </v-col>
            </v-row>
            <v-row style="text-align:center">
              <v-col> {{ periodData }} </v-col>
              <v-col
                v-if="msgValidateYear"
                style="color:#ff0000"
              >
                {{ msgValidateYear }}
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
              {{ isEdit ? 'Editar' : 'Guardar' }}
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>

    <!-- DIALOGO DE CONFIRMACIÓN -->
    <v-dialog
      v-model="budgetDistribution"
      persistent
      width="500"
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
              Distribución <br>
              {{ mesInicial + ' ' + filter.anio }}  /  {{ mesFinal }} {{ filter.anio + 1 }}
              <br> <strong> Por Distribuir: {{ formatPrice(selectedItem.to_assign) }} </strong>
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form ref="frmDistribucion">
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-autocomplete
                  v-model="item.city"
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
                  :disabled="!userInfo.permits.SEARCH_CITIES_TRAINING"
                  :rules="[v => !!v || 'El campo Ciudad es obligatorio']"
                  required
                  @input="loadData()"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-autocomplete
                  v-model="item.area"
                  :items="lstAreas"
                  :loading="isLoadingAreas"
                  :search-input.sync="searchAreas"
                  hide-no-data
                  hide-selected
                  clearable
                  item-text="area"
                  item-value="area"
                  label="Buscar área"
                  placeholder="Empieza a escribir para Buscar"
                  prepend-icon="mdi-database-search"
                  :disabled="!userInfo.permits.SEARCH_AREAS_TRAINING"
                  :rules="[v => !!v || 'El campo Area es obligatorio']"
                  required
                  @input="loadData()"
                />
              </v-col>
              <v-col
                cols="12"
              >
                <v-text-field
                  v-model="item.value"
                  label="Valor"
                  type="number"
                  placeholder="Ingrese el valor"
                  :rules="[v => !!v || 'El campo Valor es obligatorio']"
                  required
                />
              </v-col>
            </v-row>
          </v-form>
          <div class="pa-3 text-center mt-2">
            <v-btn
              small
              color="error"
              min-width="100"
              @click="budgetDistribution = false"
            >
              Cancelar
            </v-btn>
            <v-btn
              small
              color="success"
              class="ml-2"
              min-width="100"
              @click="storeDistribution()"
            >
              Guardar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <!-- ------ -->
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
      width="350"
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

    <!-- Editar Distribución -->
    <v-dialog
      v-model="budgetEditDistribution"
      persistent
      width="500"
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
              Distribución <br>
              {{ mesInicial + ' ' + filter.anio }}  /  {{ mesFinal }} {{ filter.anio + 1 }}
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
                  v-model="item.city"
                  label="Ciudad"
                  type="text"
                  placeholder="Ingrese el valor"
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
                  placeholder="Ingrese el valor"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="12"
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
              @click="budgetEditDistribution = false"
            >
              Cancelar
            </v-btn>
            <v-btn
              small
              color="success"
              class="ml-2"
              min-width="100"
              @click="updateData()"
            >
              Guardar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>
    <!-- ------ -->
    <!-- ----- -->
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
      headersSelected: [
        {
          text: 'Asignado',
          value: 'value',
          width: '16%',
        },
        {
          text: 'Gastado',
          value: 'spent',
          width: '16%',
        },
        {
          text: 'Disponible',
          value: 'executed_budget',
          width: '18%',
        },
      ],
      items: [],
      isMain: [],
      filter: {
        anio: null,
        city: null,
        area: null,
      },
      selectedItem: {
        id: null,
        anio: null,
        value: null,
        spent: null,
        to_assign: null,
        city: null,
        area: null,
        executed_budget: null,
        budgets: [],
      },
      item: {
        id: null,
        anio: null,
        value: null,
        spent: null,
        city: null,
        area: null,
        executed_budget: null,
        is_main: null,
        budgets: [],
      },
      budget: {
        anio: null,
        value: null,
        city: null,
        area: null,
      },
      json_fields: {
        Ciudad: 'city',
        Area: 'area',
        Asignado: 'value',
        Ejecutado: 'spent',
        Disponible: 'executed_budget',
      },
      assigned: null,
      displayDate: false,
      dialog: false,
      budgetDistribution: false,
      isDistribution: false,
      budgetEditDistribution: false,
      periodData: null,
      mesInicial: process.env.VUE_APP_MES_INICIAL,
      mesFinal: process.env.VUE_APP_MES_FINAL,
      budgetService: null,
      search: undefined,
      overlay: false,
      displayDialog: false,
      displayBudget: false,
      msgValidateYear: '',
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

      headers () {
        const headers = [
          {
            text: 'Ciudad',
            value: 'city',
            width: '10%',
          },
          {
            text: 'Área',
            value: 'area',
            width: '25%',
          },
          {
            text: 'Asignado',
            value: 'value',
            width: '16%',
            align: 'right',
          },
          {
            text: 'Gastado',
            value: 'spent',
            width: '16%',
            align: 'right',
          },
          {
            text: 'Disponible',
            value: 'executed_budget',
            width: '18%',
            align: 'right',
          },
        ];

        if (this.userInfo.permits.UPDATE_BUDGETS) {
          // Agregar columna de opciones solo si el usuario tiene permisos de actualización
          headers.push({
            sortable: false,
            text: 'Opciones',
            value: 'actions',
            width: '15%',
            align: 'center',
          });
        }

        return headers;
      },
      filteredItems () {
        const searchQuery = (this.search || '').toLowerCase();
        // Filtrar por area o city en lugar de value
        return this.items.filter(item => {
          if (item && item.area) {
            return item.area.toLowerCase().includes(searchQuery);
          }
          return false;
        });
      },
      computedItems () {
        console.log('filteredItems:', this.filteredItems);
        // Calcular executed_budget para cada elemento en filteredItems
        return this.filteredItems.map(item => ({
          ...item,
          executed_budget: item.value - item.spent,
        }));
      },
      totalFilteredValue () {
        // Calcula la suma de 'value' para los elementos en filteredItems
        return this.filteredItems.reduce((sum, item) => sum + item.value, 0);
      },
    },
    watch: {
      searchCities (val) {
        this.lstAreas = [];
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
        console.log(this.item.city);

        if (!this.item.city) return false;
        this.isLoadingAreas = true;
        this.parameterService.filterAreasByCity(this.item.city, val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstAreas = entries;
          const filtered = this.items.filter(item => item.city === this.item.city);
          console.log(this.items);
          this.lstAreas = this.lstAreas.filter(areaB => !filtered.some(itemA => itemA.area === areaB.area));
          console.log(this.lstAreas);
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
      this.getMainBudget();
    },
    methods: {
      loadData () {
        if (this.filter.anio === null) {
          const actualDate = new Date();
          const month = actualDate.getMonth() + 1;
          const actualAnio = month <= 7 ? actualDate.getFullYear() - 1 : actualDate.getFullYear();
          this.filter.anio = actualAnio;
        }
        this.overlay = true;
        this.budgetService.getAllByAnio(this.filter).then(response => {
          this.items = this.filterDataByUserRole(response.data);
          // Calcular la suma aquí
          this.assigned = this.items.reduce((acc, item) => {
            if (item.is_main === false || item.is_main === null) {
              return acc + item.value;
            }
            return acc;
          }, 0);
          this.selectedItem.to_assign = this.selectedItem.value - this.assigned;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      filterDataByUserRole (data) {
        const userRole = this.userInfo.rol_id;

        // Filtrar por ciudad si el rol del usuario es 4
        if (userRole === 4) {
          return data.filter(item => item.city === this.userInfo.city);
        }

        // Filtrar por ciudad y área para otros roles según sea necesario
        if (userRole === 5) {
          // Ajusta OTRRO_ROL_ID según el valor correspondiente
          return data.filter(item => item.city === this.userInfo.city && item.area === this.userInfo.area);
        }

        // Si el rol no requiere filtrado, devolver todos los datos sin cambios
        return data;
      },
      changeAnio () {
        this.overlay = true;
        this.budgetService.getAllByAnio(this.filter).then(response => {
          this.items = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      actualizarFechaCompuesta () {
        if (this.item.anio !== null) {
          this.periodData = `${this.mesInicial} ${this.item.anio} / ${this.mesFinal} ${Number(this.item.anio) + 1}`;
        }
      },
      getMainBudget () {
        this.overlay = true;
        this.budgetService.getMain().then(response => {
          this.isMain = response.data;
          console.log(this.isMain);
          const foundBudget = this.isMain.find(budget => budget.anio === this.filter.anio);

          if (foundBudget) {
            this.selectedItem = {
              id: foundBudget.id,
              anio: foundBudget.anio,
              value: foundBudget.value,
              spent: foundBudget.spent,
              city: foundBudget.city,
              area: foundBudget.area,
              executed_budget: foundBudget.value - foundBudget.spent,
              to_assign: foundBudget.value - this.assigned,
            };
          }
          console.log(this.selectedItem);
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      openDistributeBudget () {
        this.overlay = true;
        this.item.anio = this.filter.anio;
        this.budgetDistribution = true;
        this.overlay = false;
        this.isDistribution = true;
      },
      addData () {
        this.overlay = true;
        this.item.anio = null;
        this.item.value = null;
        this.item.is_main = 1;
        this.displayDialog = true;
        this.overlay = false;
        this.isEdit = false;
        this.periodData = null;
      },
      editBudgetMain () {
        this.overlay = true;
        this.item.anio = this.selectedItem.anio;
        this.item.value = this.selectedItem.value;
        this.item.is_main = 1;
        this.item.id = this.selectedItem.id;
        this.displayDialog = true;
        this.overlay = false;
        this.isEdit = true;
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
        this.budgetEditDistribution = true;
        this.resetForm();
      },
      openConfirm (item) {
        this.item = Object.assign({}, item);
        this.confirm = true;
      },
      storeDistribution () {
        if (this.$refs.frmDistribucion.validate()) {
          if (!this.isEdit) this.saveData();
          else this.updateData();
          this.getMainBudget();
        }
      },
      store () {
        if (this.$refs.form.validate()) {
          if (!this.isEdit) {
            const anioIngresado = parseInt(this.item.anio);
            const existeAnio = this.isMain.some(presupuesto => presupuesto.anio === anioIngresado);
            if (existeAnio) {
              this.msgValidateYear = 'El año ' + this.item.anio + ' ya tiene un presupuesto.';
              return;
            }
            this.saveData();
          } else { this.updateData(); }
          this.getMainBudget();
        }
      },
      storeBudgetAnio () {

      },
      resetForm () {
        if (this.isDistribution) this.$refs.form.reset(); else this.$refs.frmDistribucion.reset();
        this.item = {};
        console.log('Limpie el formulario....');
      },
      saveData () {
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.budgetService.store(this.item).then(response => {
          console.log(response);
          this.overlay = false;
          this.item = {};
          this.displayDialog = false;
          this.budgetDistribution = false;
          this.isDistribution = false;
          this.displayBudget = false;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: 'Presupuesto Creado con Éxito!',
          };
          this.resetForm();
        }).catch((error) => {
          this.overlay = false;
          this.item = model;

          // Verifica si error.response existe antes de intentar acceder a error.response.data
          const errorMessage = error.response && error.response.data && error.response.data.message
            ? getErrorMessage(error.response.data.message)
            : 'Se produjo un error inesperado. Por favor, inténtelo de nuevo.';

          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: errorMessage,
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
          this.budgetEditDistribution = false;
          this.displayDialog = false;
          this.displayBudget = false;
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
        console.log(this.item.spent);
        if (this.item.spent != null) {
          this.snackbar = {
            display: true,
            title: 'Info: ',
            type: 'error',
            message: 'El presupuesto no puede ser eliminado si tiene gasto.',
          };
          this.overlay = false;
          this.confirm = false;
          return;
        }
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
        this.resetForm();
        this.item = {};
        this.disabled = true;
        this.displayDialog = false;
        this.displayBudget = false;
        this.overlay = false;
        this.msgValidateYear = '';
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
              message: 'El área es requerida',
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
