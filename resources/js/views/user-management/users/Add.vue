<template>
  <Layout>
    <PageHeader :title="title" :items="items" />
    <b-card class="mb-4">
      <b-card-body>
        <b-form @submit.prevent="addUser">
          <b-form-group label="Name" :state="errors.name ? false : null">
            <b-form-input v-model="user.name" :state="errors.name ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.name">{{ errors.name[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Email" :state="errors.email ? false : null">
            <b-form-input v-model="user.email" type="email" :state="errors.email ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.email">{{ errors.email[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Password">
            <b-form-input v-model="user.password" type="password" :state="errors.password ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.password">{{ errors.password[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Confirm Password">
            <b-form-input v-model="user.password_confirmation" type="password" :state="errors.password_confirmation ? false : null"></b-form-input>
            <b-form-invalid-feedback v-if="errors.password_confirmation">{{ errors.password_confirmation[0] }}</b-form-invalid-feedback>
          </b-form-group>

          <b-form-group label="Role">
            <b-form-select
              v-model="selectedRoleId"
              :options="availableRoles.map(role => ({ value: role.id, text: role.name }))"
              placeholder="Select a role"
              :state="errors.role_id ? false : null"
            ></b-form-select>
            <b-form-invalid-feedback v-if="errors.role_id">{{ errors.role_id[0] }}</b-form-invalid-feedback>
          </b-form-group>
          <div class="button-group">
              <b-button type="submit" variant="primary">Create</b-button>
              <b-button @click="cancelEdit" variant="secondary" style="margin-left: 5px;">Cancel</b-button>
          </div>
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
      title: "Add User",
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
        { text: "Add User", active: true },
      ],
    };
  },
  created() {
    this.fetchRoles();
  },
  methods: {
    fetchRoles() {
      this.$http.get("/roles").then(response => {
        this.availableRoles = response.data;
      });
    },
    addUser() {
      this.$http.post(`/user`, {
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
    cancelEdit() {
      this.$router.push('/users'); // Cancel and go back to roles list
    },
  },
};
</script>
