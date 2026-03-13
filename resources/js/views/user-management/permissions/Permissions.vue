<template>
  <Layout>
    <PageHeader :title="title" :items="[{ text: 'Dashboard', href: '/' }, { text: title, active: true }]" />
    <b-card class="mb-4">
      <b-form-group label="Search Permissions" class="mb-3">
        <b-form-input 
          v-model="searchQuery" 
          placeholder="Search by name..." 
          @input="onSearch"
        />
      </b-form-group>

      <b-table
        :items="filteredPermissions"
        :fields="fields"
        striped
        hover
        bordered
        small
        @row-clicked="openEditPermissionModal"
        :per-page="itemsPerPage"
        :current-page="currentPage"
        @update:current-page="currentPage = $event"
        :show-empty="true"
        :empty-text="'No permissions found'"
        :show-total-rows="true"
      >
      <template #cell(index)="row">
        {{ (currentPage - 1) * itemsPerPage + row.index + 1 }}
      </template>
      </b-table>

      <b-pagination
        v-model="currentPage"
        :total-rows="filteredPermissions.length"
        :per-page="itemsPerPage"
        class="mt-2 justify-content-end"
        size="sm"
      ></b-pagination>
    </b-card>
  </Layout>
</template>

<script>
import Layout from "@/layouts/main.vue";
import PageHeader from "@/components/page-header.vue";

export default {
  components: {
    Layout,
    PageHeader,
  },
  data() {
    return {
      title: "Permissions",
      permissions: [],
      searchQuery: '',
      currentPage: 1,
      itemsPerPage: 10,
      fields: [
        { key: 'index', label: '#' },
        { key: 'name', label: 'Permission Name', sortable: true },
      ],
    };
  },
  computed: {
    filteredPermissions() {
      // Filter permissions based on the search query
      return this.permissions.filter(permission =>
        permission.name.toLowerCase().includes(this.searchQuery.toLowerCase())
      );
    },
  },
  created() {
    this.fetchPermissions();
  },
  methods: {
    fetchPermissions() {
      this.$http.get('/permissions').then(response => {
        this.permissions = response.data;
      });
    },
    onSearch() {
      // Optionally reset the current page when searching
      this.currentPage = 1; // Reset to the first page on new search
    },
    openEditPermissionModal(permission) {
      // Implement your modal opening logic here
      console.log("Editing permission:", permission);
    },
  },
};
</script>

<style scoped>
.text-danger {
  margin-top: 10px;
}
</style>
