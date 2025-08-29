<template>
  <v-container
    id="providers-view"
    fluid
    tag="section"
  >
    <v-row align="center">
      <v-col cols="6">
        <p class="mb-0 d-inline-block text-h4">
          Gestion de objetivos
        </p>
      </v-col>
      <v-col cols="6">
        <div class="pb-2 text-right">
          <download-excel
            class="ml-2 v-btn mr-5 v-btn--is-elevated v-btn--has-bg theme--light v-size--small success"
            :data="items"
            :fields="json_fields"
            worksheet="ObjetivosGenerales"
            name="ObjetivosGenerales.xls"
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
          <v-btn
            v-if="userInfo.permits.CREATE_OBJETIVES"
            small
            color="primary"
            min-width="100"
            class="mr-2"
            @click="dialogImportExcel = true"
          >
            <v-icon left>
              mdi-upload
            </v-icon>
            Cargar Excel
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-divider class="mb-6 secondary" />
    <div class="mb-3 mt-3">
&nbsp;
    </div>
    <material-card
      v-if="![2, 8, 9, 10].includes(userInfo.rol_id)"
      icon="mdi-filter"
      icon-small
      color="error"
      title="Filtros"
      class="mb-0"
    >
      <v-card-text>
        <v-row align="center">
          <v-col>
            <v-select
              v-model="filter.hasSpecialty"
              :items="specialtyOptions"
              label="Especialidad"
              placeholder="Selecciona una opción"
              prepend-icon="mdi-filter"
              @change="loadData()"
            />
          </v-col>
          <v-col>
            <v-autocomplete
              v-model="filter.specialty_id"
              :items="lstSpecialtyFilter"
              :loading="isLoadingSpecialty"
              :search-input.sync="searchSpecialty"
              cache-items
              hide-no-data
              hide-selected
              clearable
              item-text="name"
              item-value="id"
              label="Buscar Especialidad"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
            />
          </v-col>
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
                @change="
                  displayDate = false;
                  loadData();
                "
              />
            </v-menu>
          </v-col>
          <v-col>
            <v-autocomplete
              v-model="filter.user_name"
              :items="lstNameFilter"
              :loading="isLoadingPositions"
              :search-input.sync="searchNames"
              cache-items
              hide-no-data
              hide-selected
              clearable
              item-text="lastname"
              item-value="lastname"
              label="Buscar por nombre"
              placeholder="Empieza a escribir para Buscar"
              prepend-icon="mdi-database-search"
              @input="loadData()"
            />
          </v-col>
        </v-row>
        <v-row align="center">
          <v-col v-if="userInfo.rol_id !== 4 && userInfo.rol_id !== 5 && userInfo.rol_id !== 11">
            <v-autocomplete
              v-model="filter.city"
              :items="lstCityFilter"
              :loading="isLoadingCity"
              :search-input.sync="searchCity"
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
          <v-col v-if="userInfo.rol_id !== 5 && userInfo.rol_id !== 11">
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
        <v-divider class="mt-3" />

        <template>
          <v-data-table
            :headers="headers"
            :items="items"
            :search.sync="search"
            multi-sort
            must-sort
          >
            <template v-slot:item.total_completed_hours="{ item }">
              {{ round(item.total_completed_hours) }} %
            </template>
            <template v-slot:item.actions="{ item }">
              <v-btn
                color="success"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                @click="viewDetails(item)"
              >
                <v-icon
                  small
                  v-text="'mdi-eye'"
                />
              </v-btn>
              <v-btn
                color="primary"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                :disabled="!userInfo.permits.UPDATE_OBJETIVES"
                @click="editData(item)"
              >
                <v-icon
                  small
                  v-text="'mdi-pencil'"
                />
              </v-btn>
              <v-btn
                color="error"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                :disabled="!userInfo.permits.DELETE_OBJETIVES"
                @click="openConfirm(item)"
              >
                <v-icon
                  small
                  v-text="'mdi-delete'"
                />
              </v-btn>
            </template>
          </v-data-table>
        </template>
      </v-card-text>
    </material-card>

    <!-- Cursos por objetivo por usuario  -->
    <v-dialog
      v-model="displayCursosByUser"
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
              Cursos Inscritos
              {{ course_all.length ? course_all[0].username : "" }}
            </div>
          </div>
        </template>
        <v-card-text>
          <v-data-table
            :headers="headersCoursesList"
            :items="course_all"
            :search.sync="search"
            multi-sort
            must-sort
          >
            <template v-slot:item.progress="{ item }">
              {{ round(item.progress) }} %
            </template>
          </v-data-table>
          <div class="pa-3 text-center mt-2">
            <download-excel
              class="ml-2 v-btn mr-5 v-btn--is-elevated v-btn--has-bg theme--light v-size--small success"
              :data="course_all"
              :fields="json_fields_course"
              worksheet="CursosUsuario"
              name="CursoUsuario.xls"
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
            <v-btn
              small
              color="error"
              min-width="100"
              @click="displayCursosByUser = false"
            >
              Cancelar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
    </v-dialog>

    <!-- -->

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
              Objetivo general {{ item.user.name }}
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <v-row align="center">
              <v-col cols="6">
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
              <v-col cols="6">
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
              <v-col cols="6">
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
              @click="displayDialog = false"
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
              itemsPerPageText: 'Usuarios por página',
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
        ['right']: true,
      }"
    >
      <div>
        <span class="font-weight-bold">&nbsp;{{ snackbar.title }}&nbsp;</span>
        <span v-html="snackbar.message" />
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
          <span class="text-h5"> ¿Esta seguro de eliminar el objetivo? </span>
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
    <!-- Cuadro de diálogo para cargar archivo de excel al servidor -->
    <v-dialog
      v-model="dialogImportExcel"
      persistent
      max-width="600px"
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
              Importar Objetivos (Excel)
            </div>
          </div>
        </template>

        <v-card-text>
          <v-file-input
            v-model="file"
            label="Archivo"
            :show-size="1000"
          />
          <div
            v-if="errors"
            :style="{
              color: 'red',
              fontWeight: 'bold',
              backgroundColor: '#dfdfdf',
              marginTop: '10px',
              padding: '10px',
              fontSize: '18px',
            }"
            v-html="errors"
          />
          <div
            v-if="message"
            :style="{
              color: 'green',
              fontWeight: 'bold',
              backgroundColor: '#dfdfdf',
              marginTop: '10px',
              padding: '10px',
              fontSize: '18px',
            }"
            v-html="message"
          />
          <v-progress-linear
            v-if="progress > 0"
            :value="progress"
            rounded
            class="mt-2"
            height="25"
          >
            <strong>{{ Math.ceil(progress) }}%</strong>
          </v-progress-linear>
        </v-card-text>
        <v-card-actions class="pa-3 justify-center mt-2">
          <v-btn
            small
            color="error"
            min-width="100"
            @click="closeDialogImport"
          >
            Cerrar
          </v-btn>
          <v-btn
            small
            color="primary"
            min-width="100"
            @click="submitFile"
          >
            Subir
          </v-btn>
        </v-card-actions>
      </material-card>
    </v-dialog>
    <!-- ----- -->
  </v-container>
