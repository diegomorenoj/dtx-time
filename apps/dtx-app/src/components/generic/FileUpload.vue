<template>
  <v-container
    id="file-upload-view"
    fluid
    tag="section"
  >
    <v-dialog
      v-model="display"
      persistent
      max-width="450"
    >
      <v-card>
        <v-card-title class="text-h4">
          Carga de Documentos
        </v-card-title>
        <v-card-text class="mt-3">
          <v-file-input
            v-model="file"
            color="deep-purple accent-4"
            label="Documento"
            placeholder="Seleccionar..."
            prepend-icon="mdi-paperclip"
            outlined
            :show-size="1000"
          >
            <template v-slot:selection="{ text }">
              <v-chip
                color="deep-purple accent-4"
                dark
                label
                small
              >
                {{ text }}
              </v-chip>
            </template>
          </v-file-input>
          <div>
            <span class="font-weight-bold">Nombre:&nbsp;</span> {{ file ? file.name : '' }}
          </div>
          <div>
            <span class="font-weight-bold">Tamaño:&nbsp;</span> {{ file ? file.size : 0 }} (bytes)
          </div>
          <div>
            <span class="font-weight-bold">Extensión:&nbsp;</span> {{ extension }}
          </div>
        </v-card-text>
        <v-card-actions class="pb-4">
          <v-spacer />
          <v-btn
            color="warning"
            class="col-3"
            small
            @click="confirm = false"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="success"
            class="col-3"
            small
            @click="uploadFile()"
          >
            Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
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
  import FileService from '../../services/FileService';
  export default {
    name: 'DefaultFileUpload',
    props: {
      id: {
        type: Number,
        default: null,
      },
      table: {
        type: String,
        default: null,
      },
      display: {
        type: Boolean,
        default: false,
      },
    },
    data: () => ({
      fileService: null,
      file: null,
      overlay: false,
      confirm: false,
      message_confirm: null,
      snackbar: {
        display: false,
        type: 'success',
        message: null,
      },
      case_confirm: null,
      value_confirm: null,
    }),
    computed: {
      extension () {
        return (this.file && this.file.name) ? this.file.name.split('.').pop() : '';
      },
    },
    created () {
      this.fileService = new FileService();
    },
    mounted () {
    },
    methods: {
      openConfirm (_case, value) {
        console.log('openConfirm::::', _case);
        this.value_confirm = value;
        this.case_confirm = _case;
        this.confirm = true;
        switch (_case) {
          case 1: // CAMBIAR DE ESTADO
            this.message_confirm = '¿Esta seguro de cambiar el estado?';
            break;

          case 2: // ELIMINAR COMENTARIO
            this.message_confirm = '¿Esta seguro de eliminar el comentario?';
            break;
          case 3: // ELIMINAR CAPACITACIÓN EXTERNA
            this.message_confirm = '¿Esta seguro de eliminar la capacitación externa?';
            break;
          default:
            break;
        }
      },
      uploadFile () {
        // UPDATE
        this.overlay = true;
        const data = { table: this.table, table_id: this.id };
        const formData = new FormData();
        formData.append('data', JSON.stringify(data));
        formData.append('file', this.file);

        this.fileService.create(formData).then(response => {
          console.log('Response File:::', response);
          this.overlay = false;
          this.display = false;
          this.snackbar = {
            display: true,
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
          this.overlay = false;
          this.snackbar = {
            display: true,
            type: 'error',
            message: error.response.data.message,
          };
        });
      },
    },
  };
</script>
