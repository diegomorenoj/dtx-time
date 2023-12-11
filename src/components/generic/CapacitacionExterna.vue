<template>
  <v-container
    id="capacitacion-view"
    fluid
    tag="section"
  >
    <v-row
      class="pt-2 pb-0 mb-0"
    >
      <v-col
        cols="8"
        class="ml-auto mr-auto"
      >
        <v-btn
          class="ml-2 float-right"
          min-width="0"
          icon
          :disabled="item.file == null"
          @click="dowloadFile(item.file)"
        >
          <v-icon>mdi-paperclip</v-icon>
        </v-btn>
        <v-select
          v-model="item.status_id"
          :items="lstStatus"
          class="float-right"
          item-text="name"
          item-value="id"
          @change="openConfirm(1, null)"
        />
      </v-col>
    </v-row>
    <v-row class="pt-0 mt-0">
      <v-col
        cols="8"
        class="ml-auto mr-auto"
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
                Solicitud de capacitación externa
              </div>
            </div>
          </template>
          <v-card-text>
            <v-form>
              <!-- INICIO DATOS USUARIO -->
              <v-row>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.user_name"
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
                    label="Cargo"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <v-row>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.area"
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
                    label="Ciudad"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <!-- FIN DATOS USUARIO -->
              <!-- INICIO SOLICITUD GRUPAL -->
              <v-row>
                <v-col
                  cols="12"
                >
                  <span class="text-h4">USUARIOS ASOCIADOS</span>
                </v-col>
              </v-row>
              <v-row
                v-for="(usr, i) in item.users"
                :key="i"
                align="center"
              >
                <v-col
                  cols="6"
                >
                  <v-text-field
                    v-model="usr.lastname"
                    class="mb-n3"
                    label="Nombre y Apellido"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="3"
                >
                  <v-text-field
                    v-model="usr.area"
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
                    v-model="usr.position"
                    class="mb-n3"
                    label="Cargo"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <!-- FIN SOLICITUD GRUPAL -->
              <v-row>
                <v-col
                  cols="12"
                >
                  <v-text-field
                    v-model="item.shortname"
                    label="Nombre de la capacitación"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <v-row>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.type_name"
                    label="Tipo"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.fee"
                    label="Valor solicitado $"
                    type="number"
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.methodology_name"
                    label="Metodología"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <!-- FECHAS -->
              <v-row>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.start_date"
                    label="Fecha de inicio"
                    prepend-icon="mdi-calendar"
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.end_date"
                    label="Fecha de fin"
                    prepend-icon="mdi-calendar"
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.hours"
                    label="Total horas"
                    type="number"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <!-- FIN FECHAS -->
              <v-row>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.permission_name"
                    label="¿Requiere permiso de horario?"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.schedule"
                    label="Describa el horario del permiso"
                    type="text"
                    prepend-icon="mdi-clock"
                    hint="Requiero permiso todos los lunes de 9:00 am a 10:00 am."
                    :disabled="true"
                  />
                </v-col>
                <v-col
                  cols="4"
                >
                  <v-text-field
                    v-model="item.institute"
                    label="Entidad / Proveedor"
                    type="text"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
              <v-row>
                <v-col
                  cols="12"
                >
                  <v-textarea
                    v-model="item.comments"
                    label="Comentario"
                    height="80"
                    :disabled="true"
                  />
                </v-col>
              </v-row>
            </v-form>
          </v-card-text>
        </material-card>
      </v-col>
    </v-row>
    <v-row>
      <v-col
        cols="8"
        class="ml-auto mr-auto"
      >
        <v-row class="pt-0 mt-0">
          <v-col
            cols="7"
          >
            <v-row class="pt-0 mt-0">
              <v-col
                cols="12"
              >
                <material-card
                  icon="mdi-comment-text-outline"
                  icon-small
                  title="Comentarios"
                  color="warning"
                >
                  <v-card-text>
                    <v-row class="pt-0 mt-0">
                      <v-col
                        cols="12"
                      >
                        <app-btn
                          color="success"
                          class="px-2 ml-1 float-right"
                          elevation="0"
                          min-width="0"
                          small
                          :disabled="!comment.comments"
                          @click="createComment()"
                        >
                          <v-icon
                            small
                            v-text="'mdi-content-save-check'"
                          />
                        </app-btn>
                        <v-file-input
                          v-model="fileComment"
                          hide-input
                          truncate-length="50"
                          class="float-right v-btn pt-0 mt-0 v-file-input-btn px-1 ml-1"
                          :class="fileComment ? 'success' : 'primary'"
                        />

                        <v-text-field
                          v-model="comment.comments"
                          label="Ingrese su comentario"
                          class="col-12 pb-0"
                          type="text"
                        />
                      </v-col>
                    </v-row>

                    <v-row
                      v-for="(com, i) in item.lstTrainingRequestsComments"
                      :key="i"
                      class="pt-0 mt-0"
                    >
                      <v-col
                        cols="12"
                      >
                        <p class="mx-2 mb-2 text-justify">
                          {{ com.comments }}
                        </p>
                        <v-divider class="mx-2 mb-2" />
                        <app-btn
                          color="error"
                          class="px-2 ml-1 float-right"
                          elevation="0"
                          min-width="0"
                          small
                          @click="openConfirm(2, com.id)"
                        >
                          <v-icon
                            small
                            v-text="'mdi-delete'"
                          />
                        </app-btn>
                        <app-btn
                          color="primary"
                          class="px-2 ml-1 float-right"
                          elevation="0"
                          min-width="0"
                          small
                          :disabled="com.file == null"
                          @click="dowloadFile(com.file)"
                        >
                          <v-icon
                            small
                            v-text="'mdi-paperclip'"
                          />
                        </app-btn>
                        <app-btn
                          color="info"
                          class="px-2 ml-1 float-right"
                          elevation="0"
                          min-width="0"
                          small
                          @click="opendDialogComment(com)"
                        >
                          <v-icon
                            small
                            v-text="'mdi-pencil'"
                          />
                        </app-btn>
                        <p class="ml-2 mb-2">
                          {{ com.lastname }} | {{ com.date }}
                        </p>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </material-card>
              </v-col>
              <v-col
                cols="12"
              >
                <material-card
                  icon="mdi-comment-text-outline"
                  icon-small
                  title="Historial"
                  color="info"
                >
                  <v-card-text>
                    <v-row
                      v-for="(log, i) in item.lstTrainingRequestsLogs"
                      :key="i"
                      class="pt-0 mt-0"
                    >
                      <v-col
                        cols="12"
                      >
                        <p class="mx-3 mb-2 text-justify">
                          {{ log.comments }} {{ log.before_status_name ? ' de ' + log.before_status_name + ' a ' : null }} {{ log.after_status_name }} ({{ log.lastname }})
                        </p>
                        <v-divider class="mx-3 mb-2" />
                        <p class="mx-3 mb-2">
                          <v-icon
                            small
                            v-text="'mdi-clock-outline'"
                          />
                          {{ log.date }}
                        </p>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </material-card>
              </v-col>
            </v-row>
          </v-col>
          <v-col
            cols="5"
          >
            <v-row class="pt-0 mt-0">
              <v-col
                cols="12"
              >
                <material-card
                  icon="mdi-star"
                  icon-small
                  title="Constancia"
                  color="accent"
                >
                  <v-card-text>
                    <vue-dropzone
                      id="dropzone"
                      ref="dropzone"
                      class="vdropzone"
                      :options="dropOptions"
                      @vdropzone-complete="afterComplete"
                      @vdropzone-removed-file="removeThisFile"
                    />
                    <v-row
                      v-if="item.constancy"
                      class="pt-0 mt-0"
                    >
                      <v-col
                        cols="12"
                      >
                        <a
                          href="#"
                          class="mx-3 mb-2 text-justify text-decoration-none"
                          @click="dowloadFile(item.constancy)"
                        >
                          Descargar constancia
                        </a>
                        <app-btn
                          color="error"
                          class="px-2 ml-1 float-right"
                          elevation="0"
                          min-width="0"
                          small
                          @click="openConfirm(4, item.constancy.id)"
                        >
                          <v-icon
                            small
                            v-text="'mdi-delete'"
                          />
                        </app-btn>
                        <v-divider class="mt-3 mb-2" />
                        <p class="mx-3 mb-2">
                          <v-icon
                            small
                            v-text="'mdi-clock-outline'"
                          />
                          {{ item.constancy.date }}
                        </p>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </material-card>
              </v-col>
              <v-col
                cols="12"
              >
                <material-card
                  icon="mdi-reload"
                  icon-small
                  title="Solicitudes anteriores"
                  color="error"
                >
                  <v-card-text>
                    <v-row
                      v-for="(tra, i) in item.lstLastTrainings"
                      :key="i"
                      class="pt-0 mt-0"
                    >
                      <v-col
                        cols="12"
                      >
                        <p class="mx-3 mb-2 text-justify">
                          {{ tra.shortname }} - {{ tra.status_name }}
                        </p>
                        <v-divider class="mx-3 mb-2" />
                        <p class="mx-3 mb-2">
                          <v-icon
                            small
                            v-text="'mdi-clock-outline'"
                          />
                          {{ tra.date }}
                        </p>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </material-card>
              </v-col>
            </v-row>
          </v-col>
        </v-row>
      </v-col>
    </v-row>
    <!-- DIALOGO PARA EDITAR LA CAPACITACIÓN -->
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
              Solicitud de capacitación externa
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

            <v-row
              v-for="(usr, i) in item.users"
              :key="i"
              align="center"
            >
              <v-col
                cols="5"
              >
                <v-text-field
                  v-model="usr.lastname"
                  class="mb-n3"
                  label="Nombre y Apellido"
                  type="text"
                  :disabled="true"
                />
              </v-col>
              <v-col
                cols="3"
              >
                <v-text-field
                  v-model="usr.area"
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
                  v-model="usr.position"
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
                  color="error"
                  @click="removeUser(i)"
                >
                  <v-icon>mdi-minus-circle</v-icon>
                </v-btn>
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
              @click="updateData()"
            >
              Guardar
            </v-btn>
          </div>
        </v-card-text>
      </material-card>
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
            {{ message_confirm }}
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
            @click="okConfirm()"
          >
            Si
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- DIALOGO PARA EDITAR UN COMENTARIO -->
    <v-dialog
      v-model="displayComment"
      persistent
      max-width="450"
    >
      <v-card>
        <v-card-title class="text-h4">
          Comentario
        </v-card-title>
        <v-card-text>
          <v-row class="pt-0 mt-0">
            <v-col
              cols="12"
            >
              <v-file-input
                v-model="fileComment"
                hide-input
                truncate-length="50"
                class="float-right v-btn pt-0 mt-0 v-file-input-btn px-1 ml-1"
                :class="fileComment ? 'success' : 'primary'"
              />

              <v-text-field
                v-model="comment.comments"
                label="Ingrese su comentario"
                class="col-12 pb-0"
                type="text"
              />
            </v-col>
          </v-row>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn
            color="warning"
            small
            class="col-3"
            @click="displayComment = false"
          >
            Cancelar
          </v-btn>
          <v-btn
            color="success"
            class="col-3"
            small
            @click="updateComment()"
          >
            Guardar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <!-- COMPONENTE PARA CARGAR ARCHIVOS -->
    <default-file-upload
      v-if="displayUpload"
      :id="table_id"
      :table="table"
      :display="displayUpload"
    />
    <!-- LOADER -->
    <v-overlay
      class="v-overlay-custom"
      :value="overlay"
    >
      <v-progress-circular
        indeterminate
        size="64"
      />
    </v-overlay>
    <!-- MENSAGES O ALERTS -->
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
  </v-container>