</template>

<script>
  import * as XLSX from 'xlsx';
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
          text: 'Usuario',
          value: 'user.name',
          width: '10%',
        },
        {
          text: 'Area',
          value: 'area',
          width: '9%',
        },
        {
          text: 'Cargo',
          value: 'position',
          width: '9%',
        },
        {
          text: 'Especialidad',
          value: 'specialty',
          width: '10%',
        },
        {
          text: 'Ciudad',
          value: 'user.city',
          width: '9%',
        },
        {
          text: 'Fecha Inicio',
          value: 'start_date',
          width: '10%',
        },
        {
          text: 'Fecha Fin',
          value: 'end_date',
          width: '10%',
        },
        {
          text: 'H. Objetivo',
          value: 'hours',
          width: '10%',
        },
        {
          text: 'Progreso',
          value: 'total_completed_hours',
        },
        {
          text: 'Acciones',
          value: 'actions',
        },
      ],
      headersCoursesList: [
        {
          text: 'Proveedor',
          value: 'provider',
        },
        {
          text: 'Curso',
          value: 'name',
        },
        {
          text: 'Horas',
          value: 'hours',
        },
        {
          text: 'Progreso',
          value: 'progress',
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
      json_fields: {
        Usuario: 'user.name',
        Area: 'area',
        Cargo: 'position',
        Nivel: 'user.level',
        Ciudad: 'user.city',
        Inicio: 'start_date',
        Fin: 'end_date',
        Horas: 'hours',
        Progreso: 'total_completed_hours',
      },
      json_fields_course: {
        Usuario: 'username',
        Curso: 'name',
        Progreso: 'progress',
        Calificacion: 'qualification',
        Horas: 'hours',
        Inicio: 'start_date',
        Fin: 'end_date',
      },
      filter: {
        range: null,
        user_id: null,
        area: null,
        position: null,
        role_id: null,
        user_name: null,
        city: null,
        specialty_id: null,
        hasSpecialty: null,
      },
      specialtyOptions: [
        { text: 'Todos', value: null },
        { text: 'General', value: false },
        { text: 'Con especialidad', value: true },
      ],
      items: [],
      course_all: [],
      item: {
        user: {
          name: null,
          area: null,
          position: null,
          level: null,
          city: null,
        },
        start_date: null,
        end_date: null,
        hours: null,
      },
      display_start_date: false,
      display_end_date: false,
      displayDate: false,
      dialog: false,
      displayDialogUsers: false,
      displayCursosByUser: false,
      // Variables de importación de archivo
      dialogImportExcel: false,
      file: null,
      progress: 0,
      errors: '',
      message: '',
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
      // filtro por ciudad
      isLoadingCity: false,
      lstCityFilter: [],
      searchCity: null,
      // Filtro especialidad
      isLoadingSpecialty: false,
      lstSpecialtyFilter: [],
      searchSpecialty: null,
      // filtro por nombres
      isLoadingName: false,
      lstNameFilter: [],
      searchNames: null,
      // filtro por niveles
      isLoadingLevels: false,
      lstLevelsFilter: [],
      searchLevels: null,
    }),
    computed: {
      ...get('session', ['userInfo']),
    },
    watch: {
      searchAreas (val) {
        this.isLoadingAreas = true;
        this.parameterService
          .filterAreas(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstAreasFiler = entries;
            this.isLoadingAreas = false;
          })
          .catch((error) => {
            console.log('Error::::::', error);
            this.isLoadingAreas = false;
          });
      },
      searchSpecialty (val) {
        this.isLoadingSpecialty = true;
        this.parameterService
          .filterSpecialties(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstSpecialtyFilter = entries;
            this.isLoadingSpecialty = false;
          })
          .catch((error) => {
            console.log('Error::::::', error);
            this.isLoadingSpecialty = false;
          });
      },
      searchNames (val) {
        this.isLoadingName = true;
        this.parameterService
          .filterByUserName(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstNameFilter = entries;
            this.isLoadingName = false;
          })
          .catch((error) => {
            console.log('Error::::::', error);
            this.isLoadingName = false;
          });
      },
      searchCity (val) {
        this.isLoadingCity = true;
        this.parameterService
          .filterCities(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstCityFilter = entries;
            this.isLoadingCity = false;
          })
          .catch((error) => {
            console.log('Error::::::', error);
            this.isLoadingCity = false;
          });
      },
      searchPositions (val) {
        this.isLoadingPositions = true;
        this.parameterService
          .filterPositions(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstPositionsFilter = entries;
            this.isLoadingPositions = false;
          })
          .catch((error) => {
            console.log('Error::::::', error);
            this.isLoadingPositions = false;
          });
      },
      searchLevels (val) {
        this.isLoadingLevels = true;
        this.parameterService
          .filterLevels(val)
          .then((res) => {
            const { count, entries } = res;
            this.count = count;
            this.lstLevelsFilter = entries;
            this.isLoadingLevels = false;
          })
          .catch((error) => {
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
      this.loadParameters();
      this.loadData();
      this.getAreas();
    },
    methods: {
      async viewDetails (filter) {
        try {
          this.overlay = true;
          const response = await this.objetiveService.getAllCourses(filter);
          console.log(response.data);
          this.course_all = response.data;
          await this.waitFor(500);
        } catch (error) {
          console.error(error);
        } finally {
          this.displayCursosByUser = true;
          this.overlay = false;
        }
      },
      waitFor (ms) {
        return new Promise((resolve) => setTimeout(resolve, ms));
      },
      loadData () {
        this.overlay = true;
        console.log('Al cargar datos --------------');
        console.log(this.filter);
        this.objetiveService
          .all(this.filter)
          .then((response) => {
            console.log(response); // Para verificar qué contiene response
            this.items = response; // Asignar directamente response a this.items
            this.overlay = false;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
      },
      roundToTwoDecimals (value) {
        return parseFloat(value).toFixed(2);
      },
      round (value) {
        return Math.round(value);
      },
      async submitFile () {
        if (!this.file) {
          alert('Por favor, selecciona un archivo.');
          return;
        }
        try {
          // Aquí invocas handleFileUpload y guardas el resultado en validationResult
          const validationResult = await this.handleFileUpload();

          // Aquí verificas el resultado

          if (!validationResult.isValid) {
            this.snackbar = {
              display: true,
              title: 'ERROR: ',
              type: 'error',
              message: `El archivo tiene errores:\n\n${validationResult.errors.join(
                '\n',
              )}`,
            };
            return;
          }

          // Usar FormData para enviar el archivo a través de axios
          const formData = new FormData();
          formData.append('file', this.file);

          // Simular progreso mientras se realiza la importación
          const progressInterval = setInterval(() => {
            if (this.progress < 95) {
              // 95 para evitar llegar a 100 antes de completar
              this.progress += 2; // Incrementar el progreso en 5%
            }
          }, 600); // Actualizar cada 200ms

          const response = await this.objetiveService.importExcel(formData);

          if (response.success === false && response.errors) {
            const uniqueErrors = [...new Set(response.errors)];
            this.errors = uniqueErrors.join('<br /> ');
          } else {
            this.message = 'Los Objetivos se importaron con éxito';
          }

          clearInterval(progressInterval); // Detener la simulación del progreso
          this.progress = 100; // Marcar como completo al finalizar
        } catch (error) {
          console.error('Error al subir el archivo:', error);
        }
      },
      closeDialogImport () {
        this.errors !== '' || this.message !== ''
          ? location.reload()
          : this.resetDialogValues();
      },
      resetDialogValues () {
        // Aquí reseteas todos los valores del diálogo a sus valores por defecto
        this.file = null;
        this.errors = '';
        this.message = '';
        this.dialogImportExcel = false;
      // Y cualquier otra variable que necesites resetear.
      },
      handleFileUpload () {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();

          reader.onload = (e) => {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet);

            const validationResult = this.validateObjetive(jsonData);

            resolve({
              isValid: validationResult.isValid, // Aquí estaba el error
              errors: validationResult.errors, // Aquí estaba el error
            });
          };

          reader.onerror = (error) => {
            reject(error);
          };

          reader.readAsArrayBuffer(this.file);
        });
      },
      validateObjetive (data) {
        this.validationErrors = [];

        const columnsToCheck = [
          {
            key: 'user_email',
            validation: (val) => typeof val === 'string',
            typeDescription: 'String',
          },
          {
            key: 'start_date',
            validation: (val) => typeof val === 'string',
            typeDescription: 'String',
          },
          {
            key: 'end_date',
            validation: (val) => typeof val === 'string',
            typeDescription: 'String',
          },
          {
            key: 'hours',
            validation: (val) => !isNaN(parseFloat(val)) && isFinite(val),
            typeDescription: 'Numérico',
          },
        ];

        for (let index = 0; index < data.length; index++) {
          const item = data[index];
          const rowNumber = index + 2;

          for (const col of columnsToCheck) {
            const value = item[col.key];
            if (value === null || value === undefined || !col.validation(value)) {
              const error = `Fila ${rowNumber}: Error en la columna '${col.key}'. El campo no puede estar vacío y debe ser del tipo '${col.typeDescription}'.`;
              this.validationErrors.push(error);
              return { isValid: false, errors: this.validationErrors };
            }
          }
        }
        return { isValid: true, errors: [] };
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
          .getPositionsByArea(this.item.area)
          .then((response) => {
            this.lstPositions = response.data;
            this.overlay = false;
          })
          .catch((error) => {
            console.log(error);
            this.overlay = false;
          });
      },
      getLevelsByPosition () {
        this.overlay = true;
        this.parameterService
          .getLevelsByPosition(this.item.position)
          .then((response) => {
            this.lstLevels = response.data;
            this.overlay = false;
          })
          .catch((error) => {
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
        this.objetiveService
          .create(this.item)
          .then((response) => {
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
          })
          .catch((error) => {
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
        this.objetiveService
          .update(this.item, this.item.id)
          .then((response) => {
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
          })
          .catch((error) => {
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
        this.objetiveService
          .delete(this.item.id)
          .then((response) => {
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
          })
          .catch((error) => {
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
        budgets.forEach((bg) => {
          _budget = Number(_budget) + Number(bg.value);
        });

        this.item.available_budget = Number(this.item.value) - _budget;
      },
      loadParameters () {
        this.overlay = true;

        // SI EL ROL ES 4
        if (this.userInfo.rol_id === 4) {
          this.filter.city = this.userInfo.city;
        }

        // SI EL ROL ES 5
        if (this.userInfo.rol_id === 5 || this.userInfo.rol_id === 11) {
          this.filter.area = this.userInfo.area;
          this.filter.city = this.userInfo.city;
        }

        // SI EL ROL ES 2
        if ([2, 8, 9, 10].includes(this.userInfo.rol_id)) {
          this.filter.user_id = this.userInfo.id;
        }

        console.log(this.filter);
      },
      openDialogUsers (item) {
        this.item = Object.assign({}, item);
        this.displayDialogUsers = true;
      },
    },
  };
</script>
