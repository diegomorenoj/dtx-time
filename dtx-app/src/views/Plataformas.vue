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
          Gestion de proveedores y plataformas
        </p>
      </v-col>
      <v-col
        cols="6"
      >
        <div class="pb-2 text-right">
          <v-btn
            small
            color="info"
            min-width="100"
            class="mr-2"
            @click="addData()"
          >
            <v-icon left>
              mdi-plus
            </v-icon>
            Nuevo proveedor y/o plataforma
          </v-btn>
        </div>
      </v-col>
    </v-row>
    <v-divider class="mb-6 secondary" />
    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <material-card
      icon="mdi-cast-education"
      icon-small
      color="orange"
      title="Listado de plataformas"
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
          <template v-slot:[`item.actions`]="data">
            <div>
              <app-btn
                color="info"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
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
              Proveedor y/o Plataforma
            </div>
          </div>
        </template>
        <v-card-text>
          <v-form>
            <!-- INICIO DATOS USUARIO -->
            <v-row align="center">
              <v-col
                cols="4"
              >
                <v-select
                  v-model="item.type"
                  :items="lstType"
                  class="mb-n3"
                  label="Tipo de cargue"
                  item-text="name"
                  item-value="code"
                />
              </v-col>
              <v-col
                cols="8"
              >
                <v-text-field
                  v-model="item.name"
                  class="mb-n3"
                  label="Nombre"
                  type="text"
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
            ¿Esta seguro de eliminar el proveedor y/o plataforma?
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
  import ProviderService from '../services/ProviderService';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'ProvidersView',
    data: () => ({
      headers: [
        {
          text: 'ID',
          value: 'id',
          width: '10%',
        },
        {
          text: 'Nombre',
          value: 'name',
          width: '45%',
        },
        {
          text: 'Tipo de cargue',
          value: 'type_name',
          width: '35%',
        },
        {
          sortable: false,
          text: 'Opciones',
          value: 'actions',
          width: '10%',
          align: 'center',
        },
      ],
      items: [],
      item: {
        id: null,
        name: null,
        type: null,
      },
      lstType: [
        { code: 1, name: 'XLS' },
        { code: 2, name: 'Moodle' },
        { code: 3, name: 'Sistema' },
      ],
      displayDate: false,
      dialog: false,
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
    }),
    computed: {
      ...get('session', [
        'userInfo',
      ]),
    },
    created () {
      this.providerService = new ProviderService();
    },
    mounted () {
      this.loadData();
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.providerService.all().then(response => {
          this.items = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      addData () {
        this.overlay = true;
        this.item.type = null;
        this.item.name = null;
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
        this.providerService.create(this.item).then(response => {
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
        this.providerService.update(this.item, this.item.id).then(response => {
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
        this.providerService.delete(this.item.id).then(response => {
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
    },

  };
</script>
