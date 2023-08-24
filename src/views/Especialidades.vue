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
          Gestion de especialidades
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <v-btn
            small
            color="success"
            min-width="100"
            class="mr-2"
            @click="addData()"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nueva especialidad
          </v-btn>
          <v-btn
            small
            color="primary"
            min-width="100"
            class="mr-2"
            @click="openImport(1)"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Importar individual
          </v-btn>
          <v-btn
            small
            color="info"
            min-width="100"
            @click="openImport(2)"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Importar grupal
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
          <v-col>
            <v-text-field
              v-model="filter.name"
              label="Buscar"
              type="text"
              prepend-icon="mdi-filter"
              @blur="loadData()"
            />
          </v-col>
        </v-row>
      </v-card-text>
    </material-card>
    <div class="m-0 p-0">
      &nbsp;
    </div>
    <material-card
      icon="mdi-cash"
      icon-small
      color="orange"
      title="Listado de especialidades"
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
            'itemsPerPageText':'Especialidades por página'
          }"
        >
          <template slot="no-data">
            No hay datos para mostrar
          </template>
          <template slot="no-results">
            No hay resultados para mostrar
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
                    v-on="on"
                    @click="editData(data.item)"
                  >
                    <v-icon
                      small
                      v-text="'mdi-pencil'"
                    />
                  </app-btn>
                </template>
                <span>Editar especialidad</span>
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
    <!-- INICIO CREAR/EDITAR -->
    <v-dialog
      v-model="displayDialog"
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
              Especialidad
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col>
                <v-text-field
                  v-model="item.name"
                  label="Nombre de la especialidad"
                  type="text"
                />
              </v-col>
            </v-row>

            <!-- INICIO SOLICITUD GRUPAL -->
            <v-row align="center">
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
            <!-- FIN LISTA USUARIOS -->
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
    <!-- FIN CREAR/EDITAR -->
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
    <v-dialog
      v-model="displayDialogImport"
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
              Importar usuarios
            </div>
          </div>
        </template>
        <v-card-text>
          <v-row
            v-if="!importGroup"
            align="center"
          >
            <v-col
              cols="12"
            >
              <v-select
                v-model="specialtyId"
                :items="items"
                label="Especialidad"
                item-text="name"
                item-value="id"
              />
            </v-col>
          </v-row>
          <v-row>
            <v-col
              cols="12"
            >
              <upload-data-component
                v-if="displayDialogImport"
                :read="loadExcel"
              />
              <span
                v-if="lstData.length > 0"
                class="font-weight-bold"
              >
                Usuarios leidos:&nbsp;
              </span> {{ lstData.length > 0 ? lstData.length : '' }}
            </v-col>
          </v-row>

          <v-progress-linear
            v-if="progress > 0"
            :value="progress"
            rounded
            class="mt-2"
            height="25"
          >
            <strong>{{ Math.ceil(progress) }}%</strong>
          </v-progress-linear>

          <div class="pa-3 text-center mt-2">
            <v-btn
              small
              color="error"
              min-width="100"
              @click="displayDialogImport = false; lstData = []; messageImport = []; loadData(); specialtyId = null; progress = 0"
            >
              Cerrar
            </v-btn>
            <v-btn
              v-if="lstData.length > 0 && messageImport.length == 0"
              small
              color="success"
              class="ml-2"
              min-width="100"
              @click="importUsers()"
            >
              Guardar
            </v-btn>
            <v-btn
              v-if="messageImport.length > 0"
              small
              color="info"
              class="ml-2"
              min-width="100"
              @click="displayLog = true"
            >
              Ver Log
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
    <v-dialog
      v-model="displayLog"
      persistent
      max-width="500"
    >
      <v-card>
        <v-card-title class="text-h4">
          Log importación
        </v-card-title>
        <v-card-text>
          <li
            v-for="(msg, i) in messageImport"
            :key="i"
            class="txt-success"
            :class="msg.status ? 'txt-success' : 'txt-error'"
          >
            {{ msg.message }}
          </li>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="warning"
            small
            @click="displayLog = false"
          >
            Cerrar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script>
  import SpecialtyService from '../services/SpecialtyService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'EspecialidadView',
    components: {
      UploadDataComponent: () => import('../components/generic/UploadData') /* webpackChunkName: "default-drawer-toggle" */,
    },
    data: () => ({
      headers: [
        {
          text: 'Código',
          value: 'id',
          width: '10%',
        },
        {
          text: 'Especialidad',
          value: 'name',
          width: '59%',
        },
        {
          text: 'Usuarios asignados',
          value: 'users_count',
          width: '20%',
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
      headersUsers: [
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
        {
          sortable: false,
          text: 'Eliminar',
          value: 'actions',
          width: '10%',
          align: 'center',
        },
      ],
      filter: {
        name: null,
        role_id: null,
      },
      items: [],
      item: {
        id: null,
        name: null,
        users: [],
      },
      displayDialogUsers: false,
      displayDialogImport: false,
      importGroup: false,
      specialtyService: null,
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
      userService: null,
      parameterService: null,
      // para el filtro de usuario
      userSelected: {},
      lstUsers: [],
      isLoading: false,
      searchUsers: null,
      lstData: [],
      progress: 0,
      specialtyId: null,
      messageImport: [],
      displayLog: false,
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
    },
    created () {
      this.specialtyService = new SpecialtyService();
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
        this.specialtyService.getAllByFilter(this.filter).then(response => {
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
          name: null,
          users: [],
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
      openImport (option) {
        this.importGroup = option === 2;
        this.displayDialogImport = true;
        this.lstData = [];
      },
      store () {
        if (!this.isEdit) this.saveData();
        else this.updateData();
      },
      saveData () {
        // GUARDAR SOLICTUD
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.specialtyService.create(this.item).then(response => {
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
        // ACTUALIZAR
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.specialtyService.update(this.item, this.item.id).then(response => {
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
        this.specialtyService.delete(this.item.id).then(response => {
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
        this.item = { users: [] };
        this.disabled = true;
        this.displayDialog = false;
        this.overlay = false;
        this.loadData();
      },
      openDialogUsers (item) {
        this.item = Object.assign({}, item);
        this.displayDialogUsers = true;
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
      importUsers () {
        this.messageImport = [];
        if (this.specialtyId == null && !this.importGroup) {
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: 'No ha seleccionado la espacialidad asociada.',
          };
          return;
        }
        this.overlay = true;
        console.log(this.lstData);
        let count = 0;
        this.lstData.forEach(item => {
          count++;
          // IMPORTAR
          this.overlay = true;
          this.specialtyService.storeExcel(item, this.specialtyId).then(response => {
            this.overlay = false;
            console.log(response.message);
            this.messageImport.push({ status: response.success, message: response.message });
            this.progress = ((count / this.lstData.length) * 100).toFixed(2);
          }).catch((error) => {
            this.overlay = false;
            this.progress = ((count / this.lstData.length) * 100).toFixed(2);
            console.log(getErrorMessage(error.response.data.message));
            this.messageError.push(getErrorMessage(error.response.data.message));
          });
        });
      },
      loadExcel (data) {
        console.log('Data Excel::::', data);
        this.lstData = data;
        this.snackbar = {
          display: true,
          title: 'INFO: ',
          type: 'success',
          message: 'Usuarios leidos correctamente.',
        };
      },
    },

  };
</script>
