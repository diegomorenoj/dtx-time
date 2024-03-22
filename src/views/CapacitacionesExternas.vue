<template>
  <v-container
    id="usuarios-view"
    fluid
    tag="section"
  >
    <v-row align="center">
      <v-col
        cols="4"
      >
        <p class="mb-0 d-inline-block text-h4">
          Capacitaciones Externas
        </p>
      </v-col>
      <v-col>
        <div class="pb-2 text-right">
          <v-btn
            v-if="userInfo.permits.CREATE_TRAINING_TERCERO"
            small
            color="primary"
            min-width="100"
            class="mr-2"
            @click="addData('T')"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nueva solicitud - Tercero
          </v-btn>
          <v-btn
            v-if="userInfo.permits.CREATE_TRAINING"
            small
            color="info"
            min-width="100"
            class="mr-2"
            @click="addData('N')"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nueva solicitud
          </v-btn>
          <v-btn
            v-if="userInfo.permits.CREATE_TRAINING"
            small
            color="info"
            min-width="100"
            @click="addData('S')"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nueva solicitud grupal
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
      color="orange"
      title="Filtros de informe"
      class="mb-6"
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
          <v-col
            cols="3"
          >
            <v-autocomplete
              v-model="filter.user_name"
              :items="lstUsers"
              :loading="isLoading"
              :search-input.sync="searchUsersFilter"
              hide-no-data
              hide-selected
              clearable
              :disabled="!userInfo.permits.SEARCH_USERS_TRAINING"
              item-text="lastname"
              item-value="lastname"
              label="Buscar usuario"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
            />
          </v-col>
          <v-col
            cols="3"
          >
            <v-text-field
              v-model="filter.name"
              label="Curso"
              type="text"
              prepend-icon="mdi-filter"
              :disabled="!userInfo.permits.SEARCH_COURSES_TRAINING"
              @blur="loadData()"
            />
          </v-col>
          <v-col
            cols="2"
          >
            <v-text-field
              v-model="filter.group"
              label="Grupo"
              type="text"
              prepend-icon="mdi-magnify"
              :disabled="!userInfo.permits.SEARCH_GROUPS_TRAINING"
              @blur="loadData()"
            />
          </v-col>
        </v-row>
        <v-row align="center">
          <v-col
            cols="3"
          >
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
              :disabled="!userInfo.permits.SEARCH_AREAS_TRAINING"
              @input="loadData()"
            />
          </v-col>
          <v-col
            cols="3"
          >
            <v-text-field
              v-model="filter.position"
              label="Cargo"
              type="text"
              prepend-icon="mdi-filter"
              :disabled="!userInfo.permits.SEARCH_POSITIONS_TRAINING"
              @blur="loadData()"
            />
          </v-col>
          <v-col
            cols="3"
          >
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
              :disabled="!userInfo.permits.SEARCH_CITIES_TRAINING"
              @input="loadData()"
            />
          </v-col>
          <v-col
            cols="3"
          >
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
    <v-row
      v-if="false"
      class="mb-6"
    >
      <v-col
        v-if="userInfo.permits.FEE_AVAILABLE_TRAINING"
        cols="4"
      >
        <material-stat-card
          color="primary"
          icon="mdi-cash-100"
          title="Presupuesto gastado"
          :value="formatPrice(summary.budget_spent)"
          icon-small
        >
          <v-card-text class="mb-0 pb-0">
            <v-divider class="secondary" />&nbsp;
          </v-card-text>
        </material-stat-card>
      </v-col>
      <v-col
        v-if="userInfo.permits.FEE_SPEND_TRAINING"
        cols="4"
      >
        <material-stat-card
          color="primary"
          icon="mdi-currency-usd"
          title="Presupuesto disponible"
          :value="formatPrice(summary.budget_available)"
          icon-small
        >
          <v-card-text class="mb-0 pb-0">
            <v-divider class="secondary" />&nbsp;
          </v-card-text>
        </material-stat-card>
      </v-col>
      <v-col
        v-if="userInfo.permits.APPROVED_TRAINING"
        cols="4"
      >
        <material-stat-card
          color="primary"
          icon="mdi-check-circle"
          title="Capacitaciones por aprobar"
          :value="summary.training_to_approved"
          icon-small
        >
          <v-card-text class="mb-0 pb-0">
            <v-divider class="secondary" />&nbsp;&nbsp;
          </v-card-text>
        </material-stat-card>
      </v-col>
    </v-row>
    <material-card
      icon="mdi-account"
      icon-small
      color="orange"
      title="Resumen de solicitudes"
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
            'itemsPerPageText':'Solicitudes por página'
          }"
        >
          <template slot="no-data">
            No hay datos para mostrar
          </template>
          <template slot="no-results">
            No hay resultados para mostrar
          </template>
          <template v-slot:[`item.fee`]="data">
            {{ formatPrice(data.item.fee) }}
          </template>
          <template v-slot:[`item.actions`]="data">
            <div>
              <app-btn
                color="success"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                :disabled="!userInfo.permits.VIEW_TRAINING"
                @click="manageData(data.item)"
              >
                <v-icon
                  small
                  v-text="'mdi-eye'"
                />
              </app-btn>
              <app-btn
                color="info"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                :disabled="!userInfo.permits.UPDATE_TRAINING"
                @click="editData(data.item)"
              >
                <v-icon
                  small
                  v-text="'mdi-pencil'"
                />
              </app-btn>
              <app-btn
                color="error"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                :disabled="!userInfo.permits.DELETE_TRAINING"
                @click="openConfirm(data.item)"
              >
                <v-icon
                  small
                  v-text="'mdi-delete'"
                />
              </app-btn>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </material-card>
    <v-dialog
      v-model="displayDialog"
      persistent
      max-width="810"
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
              Solicitud de capacitación externa {{ isTercero ? ' tercero' : '' }}
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <!-- INICIO SOLICITUD GRUPAL -->
            <v-row
              v-if="isTercero"
              align="center"
            >
              <v-col
                cols="12"
              >
                <v-autocomplete
                  v-model="userSelected"
                  :items="lstUsers"
                  :loading="isLoading"
                  :search-input.sync="searchUsers"
                  hide-no-data
                  hide-selected
                  clearable
                  item-text="lastname"
                  item-value="id"
                  label="Usuarios"
                  placeholder="Empieza a escribir para Buscar"
                  prepend-icon="mdi-database-search"
                  return-object
                  class="mb-n3"
                  @change="selectedTercero()"
                />
              </v-col>
            </v-row>
            <!-- INICIO DATOS USUARIO -->
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.user_name"
                  class="mb-n3"
                  label="Nombres"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.lastname"
                  class="mb-n3"
                  label="Apellidos"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.position"
                  class="mb-n3"
                  label="Cargo"
                  type="text"
                  :disabled="true"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.area"
                  class="mb-n3"
                  label="Área"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.email"
                  class="mb-n3"
                  label="Email"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.city"
                  class="mb-n3"
                  label="Ciudad"
                  type="text"
                  :disabled="true"
                />
              </v-col>
            </v-row>
            <!-- FIN DATOS USUARIO -->
            <!-- INICIO SOLICITUD GRUPAL -->
            <v-row
              v-if="item.group == 'S'"
              align="center"
            >
              <v-col
                cols="5"
              >
                <v-autocomplete
                  v-model="userSelected"
                  :items="lstUsers"
                  :loading="isLoading"
                  :search-input.sync="searchUsers"
                  hide-no-data
                  hide-selected
                  clearable
                  item-text="lastname"
                  item-value="id"
                  label="Usuarios"
                  placeholder="Empieza a escribir para Buscar"
                  prepend-icon="mdi-database-search"
                  return-object
                  class="mb-n3"
                />
              </v-col>
              <v-col
                cols="3"
              >
                <v-text-field
                  v-if="userSelected"
                  v-model="userSelected.area"
                  class="mb-n3"
                  label="Área"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="3"
              >
                <v-text-field
                  v-if="userSelected"
                  v-model="userSelected.position"
                  class="mb-n3"
                  label="Cargo"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="1"
              >
                <v-btn
                  v-if="userSelected"
                  class="mb-n3 float-right"
                  min-width="0"
                  icon
                  color="success"
                  @click="addUser()"
                >
                  <v-icon>mdi-plus-circle</v-icon>
                </v-btn>
              </v-col>
            </v-row>
            <v-row
              v-if="item.group == 'S'"
              align="center"
            >
              <v-col
                cols="12"
              >
                <v-text-field
                  v-model="searchUsr"
                  append-icon="mdi-magnify"
                  class="ml-auto"
                  hide-details
                  label="Buscar usuario"
                  single-line
                />

                <v-divider class="mt-3" />

                <v-data-table
                  :headers="headersUsers"
                  :items="item.users"
                  :search.sync="searchUsr"
                  multi-sort
                  must-sort
                  :footer-props="{
                    showFirstLastPage: true,
                    'itemsPerPageText':'Usuarios por página'
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
                      <v-btn
                        class="float-center"
                        min-width="0"
                        icon
                        color="error"
                        @click="removeUser(data.item)"
                      >
                        <v-icon>mdi-minus-circle</v-icon>
                      </v-btn>
                    </div>
                  </template>
                </v-data-table>
              </v-col>
            </v-row>

            <!-- FIN SOLICITUD GRUPAL -->

            <v-row align="center">
              <v-col
                cols="12"
              >
                <v-text-field
                  v-model="item.shortname"
                  class="mb-n3"
                  label="Nombre de la capacitación"
                  type="text"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-select
                  v-model="item.type"
                  :items="lstType"
                  class="mb-n3"
                  label="Tipo"
                  item-text="name"
                  item-value="code"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.fee"
                  class="mb-n3"
                  label="Valor solicitado $"
                  type="number"
                  :disabled="item.type != 'P'"
                />
              </v-col>
              <v-col
                cols="4"
              >
                <v-select
                  v-model="item.methodology"
                  :items="lstMethodology"
                  class="mb-n3"
                  label="Metodología"
                  item-text="name"
                  item-value="code"
                />
              </v-col>
            </v-row>
            <!-- FECHAS -->
            <v-row align="center">
              <v-col
                cols="4"
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
                cols="4"
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
                    @input="display_end_date = false"
                  />
                </v-menu>
              </v-col>
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.hours"
                  label="Total horas"
                  type="number"
                />
              </v-col>
            </v-row>
            <!-- FIN FECHAS -->
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-select
                  v-model="item.permission"
                  :items="lstPermission"
                  class="mb-n3"
                  label="¿Requiere permiso de horario?"
                  item-text="name"
                  item-value="code"
                />
              </v-col>
              <v-col
                cols="8"
              >
                <v-text-field
                  v-model="item.schedule"
                  class="mb-n3"
                  label="Describa el horario del permiso"
                  type="text"
                  prepend-icon="mdi-clock"
                  hint="Requiero permiso todos los lunes de 9:00 am a 10:00 am."
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-text-field
                  v-model="item.institute"
                  class="mb-n3"
                  label="Entidad / Proveedor"
                  type="text"
                />
              </v-col>
              <v-col
                cols="8"
              >
                <v-file-input
                  v-model="file"
                  label="Adjuntar archivo"
                  class="mb-n3"
                />
              </v-col>
            </v-row>
            <v-row
              align="center"
              class="pt-3 mt-3"
            >
              <v-col
                cols="12"
              >
                <v-textarea
                  v-model="item.comments"
                  label="Comentario"
                  height="60"
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
    <v-dialog
      v-model="dialog"
      fullscreen
      hide-overlay
      transition="dialog-bottom-transition"
      scrollable
    >
      <v-card tile>
        <v-toolbar
          extended
          dark
          color="primary"
          extension-height="10"
        >
          <v-btn
            icon
            dark
            @click="closeManageData()"
          >
            <v-icon>mdi-arrow-left</v-icon>
          </v-btn>
          <v-toolbar-title>Gestión Capacitación Externa</v-toolbar-title>
        </v-toolbar>
        <v-card-text class="bg-gray">
          <default-capacitacion-externa
            v-if="dialog"
            :id="item.id"
          />
        </v-card-text>
      </v-card>
    </v-dialog>
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
            ¿Esta seguro de eliminar la solicitud?
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
  import TrainingRequestService from '../services/TrainingRequestService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'CapacitacionesExternasView',
    components: {
      DefaultCapacitacionExterna: () => import('../components/generic/CapacitacionExterna'), /* webpackChunkName: "default-drawer-toggle" */
    },
    data: () => ({
      headers: [
        {
          text: 'Fecha',
          value: 'date',
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
          text: 'Usuario',
          value: 'lastname',
        },
        {
          text: 'Cargo',
          value: 'position',
        },
        {
          text: 'Curso',
          value: 'shortname',
        },
        {
          text: 'Costo',
          value: 'fee',
        },
        {
          text: 'Horas',
          value: 'hours',
        },
        {
          text: 'Estado solicitud',
          value: 'status_name',
        },
        {
          sortable: false,
          text: 'Opciones',
          value: 'actions',
          width: '12%',
          align: 'center',
        },
      ],
      headersUsers: [
        {
          text: 'Usuario',
          value: 'lastname',
        },
        {
          text: 'Área',
          value: 'area',
        },
        {
          sortable: false,
          text: 'Eliminar',
          value: 'actions',
          width: '10%',
          align: 'center',
        },
      ],
      filter: {
        range: null,
        user_id: null,
        user_name: null,
        user: null,
        name: null,
        group: null,
        area: null,
        position: null,
        city: null,
        status_id: null,
        role_id: null,
      },
      summary: {
        budget_spent: null,
        budget_available: null,
        training_to_approved: null,
      },
      lstStatus: [],
      items: [],
      item: {
        id: null,
        user_id: null,
        user_name: null,
        lastname: null,
        position: null,
        category: null,
        area: null,
        email: null,
        city: null,
        type: null,
        shortname: null,
        start_date: null,
        hours: null,
        end_date: null,
        fee: null,
        permission: null,
        schedule: null,
        methodology: null,
        institute: null,
        group: 'N',
        users: [],
      },
      file: null,
      displayDate: false,
      display_start_date: false,
      display_end_date: false,
      dialog: false,
      lstType: [
        { code: 'G', name: 'Gratis' },
        { code: 'P', name: 'Pago' },
      ],
      lstPermission: [
        { code: 'S', name: 'Si' },
        { code: 'N', name: 'No' },
      ],
      lstMethodology: [
        { code: 'P', name: 'Presencial' },
        { code: 'V', name: 'Virtual' },
      ],
      trainingRequestService: null,
      parameterService: null,
      userService: null,
      search: undefined,
      searchUsr: undefined,
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
      json_fields: {
        Usuario: 'name',
        Cargo: 'position',
        Ciudad: 'city',
        Área: 'area',
        Teléfono: 'phone',
        Rol: 'role_name',
      },
      // para el filtro de usuario
      userSelected: {},
      lstUsers: [],
      isLoading: false,
      searchUsers: null,
      searchUsersFilter: null,
      // filtro por cursos
      isLoadingCourses: false,
      lstCourses: [],
      searchCourses: null,
      // filtro por ciudades
      isLoadingCities: false,
      lstCities: [],
      searchCities: null,
      // filtro por areas
      isLoadingAreas: false,
      lstAreas: [],
      searchAreas: null,
      // CAPACITACIÓN PARA TERCERO
      isTercero: false,
    }),
    computed: {
      ...get('session', [
        'userInfo',
      ]),
    },
    watch: {
      searchUsers (val) {
        this.isLoading = true;
        this.parameterService.filterUsers(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstUsers = entries;
          this.isLoading = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoading = false;
        });
      },
      searchUsersFilter (val) {
        this.isLoading = true;
        this.parameterService.filterUsers(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstUsers = entries;
          this.isLoading = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoading = false;
        });
      },
      searchCourses (val) {
        this.isLoadingCourses = true;
        this.trainingRequestService.filter(val).then(res => {
          const { count, entries } = res;
          this.count = count;
          this.lstCourses = entries;
          this.isLoadingCourses = false;
        }).catch((error) => {
          console.log('Error::::::', error);
          this.isLoadingCourses = false;
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
      searchAreas (val) {
        this.isLoadingAreas = true;
        this.parameterService.filterAreas(val).then(res => {
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
      this.trainingRequestService = new TrainingRequestService();
      this.userService = new UserService();
      this.parameterService = new ParameterService();
    },
    mounted () {
      this.loadData();
      this.loadParameters();
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.filter.user_id = this.userInfo.id;
        console.log(this.filter);
        this.trainingRequestService.getAllByFilter(this.filter).then(response => {
          this.items = response.data.data;
          this.summary = response.data.summary;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      loadParameters () {
        this.overlay = true;
        this.parameterService.getByType(1).then(response => {
          this.lstStatus = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      addData (group) {
        this.item = {};
        this.overlay = true;
        // VALIDAR SI NO ES A NOMBRE PROPIO
        this.isTercero = group === 'T';
        if (!this.isTercero) {
          this.item.user_id = this.userInfo.id;
          this.item.user_name = this.userInfo.name;
          this.item.lastname = this.userInfo.lastname;
          this.item.position = this.userInfo.position;
          this.item.area = this.userInfo.area;
          this.item.email = this.userInfo.email;
          this.item.city = this.userInfo.city;
        }
        this.item.status_id = 1;
        this.item.group = group === 'S' ? group : 'N';
        if (group === 'S') {
          this.item = {
            ...this.item, // Esto mantiene las propiedades existentes de `item`
            users: [], // Esto inicializa `users` como un arreglo vacío
          };
        } else {
          this.item = {
            ...this.item, // Esto mantiene las propiedades existentes de `item`
            users: null, // Esto asigna `users` a null si `group` no es 'S'
          };
        }
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
        // VALIDAR QUE SE AGREGE LOS USUARIOS
        if (this.item.group === 'S' && this.item.users.length === 0) {
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: 'No ha seleccionado ningún usuario para la capacitación.',
          };
          return false;
        }

        if (!this.isEdit) this.saveData();
        else this.updateData();
      },
      saveData () {
        // GUARDAR SOLICTUD
        this.overlay = true;
        const model = Object.assign({}, this.item);
        const formData = new FormData();
        formData.append('data', JSON.stringify(this.item));
        formData.append('file', this.file);
        this.trainingRequestService.create(formData).then(response => {
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
        const formData = new FormData();
        formData.append('data', JSON.stringify(this.item));
        formData.append('file', this.file);
        formData.append('_method', 'PUT');
        this.trainingRequestService.update(formData, this.item.id).then(response => {
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
        });
      },
      deleteData () {
        // UPDATE
        this.overlay = true;
        this.trainingRequestService.delete(this.item.id).then(response => {
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
      manageData (item) {
        this.item = Object.assign({}, item);
        this.dialog = true;
      },
      closeDialog () {
        this.item = {};
        this.disabled = true;
        this.displayDialog = false;
        this.overlay = false;
      },
      closeManageData () {
        this.item = {};
        this.dialog = false;
        this.loadData();
      },
      addUser () {
        if (this.userSelected != null) {
          const found = this.item.users.find(usr => usr.id === this.userSelected.id);
          if (found === undefined) this.item.users.push(this.userSelected);
          else {
            this.snackbar = {
              display: true,
              title: 'ERROR: ',
              type: 'error',
              message: 'El usuario ya se ecnuentra asignado',
            };
          }
          this.userSelected = {};
        }
      },
      removeUser (item) {
        const index = this.item.users.findIndex((element) => element.id === item.id);
        console.log('removeUser:::::::', index);
        this.item.users.splice(index, 1);
      },
      formatPrice (value) {
        let val = 0;
        val = (value / 1).toFixed(0).replace('.', ',');
        return '$ ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      },
      selectedTercero () {
        if (this.userSelected && this.userSelected.id) {
          this.item.user_id = this.userSelected.id;
          this.item.user_name = this.userSelected.name;
          this.item.lastname = this.userSelected.lastname;
          this.item.position = this.userSelected.position;
          this.item.area = this.userSelected.area;
          this.item.email = this.userSelected.email;
          this.item.city = this.userSelected.city;
        } else {
          this.item.user_id = null;
          this.item.user_name = null;
          this.item.lastname = null;
          this.item.position = null;
          this.item.area = null;
          this.item.email = null;
          this.item.city = null;
          this.userSelected = {};
        }
        this.loadData();
      },
    },

  };
</script>