</template>

<script>
  import TrainingRequestService from '../../services/TrainingRequestService';
  import TrainingRequestCommentService from '../../services/TrainingRequestCommentService';
  import ParameterService from '../../services/ParameterService';
  import ConstancyService from '../../services/ConstancyService';
  import UserService from '../../services/UserService';
  import vueDropzone from 'vue2-dropzone';
  import 'vue2-dropzone/dist/vue2Dropzone.min.css';
  import { getErrorMessage } from '@/util/helpers';
  import { get } from 'vuex-pathify';
  export default {
    name: 'DefaultCapacitacionExterna',
    components: {
      DefaultFileUpload: () => import('./FileUpload'), /* webpackChunkName: "default-drawer-toggle" */
      vueDropzone,
    },
    props: {
      id: {
        type: Number,
        default: null,
      },
    },
    data: () => ({
      trainingRequestService: null,
      parameterService: null,
      trainingRequestCommentService: null,
      constancyService: null,
      lstStatus: [],
      item: {
        user_id: null,
        user_name: null,
        lastname: null,
        position: null,
        status_id: null,
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
      },
      file: null,
      fileComment: null,
      fileConstancy: null,
      displayComment: false,
      displayDialog: false,
      display_start_date: false,
      display_end_date: false,
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
      comment: { id: null, training_request_id: null, comments: null },
      constancy: { id: null, training_request_id: null, comments: null },
      overlay: false,
      confirm: false,
      message_confirm: null,
      snackbar: {
        title: null,
        display: false,
        type: 'success',
        message: null,
      },
      case_confirm: null,
      value_confirm: null,
      displayUpload: false,
      table: null,
      table_id: null,
      dropOptions: {
        uploadMultiple: false,
        paramName: 'file',
        acceptedFiles: 'image/*, application/pdf, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        maxFilesize: 5, // MB
        thumbnailWidth: 100,
        thumbnailHeight: 50,
        dictDefaultMessage: 'Agregar adjunto',
      },
      // para el filtro de usuario
      userService: null,
      userSelected: {},
      lstUsers: [],
      isLoading: false,
      searchUsers: null,
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
      this.trainingRequestService = new TrainingRequestService();
      this.parameterService = new ParameterService();
      this.trainingRequestCommentService = new TrainingRequestCommentService();
      this.constancyService = new ConstancyService();
      this.userService = new UserService();
      this.dropOptions.url = `${process.env.VUE_APP_RUTA_API}/constancies?token=${localStorage.getItem('session@token')}&training_request_id=${this.id}`;
    },
    mounted () {
      this.loadParameters();
      this.loadData();
    },
    methods: {
      loadParameters () {
        this.overlay = true;
        this.parameterService.getStatus(this.id).then(response => {
          this.lstStatus = response.data;
          console.log(this.lstStatus);
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
      loadData () {
        this.overlay = true;
        this.trainingRequestService.getAllById(this.id).then(response => {
          console.log('TrainingRequest:::', response);
          this.item = response.data;
          this.overlay = false;
        }).catch((error) => {
          console.log(error);
          this.overlay = false;
        });
      },
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
          case 4: // ELIMINAR CONSTANCIA
            this.message_confirm = '¿Esta seguro de eliminar la constancia?';
            break;
          default:
            break;
        }
      },
      okConfirm () {
        switch (this.case_confirm) {
          case 1: // CAMBIAR DE ESTADO
            this.changeStatus();
            break;
          case 2: // ELIMINAR COMENTARIO
            this.deleteComment(this.value_confirm);
            break;
          case 3: // ELIMINAR
            this.deleteTrainingRequest();
            break;
          case 4: // ELIMINAR CONSTANCIA
            this.deleteConstancy(this.value_confirm);
            break;
          default:
            break;
        }
      },
      changeStatus () {
        // UPDATE
        this.overlay = true;
        this.confirm = false;
        const id = this.item.id;
        this.trainingRequestService.changeStatus(id, this.item.status_id).then(response => {
          this.overlay = false;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
          this.overlay = false;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
        });
      },
      deleteTrainingRequest () {
        // DELETE
        this.overlay = true;
        this.confirm = false;
        this.trainingRequestService.delete(this.item.id).then(response => {
          this.overlay = false;
          this.item = {};
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
          setTimeout(() => {
            location.reload();
          }, 500);
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
          this.overlay = false;
          this.confirm = false;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
        });
      },
      createComment () {
        // UPDATE
        this.overlay = true;
        this.comment.training_request_id = this.item.id;
        const formData = new FormData();
        formData.append('data', JSON.stringify(this.comment));
        formData.append('file', this.fileComment);
        this.trainingRequestCommentService.create(formData).then(response => {
          this.overlay = false;
          this.fileComment = null;
          this.comment.comments = null;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
          this.overlay = false;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
        });
      },
      updateComment () {
        // UPDATE
        this.overlay = true;
        const formData = new FormData();
        formData.append('data', JSON.stringify(this.comment));
        formData.append('file', this.fileComment);
        formData.append('_method', 'PUT');
        this.trainingRequestCommentService.update(formData, this.comment.id).then(response => {
          this.overlay = false;
          this.fileComment = null;
          this.displayComment = false;
          this.comment.comments = null;
          this.comment = {};
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
          this.overlay = false;
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
        });
      },
      deleteComment (id) {
        // UPDATE
        this.overlay = true;
        this.trainingRequestCommentService.delete(id).then(response => {
          this.overlay = false;
          this.confirm = false;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
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
      updateData () {
        // ACTUALIZAR SOLICTUD

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
          this.snackbar = {
            display: true,
            title: 'ERROR: ',
            type: 'error',
            message: getErrorMessage(error.response.data.message),
          };
        });
      },
      closeDialog () {
        this.displayDialog = false;
      },
      openFileUpload (table, tableId) {
        this.table = table;
        this.table_id = tableId;
        setTimeout(() => {
          this.displayUpload = true;
        }, 10);
      },
      opendDialogComment (item) {
        this.comment = Object.assign({}, item);
        this.displayComment = true;
      },
      afterComplete (file) {
        if (file.accepted) {
          let conv = {};
          const res = file.xhr.response;
          conv = JSON.parse(res);
          console.log('conv::::::::::', conv.data);
          const f = {
            file: {
              id: conv.data.id,
              // table: conv.data.table,
              training_request_id: conv.data.training_request_id,
              path: conv.data.path,
              name: conv.data.name,
              type: conv.data.type,
            },
          };
          // console.log(f);
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: 'Constancia guardada correctamente.',
          };
          this.loadData();
          this.$emit('newfile', f);
          this.$refs.dropzone.removeAllFiles();
        }
      },
      removeThisFile (file) {
        console.log(file);
      },
      dowloadFile (_file) {
        window.open(`${process.env.VUE_APP_RUTA_API}/file/${_file.name}?token=${localStorage.getItem('session@token')}`);
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
      deleteConstancy (id) {
        // UPDATE
        this.overlay = true;
        this.constancyService.delete(id).then(response => {
          this.overlay = false;
          this.confirm = false;
          this.loadData();
          this.snackbar = {
            display: true,
            title: 'INFO: ',
            type: 'success',
            message: response.message,
          };
        }).catch((error) => {
          console.log('Service error leo :::', error.response.data);
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
    },
  };
</script>
