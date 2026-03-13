<template>
    <Layout>
        <PageHeader :title="title" :items="[{ text: 'Roles', href: '/roles' }, { text: title, active: true }]" />
        <b-card class="mb-4">
            <b-form @submit.prevent="saveRole">
                <b-form-group label="Role Name" :state="errors.name ? false : null">
                    <b-form-input v-model="role.name" placeholder="Role Name"
                        :state="errors.name ? false : null"></b-form-input>
                    <b-form-invalid-feedback v-if="errors.name">{{ errors.name[0] }}</b-form-invalid-feedback>
                </b-form-group>

                <b-form-group label="Permissions">
                    <!-- Search bar for filtering permissions -->
                    <b-input-group class="mb-3">
                        <b-form-input v-model="searchQuery" placeholder="Search permissions..."
                            aria-label="Search Permissions" />
                        <b-input-group-append>
                            <b-button variant="outline-secondary" @click="clearSearch">Clear</b-button>
                        </b-input-group-append>
                    </b-input-group>

                    <!-- Button Group for Permissions -->
                    <div class="permission-buttons">
                        <!-- Render Checkboxes with v-model binding -->
                        <input v-for="permission in filteredPermissions" :key="permission.value" type="checkbox"
                            class="btn-check" :id="'permission-' + permission.value" v-model="selectedPermissions"
                            :value="permission.value" autocomplete="off" />
                        <label v-for="permission in filteredPermissions" :key="'label-' + permission.value"
                            :for="'permission-' + permission.value" class="btn btn-outline-secondary permission-btn"
                            :class="selectedPermissions.includes(permission.value) ? 'active' : ''">
                            {{ permission.text }}
                        </label>
                    </div>
                </b-form-group>

                <div class="button-group">
                    <b-button type="submit" variant="primary">{{ isEditMode ? 'Update' : 'Submit' }}</b-button>
                    <b-button @click="cancelEdit" variant="secondary" style="margin-left: 5px;">Cancel</b-button>
                </div>
                <p v-if="errorMessage" class="text-danger">{{ errorMessage }}</p>
            </b-form>
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
            searchQuery: '', // Search query for filtering permissions
            role: {
                id: null,
                name: '',
                permissions: [], // Permissions assigned to the role
            },
            selectedPermissions: [], // All permissions selected for the role
            permissionOptions: [], // All permissions available
            errorMessage: null,
            errors: {},
            isEditMode: false,
        };
    },
    created() {
        const roleId = this.$route.params.id; // Get role ID from the URL
        this.isEditMode = !!roleId;
        if (roleId) this.fetchRole(roleId);
        this.fetchPermissions();
    },
    computed: {
        title() {
            return this.isEditMode ? "Edit Role" : "Add Role";
        },
        filteredPermissions() {
            if (!this.searchQuery) {
                return this.permissionOptions;
            }
            return this.permissionOptions.filter(permission =>
                permission.text.toLowerCase().includes(this.searchQuery.toLowerCase())
            );
        },
    },
    methods: {
        clearSearch() {
            this.searchQuery = '';
        },
        fetchRole(roleId) {
            // Fetch the role data for editing
            this.$http.get(`/roles/${roleId}`).then(response => {
                const roleData = response.data;
                this.role.id = roleData.id;
                this.role.name = roleData.name;

                // Initialize selectedPermissions based on fetched role data
                this.selectedPermissions = roleData.permissions.map(permission => permission.id);
            }).catch(error => {
                this.errorMessage = this.handleError(error);
            });
        },
        fetchPermissions() {
            // Fetch available permissions
            this.$http.get('/permissions').then(response => {
                this.permissionOptions = response.data.map(permission => ({
                    value: permission.id,
                    text: permission.name,
                }));
            }).catch(error => {
                this.errorMessage = this.handleError(error);
            });
        },
        saveRole() {
            // Submit updated role data
            if (this.role.id) {
                this.updateRole();
            } else {
                this.storeRole();
            }
        },
        storeRole() {
            // Submit new role data
            this.role.permissions = this.selectedPermissions;
            this.$http.post('/roles', this.role)
                .then(() => {
                    this.$router.push('/roles'); // Navigate back to roles list
                })
                .catch(error => {
                    if (error.response && error.response.data) {
                        this.errors = error.response.data.errors; // Capture the validation errors
                    }
                });
        },
        updateRole() {
            // Submit updated role data
            this.role.permissions = this.selectedPermissions;
            this.$http.put(`/roles/${this.role.id}`, this.role)
                .then(() => {
                    this.$router.push('/roles'); // Navigate back to roles list
                })
                .catch(error => {
                    if (error.response && error.response.data) {
                        this.errors = error.response.data.errors; // Capture the validation errors
                    }
                });
        },
        cancelEdit() {
            this.$router.push('/roles'); // Cancel and go back to roles list
        },
        handleError(error) {
            // Generic error handling function
            if (error.response && error.response.data && error.response.data.message) {
                return error.response.data.message; // API error message
            }
            return 'An unexpected error occurred. Please try again.'; // Fallback message
        },
    },
};
</script>
<style scoped>
/* Grid layout for permission buttons */
.permission-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    /* Auto-adjusts based on screen width */
    gap: 10px;
    /* Space between buttons */
}

/* Styling for the permission buttons */
.permission-btn {
    cursor: pointer;
    transition: background-color 0.3s ease, border-color 0.3s ease;
    text-align: center;
    padding: 8px 2px;
    border-radius: 5px;
    /* Rounded buttons */
    font-size: 10px;
    color: #495057;
    border: 1px solid #ddd;
}

.permission-btn:hover {
    background-color: #f8f9fa;
}

.permission-btn.active {
    background-color: #00695c;
    /* Active button color */
    color: white;
    border-color: #00695c;
}

.permission-btn:focus {
    box-shadow: none;
    border-color: #00695c;
}
</style>