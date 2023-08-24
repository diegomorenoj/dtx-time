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
          Gestión de usuarios
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
            @click="importLdapUsers()"
          >
            <v-icon left>
              mdi-reload
            </v-icon>
            Sincronizar usuarios
          </v-btn>

          <download-excel
            class="ml-2 v-btn v-btn--is-elevated v-btn--has-bg theme--light v-size--small primary"
            :data="items"
            :fields="json_fields"
            worksheet="Usuarios"
            name="usuarios.xls"
          >
            <v-icon
              left
              dark
              small
            >
              mdi-arrow-down
            </v-icon>
            Descargar &nbsp;usuarios&nbsp;
          </download-excel>
        </div>
      </v-col>
    </v-row>
    <v-divider class="mb-6 secondary" />
    <div class="mb-3 mt-3">
      &nbsp;
    </div>
    <material-card
      icon="mdi-account"
      icon-small
      color="orange"
      title="Listado de usuarios"
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
          :sort-by="['name', 'position']"
          multi-sort
        >
          <template v-slot:[`item.actions`]="data">
            <div>
              <app-btn
                color="success"
                class="px-2 ml-1"
                elevation="0"
                min-width="0"
                small
                @click="viewData(data.item, true)"
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
                @click="viewData(data.item, false)"
              >
                <v-icon
                  small
                  v-text="'mdi-cog'"
                />
              </app-btn>
            </div>
          </template>
        </v-data-table>
      </v-card-text>
    </material-card>

    <v-dialog
      v-model="displayUser"
      persistent
      max-width="600"
    >
      <material-card
        full-header
        light
        color="info"
        class="mx-auto"
      >
        <template #heading>
          <div class="text-center pa-5">
            <div class="text-h4 white--text">
              {{ item.name }}
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
                  v-model="item.position"
                  class="mb-n3"
                  label="Cargo"
                  type="text"
                  :disabled="true"
                />
                <v-text-field
                  v-model="item.email"
                  class="mb-n3"
                  label="Correo"
                  type="text"
                  :disabled="true"
                />
                <v-text-field
                  v-model="item.city"
                  class="mb-n3"
                  label="Ciudad"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="6"
              >
                <v-text-field
                  v-model="item.area"
                  class="mb-n3"
                  label="Área"
                  type="text"
                  :disabled="true"
                />
                <v-text-field
                  v-model="item.phone"
                  class="mb-n3"
                  label="Teléfono"
                  type="text"
                  :disabled="true"
                />
                <v-select
                  v-model="item.rol_id"
                  :items="rols"
                  class="mb-n3"
                  label="Rol"
                  item-text="name"
                  item-value="id"
                  :disabled="disabled"
                />
              </v-col>
            </v-row>
          </v-form>
          <div class="pa-3 text-center">
            <v-btn
              small
              color="error"
              min-width="100"
              @click="closeDialog()"
            >
              Cancelar
            </v-btn>
            <v-btn
              v-if="!disabled"
              small
              color="warning"
              class="ml-2"
              min-width="100"
              @click="saveData()"
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
      <span class="font-weight-bold">&nbsp;INFO&nbsp;</span> — {{ snackbar.message }}.
    </material-snackbar>
  </v-container>
</template>

<script>
  import UserService from '../services/UserService';
  import RolService from '../services/RolService';

  export default {
    name: 'UsuariosView',

    data: () => ({
      headers: [
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
          text: 'Rol en la plataforma',
          value: 'role_name',
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
      item: {},
      rols: [],
      userService: null,
      rolService: null,
      search: undefined,
      overlay: false,
      displayUser: false,
      disabled: false,
      snackbar: {
        display: false,
        type: 'success',
        message: null,
      },
      json_fields: {
        Usuario: 'lastname',
        Cargo: 'position',
        Ciudad: 'city',
        Área: 'area',
        Teléfono: 'phone',
        Rol: 'role_name',
      },
    }),
    created () {
      this.userService = new UserService();
      this.rolService = new RolService();
    },
    mounted () {
      this.loadData();
      this.loadRols();
    },
    methods: {
      loadData () {
        this.overlay = true;
        this.userService.all().then(response => {
          console.log('Response:::', response);
          this.items = response.data;
          setTimeout(() => {
            this.overlay = false;
          }, 500);
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      loadRols () {
        this.overlay = true;
        this.rolService.all().then(response => {
          console.log('Rols:::', response);
          this.rols = response.data;
          setTimeout(() => {
            this.overlay = false;
          }, 500);
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      viewData (item, disabled) {
        this.overlay = true;
        this.disabled = disabled;
        console.log('Item:::::::', item);
        this.item = Object.assign({}, item);
        setTimeout(() => {
          this.overlay = false;
          this.displayUser = true;
        }, 500);
      },
      closeDialog () {
        this.item = {};
        this.disabled = true;
        this.displayUser = false;
      },
      saveData () {
        // UPDATE
        this.overlay = true;
        const id = this.item.id;
        this.userService.update(this.item, id).then(response => {
          console.log('Response:::', response);
          this.overlay = false;
          this.item = {};
          this.displayUser = false;
          this.loadData();
          this.snackbar = {
            display: true,
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log(error);
          this.snackbar = {
            display: true,
            type: 'error',
            message: 'Error',
          };
        });
      },
      importLdapUsers () {
        this.overlay = true;
        this.userService.importLdapUsers().then(response => {
          console.log('Response:::', response);
          this.overlay = false;
          this.loadData();
          this.snackbar = {
            display: true,
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
    },

  };
</script>
