<template>
    <Layout>
        <PageHeader :title="title" :items="items" />
        <b-card class="mb-4">
            <b-card-header>
                <b-row class="align-items-center">
                    <b-col md="8">
                        <b-button @click="addUser" variant="primary" class="mb-2 custom-button" v-if="$hasPermission(['users-all', 'users-create'])">Add User</b-button>
                    </b-col>
                    <b-col md="4">
                        <b-form-input v-model="searchQuery" placeholder="Search users..." type="text"
                            class="mb-2 custom-input" @input="onSearchInput" />
                    </b-col>
                </b-row>
            </b-card-header>
            <b-card-body>
                <b-table :items="filteredUsers" :fields="tableFields" :current-page="currentPage"
                    :total-rows="totalUsers" :responsive="true">
                    <template #cell(actions)="row">
                        <div class="text-end">
                            <b-button v-if="$hasPermission(['users-all', 'users-delete'])"
                                @click="deleteUser(row.item.id)" class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-trash icon-delete"></i>
                            </b-button>
                            <b-button v-if="$hasPermission(['users-all', 'users-edit'])" @click="editUser(row.item.id)"
                                class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-edit icon-edit"></i>
                            </b-button>
                        </div>
                    </template>
                    <template #cell(index)="row">
                        {{ (currentPage - 1) * perPage + row.index + 1 }}
                    </template>
                </b-table>
                <div v-if="!filteredUsers.length && searchQuery" class="text-center">
                    <p>No users found for "{{ searchQuery }}".</p>
                </div>

                <b-pagination v-model="currentPage" :total-rows="totalUsers" :per-page="perPage"
                    class="mt-2 justify-content-end" size="sm" />
            </b-card-body>
        </b-card>
        <b-modal v-model="showDeleteModal" title="Confirm Delete" ok-title="Delete" @ok="confirmDelete">
            <p>Are you sure you want to delete this user?</p>
        </b-modal>
    </Layout>
</template>

<script>
import Layout from "@/layouts/main.vue";
import { BTable, BButton, BModal, BCard, BCardHeader, BCardBody, BFormInput, BPagination, BRow, BCol } from 'bootstrap-vue-next';
import PageHeader from "@/components/page-header.vue";
import { debounce } from 'lodash';  // Import debounce utility

export default {
    components: {
        Layout,
        BTable,
        BButton,
        BModal,
        BCard,
        BCardHeader,
        BCardBody,
        BFormInput,
        BPagination,
        BRow,
        BCol,
        PageHeader
    },
    data() {
        return {
            items: [
                { text: "Dashboard", href: "/" },
                { text: "User Management", active: true },
            ],
            title: "User Management",
            users: [],
            totalUsers: 0,
            currentPage: 1,
            perPage: 10,
            searchQuery: '',
            showDeleteModal: false,
            userToDelete: null,
        };
    },
    created() {
        this.fetchUsers();
    },
    computed: {
        filteredUsers() {
            // Filter users based on search query
            return this.users;
        },
        tableFields() {
            return [
                { key: 'index', label: '#' },
                { key: 'name', label: 'Name' },
                { key: 'email', label: 'Email' },
                { key: 'roles', label: 'Roles', formatter: this.formatRoles },
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' },
            ];
        },
    },
    watch: {
        // Watch for changes to searchQuery and reset pagination
        searchQuery(newQuery) {
            this.currentPage = 1; // Reset to the first page when searching
            this.fetchUsers(newQuery); // Fetch users based on search query
        },
    },
    methods: {
        formatRoles(roles) {
            return roles.map(role => `${role.name}`).join('; ');
        },
        fetchUsers(search = '') {
            this.$http.get(`/user/list?page=${this.currentPage}&per_page=${this.perPage}&search=${search}`)
                .then(response => {
                    this.users = response.data.data;
                    this.totalUsers = response.data.total;
                });
        },
        addUser() {
            this.$router.push({ name: 'AddUser' });
        },
        editUser(userId) {
            this.$router.push({ name: 'EditUser', params: { id: userId } });
        },
        deleteUser(userId) {
            this.userToDelete = userId;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            this.$http.delete(`/user/${this.userToDelete}`).then(() => {
                this.fetchUsers(this.searchQuery); // Refetch users after delete
                this.showDeleteModal = false;
                this.userToDelete = null;
            });
        },
        onSearchInput: debounce(function () {
            this.currentPage = 1; // Reset to the first page on new search
            this.fetchUsers(this.searchQuery); // Fetch expenses based on search query
        }, 300),
    },
};
</script>