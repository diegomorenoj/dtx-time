<template>
  <div v-if="tableData.length <= 0">
    <div
      class="modal fade"
    >
      <div class="modal-success">
        <div class="modal-content">
          <div class="modal-body">
            <upload-excel-component
              :on-success="onSuccess"
              :before-upload="beforeUpload"
            />
          </div>
        </div>
      </div>
    </div>
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
  </div>
</template>
<script>
  export default {
    name: 'UploadDataView',
    components: {
      UploadExcelComponent: () => import('../app/ImporterExcel'),
    },
    props: {
      read: Function,
    },
    data: () => {
      return {
        opt: {},
        tableData: [],
        tableHeader: [],
        max: 0,
        overlay: false,
        snackbar: {
          display: false,
          title: null,
          type: 'success',
          message: null,
        },
      };
    },
    created () {
      this.tableData = [];
      this.tableHeader = [];
    },
    methods: {
      beforeUpload (file) {
        this.tableData = [];
        this.tableHeader = [];
        const isLt1M = file.size / 1024 / 1024 < 1;
        if (isLt1M) {
          return true;
        } else {
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: 'Por favor, no cargue archivos de más de 1M de tamaño.',
          };
          return false;
        }
      },
      onSuccess ({ results, header }) {
        this.tableData = results;
        this.tableHeader = header;
        this.tableData.splice(0, 1); // SE QUITA LAS DOS FILAS QUE NO TIENEN DATOS, SOLO SON ENCABEZADOS
        console.log('onSuccess => tableData:::::::::::', this.tableData);
        this.read(this.tableData);
      },
      reset () {
        this.tableData = [];
        this.tableHeader = [];
        this.errores = [];
        this.rowCount = -1;
        this.opt = { show: false };
      },
    },
  };
</script>
