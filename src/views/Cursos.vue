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
          Gestión de cursos
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <v-btn
            v-if="userInfo.permits.CREATE_COURSES"
            small
            color="success"
            min-width="100"
            class="mr-2"
            @click="addData()"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nuevo curso
          </v-btn>
          <v-btn
            v-if="userInfo.permits.IMPORT_COURSES"
            small
            color="primary"
            min-width="100"
            class="mr-2"
            @click="openImport(optionCourse)"
          >
            <v-icon left>
              mdi-upload
            </v-icon>
            Importar cursos
          </v-btn>
          <v-btn
            v-if="userInfo.permits.IMPORT_USERS_COURSES"
            small
            color="info"
            min-width="100"
            @click="openImport(optionUser)"
          >
            <v-icon left>
              mdi-upload
            </v-icon>
            Importar usuarios
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
          <v-col
            cols="3"
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
          <v-col
            cols="3"
          >
            <v-text-field
              v-model="filter.name"
              label="Buscar curso"
              type="text"
              prepend-icon="mdi-magnify"
              clearable
              @blur="loadData()"
            />
          </v-col>
          <v-col
            cols="2"
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
          <v-col
            cols="2"
          >
            <v-text-field
              v-model="filter.category"
              label="Categoría"
              type="text"
              prepend-icon="mdi-filter"
              clearable
              @blur="loadData()"
            />
          </v-col>
          <v-col
            cols="2"
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
    <v-row class="mb-6">
      <v-col
        cols="12"
      >
        <material-stat-card
          color="info"
          icon="mdi-school"
          title="Total cursos"
          :value="summary.total_courses"
          icon-small
        >
          <v-card-text class="mb-0 pb-0">
            <v-divider class="secondary" />&nbsp;
          </v-card-text>
        </material-stat-card>
      </v-col>
      <!-- <v-col
        v-for="(prov, i) in summary.providers"
        :key="i"
      >
        <material-stat-card
          color="info"
          icon="mdi-school"
          :title="prov.name"
          :value="prov.count"
          icon-small
        >
          <v-card-text class="mb-0 pb-0">
            <v-divider class="secondary" />&nbsp;
          </v-card-text>
        </material-stat-card>
      </v-col> -->
    </v-row>
    <material-card
      icon="mdi-account"
      icon-small
      color="orange"
      title="Resumen de cursos"
    >
      <v-card-text>
        <!-- <h1>Total de Cursos: {{ items.length }}</h1> -->
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
              <app-btn
                color="info"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                :disabled="data.item.origin === 'moodle' ? true : !userInfo.permits.UPDATE_COURSES"
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
                :disabled="data.item.origin === 'moodle' ? true : !userInfo.permits.DELETE_COURSES"
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
              CURSO
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col
                cols="12"
              >
                <v-text-field
                  v-model="item.shortname"
                  class="mb-n3"
                  label="Nombre del curso"
                  type="text"
                />
              </v-col>
            </v-row>
            <v-row align="center">
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.category"
                  class="mb-n3"
                  label="Categoria"
                  type="text"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-select
                  v-model="item.provider_id"
                  :items="lstProvider"
                  class="mb-n3"
                  label="Plataforma"
                  item-text="name"
                  item-value="id"
                />
              </v-col>
            </v-row>
            <!-- FECHAS -->
            <v-row align="center">
              <v-col>
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
              <v-col>
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
                cols="3"
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
                cols="6"
              >
                <v-select
                  v-model="item.specialty_id"
                  :items="lstSpecialities"
                  class="mb-n3"
                  label="Especialidad"
                  item-text="name"
                  item-value="id"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-select
                  v-model="item.required"
                  :items="lstRequerido"
                  class="mb-n3"
                  label="Requerido"
                  item-text="name"
                  item-value="code"
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
              Importar {{ optionImport }}
            </div>
          </div>
        </template>
        <v-card-text>
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
                {{ optionImport }} leidos:&nbsp;
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
              @click="importData()"
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
            ¿Esta seguro de eliminar el curso?
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
  import CourseService from '../services/CourseService';
  import UserService from '../services/UserService';
  import ParameterService from '../services/ParameterService';
  import ProviderService from '../services/ProviderService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'CursosView',
    components: {
      UploadDataComponent: () => import('../components/generic/UploadData') /* webpackChunkName: "default-drawer-toggle" */,
    },
    data: () => ({
      headers: [
        {
          text: 'ID del curso',
          value: 'id',
        },
        {
          text: 'Código',
          value: 'code',
        },
        {
          text: 'Fecha',
          value: 'date',
        },
        {
          text: 'Nombre',
          value: 'name',
        },
        {
          text: 'Plataforma',
          value: 'provider_name',
        },
        {
          text: 'Usuarios',
          value: 'users_count',
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
          text: 'Opciones',
          value: 'actions',
          width: '12%',
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
        name: null,
        specialty_id: null,
        category: null,
        status_id: null,
      },
      summary: {
        total_courses: '0',
        providers: [],
      },
      lstStatus: [],
      items: [],
      item: {
        id: null,
        name: null,
        shortname: null,
        start_date: null,
        hours: null,
        end_date: null,
        category: null,
        provider_id: null,
        specialty_id: null,
        status_id: null,
        required: null,
        users: [],
      },
      file: null,
      displayDate: false,
      display_start_date: false,
      display_end_date: false,
      displayDialogUsers: false,
      dialog: false,
      lstRequerido: [
        { code: 'S', name: 'Si' },
        { code: 'N', name: 'No' },
      ],
      lstSpecialities: [],
      lstProvider: [],
      courseService: null,
      parameterService: null,
      userService: null,
      providerService: null,
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
      // import
      optionCourse: 'Cursos',
      optionUser: 'Usuarios',
      optionImport: null,
      displayDialogImport: false,
      lstData: [],
      messageImport: [],
      progress: 0,
      displayLog: false,
    }),
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
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.filter.user_id = this.userInfo.id;
        this.courseService.getAllByFilter(this.filter).then(response => {
          this.items = response.data.data;
          console.log('loadData::::::::', response);
          this.summary = response.data.summary;
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
      addData () {
        this.overlay = true;
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
        console.log('USERS:::::', this.item.users);

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

        console.log('ITEM:::::', this.item);
      },
      saveData () {
        // GUARDAR SOLICTUD
        this.overlay = true;
        const model = Object.assign({}, this.item);
        this.courseService.create(this.item).then(response => {
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
        this.courseService.update(this.item, this.item.id).then(response => {
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
        this.courseService.delete(this.item.id).then(response => {
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
      },
      addUser () {
        if (this.userSelected != null) {
          this.item.users.push(this.userSelected);
          this.userSelected = {};
        }
      },
      removeUser (index) {
        this.item.users.splice(index, 1);
      },
      formatPrice (value) {
        let val = 0;
        val = (value / 1).toFixed(0).replace('.', ',');
        return '$ ' + val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
      },
      openImport (optionImport) {
        this.optionImport = optionImport;
        this.displayDialogImport = true;
        this.lstData = [];
      },
      loadExcel (data) {
        console.log('Data Excel::::', data);
        this.lstData = data;
        this.snackbar = {
          display: true,
          title: 'INFO: ',
          type: 'success',
          message: this.optionImport === this.optionCourse ? 'Cursos leidos correctamente.' : 'Usuarios leidos correctamente.',
        };
      },
      importData () {
        this.messageImport = [];
        this.overlay = true;
        console.log(this.lstData);
        let count = 0;
        this.lstData.forEach(item => {
          count++;
          // IMPORTAR
          this.overlay = true;
          if (this.optionImport === this.optionCourse) {
            this.courseService.importCourses(item, this.specialtyId).then(response => {
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
          } else {
            this.courseService.importUsers(item, this.specialtyId).then(response => {
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
          }
        });
      },
      openDialogUsers (item) {
        this.item = Object.assign({}, item);
        this.displayDialogUsers = true;
      },
    },

  };
</script>
