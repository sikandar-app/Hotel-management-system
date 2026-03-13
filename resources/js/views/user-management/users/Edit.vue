<template>
    <Layout>
      <PageHeader :title="title" :items="items" />
      <b-card class="mb-4">
        <b-card-body>
          <b-form @submit.prevent="updateUser">
            <b-form-group label="Name" :state="errors.name ? false : null">
              <b-form-input v-model="user.name" :state="errors.name ? false : null" ></b-form-input>
              <b-form-invalid-feedback v-if="errors.name">{{ errors.name[0] }}</b-form-invalid-feedback>
            </b-form-group>
      
            <b-form-group label="Email" :state="errors.email ? false : null">
              <b-form-input v-model="user.email" :state="errors.email ? false : null" type="email"></b-form-input>
              <b-form-invalid-feedback v-if="errors.email">{{ errors.email[0] }}</b-form-invalid-feedback>
            </b-form-group>
      
            <b-form-group label="Password" description="Leave empty if you don't want to change"  :state="errors.password ? false : null">
              <b-form-input v-model="user.password" :state="errors.password ? false : null" type="password"></b-form-input>
              <b-form-invalid-feedback v-if="errors.password">{{ errors.password[0] }}</b-form-invalid-feedback>
            </b-form-group>

            <b-form-group label="Confirm Password">
              <b-form-input v-model="user.password_confirmation" type="password" :state="errors.password_confirmation ? false : null"></b-form-input>
              <b-form-invalid-feedback v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</b-form-invalid-feedback>
            </b-form-group>
      
            <b-form-group 
              label="Role"
              :state="errors.role_id ? false : null"
            >
              <b-form-select
                v-model="selectedRoleId"
                :options="availableRoles.map(role => ({ value: role.id, text: role.name }))"
                required
                placeholder="Select a role"
                :state="errors.role_id ? false : null"
              ></b-form-select>
              <b-form-invalid-feedback v-if="errors.role_id">{{ errors.role_id[0] }}</b-form-invalid-feedback>
            </b-form-group>
      
            <b-button type="submit" variant="primary">Update</b-button>
          </b-form>
        </b-card-body>
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
        title: "Edit User",
        user: {
          name: '',
          email: '',
          password: null, // Leave null if no change
          password_confirmation: null
        },
        selectedRoleId: null,
        availableRoles: [],
        errors: {}, // To store validation errors
        items: [
          { text: "Users", href: "/users" },
          { text: "Edit User", active: true },
        ],
      };
    },
    created() {
      this.fetchUser();
      this.fetchRoles();
    },
    methods: {
      fetchUser() {
        const userId = this.$route.params.id;
        this.$http.get(`/user/${userId}`).then(response => {
          this.user = response.data;
          this.selectedRoleId = this.user.roles.length > 0 ? this.user.roles[0].id : null;
        });
      },
      fetchRoles() {
        this.$http.get("/roles").then(response => {
          this.availableRoles = response.data;
        });
      },
      updateUser() {
        const userId = this.$route.params.id;
        this.$http.put(`/user/${userId}`, {
          name: this.user.name,
          email: this.user.email,
          password: this.user.password, // Send password only if changed
          password_confirmation: this.user.password_confirmation, // Send password only if changed
          role_id: this.selectedRoleId,
        })
        .then(() => {
          this.$router.push({ name: 'Users' });
        })
        .catch(error => {
          // Handle errors if needed
          if (error.response && error.response.data) {
            this.errors = error.response.data.errors; // Capture the validation errors
          }
        });
      },
    },
  };
  </script>
  