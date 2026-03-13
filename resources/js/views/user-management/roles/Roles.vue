<template>
  <Layout>
    <PageHeader :title="title" :items="[{ text: 'Dashboard', href: '/' }, { text: 'Roles', active: true }]" />
    <b-card class="mb-4">
      <b-card-header>
        <b-row class="align-items-center">
          <b-col md="8">
            <b-button v-if="$hasPermission(['roles-all', 'roles-create'])" @click="navigateToAddRoleAndPermissions" class="mb-2 custom-button" variant="primary">Add Role</b-button>
          </b-col>
          <b-col md="4" class="text-right">
            <b-form-input
              v-model="searchQuery"
              placeholder="Search roles..."
              class="mb-2 custom-input"
            />
          </b-col>
        </b-row>
      </b-card-header>
      <b-card-body>
        <b-table
          :items="filteredRoles"
          :fields="[ 
            { key: 'index', label: '#' },
            { key: 'name', label: 'Role Name' }, 
            { key: 'permissions', label: 'Permissions', formatter: formatPermissions }, 
            { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' } 
          ]"
          :current-page="currentPage"
          :total-rows="totalRoles"
        >
          <template #cell(actions)="row">
            <div class="text-end">
              <b-button v-if="$hasPermission(['roles-all', 'roles-delete'])" @click="deleteRole(row.item)" class="transparent-button border-0 bg-transparent">
                <i class="fas fa-trash icon-delete"></i>
              </b-button>
              <b-button v-if="$hasPermission(['roles-all', 'roles-edit'])" @click="navigateToEditPermissions(row.item)" class="transparent-button border-0 bg-transparent">
                <i class="fas fa-edit icon-edit"></i>
              </b-button>
            </div>
          </template>
          <template #cell(index)="row">
            {{ (currentPage - 1) * perPage + row.index + 1 }}
          </template>
        </b-table>
        <!-- Pagination -->
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRoles"
          :per-page="perPage"
          class="mt-2 justify-content-end"
          size="sm"
        />
      </b-card-body>
    </b-card>

    <!-- Delete Role Modal -->
    <b-modal
      v-model="showDeleteModal"
      title="Confirm Delete"
      ok-title="Delete"
      @ok="confirmDelete"
    >
      <p>Are you sure you want to delete the role: {{ roleToDelete ? roleToDelete.name : '' }}?</p>
    </b-modal>
  </Layout>
</template>

<script>
import Layout from "@/layouts/main.vue";
import PageHeader from "@/components/page-header.vue";
import { BButton, BTable, BModal, BFormInput, BPagination } from 'bootstrap-vue-next';

export default {
  components: {
    Layout,
    PageHeader,
    BButton,
    BTable,
    BModal,
    BFormInput,
    BPagination,
  },
  data() {
    return {
      title: "Roles",
      roles: [],
      showDeleteModal: false,
      roleToDelete: null,
      errorMessage: null,
      searchQuery: '',
      currentPage: 1,
      perPage: 10,
    };
  },
  created() {
    this.fetchRoles();
  },
  computed: {
    filteredRoles() {
      // Filter roles based on search query
      const query = this.searchQuery.toLowerCase();
      return this.roles.filter(role => 
        role.name.toLowerCase().includes(query)
      );
    },
    totalRoles() {
      return this.filteredRoles.length; // Total rows for pagination
    }
  },
  methods: {
    formatPermissions(permissions) {
      return permissions.map(permission => permission.name).join(', ');
    },
    fetchRoles() {
      this.$http.get('/roles').then(response => {
        this.roles = response.data;
      });
    },
    deleteRole(role) {
      this.roleToDelete = role;
      this.showDeleteModal = true;
    },
    confirmDelete() {
      this.$http.delete(`/roles/${this.roleToDelete.id}`).then(() => {
        this.fetchRoles();
        this.showDeleteModal = false;
        this.roleToDelete = null;
      }).catch(error => {
        this.errorMessage = error.response.data.message;
      });
    },
    navigateToEditPermissions(role) {
      this.$router.push({ name: 'EditRole', params: { id: role.id } });
    },
    navigateToAddRoleAndPermissions() {
      this.$router.push({ name: 'AddRole' });
    },
  },
};
</script>
