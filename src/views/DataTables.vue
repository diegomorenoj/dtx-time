<template>
  <v-container
    id="data-tables-view"
    fluid
    tag="section"
  >
    <view-intro
      heading="Data Tables"
      link="components/data-tables"
    />

    <material-card
      icon="mdi-vuetify"
      icon-small
      color="accent"
      title="Employee Positions"
    >
      <v-card-text>
        <v-text-field
          v-model="search"
          append-icon="mdi-magnify"
          class="ml-auto"
          hide-details
          label="Search records"
          single-line
          style="max-width: 250px;"
        />

        <v-divider class="mt-3" />

        <v-data-table
          :headers="headers"
          :items="items"
          :search.sync="search"
          :sort-by="['name', 'office']"
          :sort-desc="[false, true]"
          multi-sort
        >
          <template #item.actions>
            <app-btn
              v-for="(action, i) in actions"
              :key="i"
              :color="action.color"
              class="px-2 ml-1"
              elevation="0"
              min-width="0"
              small
              text
            >
              <v-icon v-text="action.icon" />
            </app-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </material-card>
  </v-container>
</template>

<script>
  export default {
    name: 'DataTablesView',

    data: () => ({
      actions: [
        { color: 'info', icon: 'mdi-heart' },
        { color: 'success', icon: 'mdi-monitor-dashboard' },
        { color: 'error', icon: 'mdi-close' },
      ],
      headers: [
        {
          text: 'Name',
          value: 'name',
        },
        {
          text: 'Position',
          value: 'position',
        },
        {
          text: 'Office',
          value: 'office',
        },
        {
          text: 'Age',
          value: 'age',
        },
        {
          text: 'Date',
          value: 'date',
        },
        {
          sortable: false,
          text: 'Actions',
          value: 'actions',
        },
      ],
      items: [
        {
          name: 'Airi Satou',
          position: 'Accountant',
          office: 'Tokyo',
          age: 33,
          date: '2008/11/28',
        },
        {
          name: 'Angelica Ramos',
          position: 'Chief Executive Officer (CEO)',
          office: 'London',
          age: 47,
          date: '2009/10/09',
        },
        {
          name: 'Ashton Cox',
          position: 'Junior Technical Author',
          office: 'San Francisco',
          age: 66,
          date: '2009/01/12',
        },
        {
          name: 'Bradley Greer',
          position: 'Software Engineer',
          office: 'London',
          age: 41,
          date: '2012/10/13',
        },
        {
          name: 'Brenden Wagner',
          position: 'Software Engineer',
          office: 'San Francisco',
          age: 28,
          date: '2011/06/07',
        },
      ],
      search: undefined,
    }),
  }
</script>
