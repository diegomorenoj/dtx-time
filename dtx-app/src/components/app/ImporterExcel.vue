<template>
  <div>
    <input
      v-if="excelData.results ? excelData.results.length > 0 ? false : true : true"
      ref="excel-upload-input"
      class="excel-upload-input"
      type="file"
      accept=".xlsx, .xls"
      :disabled="loading"
      @change="handleClick"
    >
    <div
      v-if="excelData.results ? excelData.results.length > 0 ? false : true : true"
      class="drop"
      @drop="handleDrop"
      @dragover="handleDragover"
      @dragenter="handleDragover"
    >
      Arrastrar y soltar o
      <app-btn
        :loading="loading"
        style="margin-left:16px;"
        size="mini"
        variant="success"
        :disabled="loading"
        small
        @click="handleUpload"
      >
        Buscar...
      </app-btn>
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
  </div>
</template>

<script>
  import * as XLSX from 'xlsx';
  export default {
    name: 'ImporterExcelView',
    props: {
      beforeUpload: Function, // eslint-disable-line
      onSuccess: Function, // eslint-disable-line
    },
    data () {
      return {
        loading: false,
        excelData: {
          header: null,
          results: null,
        },
        overlay: false,
      };
    },
    methods: {
      generateData ({ header, results }) {
        this.excelData.header = header;
        this.excelData.results = results;
        this.onSuccess && this.onSuccess(this.excelData);
      },
      handleDrop (e) {
        e.stopPropagation();
        e.preventDefault();
        if (this.loading) return;
        const files = e.dataTransfer.files;
        if (files.length !== 1) {
          this.$message.error('Only support uploading one file!');
          return;
        }
        const rawFile = files[0];
        if (!this.isExcel(rawFile)) {
          this.$message.error('Only supports upload .xlsx, .xls, .csv suffix files');
          return false;
        }
        this.upload(rawFile);
        e.stopPropagation();
        e.preventDefault();
      },
      handleDragover (e) {
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
      },
      handleUpload () {
        this.$refs['excel-upload-input'].click();
      },
      handleClick (e) {
        const files = e.target.files;
        const rawFile = files[0];
        if (!rawFile) return;
        this.upload(rawFile);
      },
      upload (rawFile) {
        this.$refs['excel-upload-input'].value = null;
        if (!this.beforeUpload) {
          this.readerData(rawFile);
          return;
        }
        const before = this.beforeUpload(rawFile);
        if (before) {
          this.readerData(rawFile);
        }
      },
      readerData (rawFile) {
        this.overlay = true;
        this.loading = true;
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.onload = e => {
            const data = e.target.result;
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const header = this.getHeaderRow(worksheet);
            const results = XLSX.utils.sheet_to_json(worksheet);
            this.generateData({ header, results });
            this.loading = false;
            resolve();
          };
          reader.readAsArrayBuffer(rawFile);
          this.overlay = false;
        });
      },
      getHeaderRow (sheet) {
        const headers = [];
        const range = XLSX.utils.decode_range(sheet['!ref']);
        let C;
        const R = range.s.r;
        for (C = range.s.c; C <= range.e.c; ++C) { /* walk every column in the range */
          const cell = sheet[XLSX.utils.encode_cell({ c: C, r: R })];
          /* find the cell in the first row */
          let hdr = 'UNKNOWN ' + C; // <-- replace with your desired default
          if (cell && cell.t) hdr = XLSX.utils.format_cell(cell);
          headers.push(hdr);
        }
        return headers;
      },
      isExcel (file) {
        return /\.(xlsx|xls|csv)$/.test(file.name);
      },
    },
  };
</script>
