<template>
  <Layout>
    <PageHeader :title="title" :items="items" class="mb-3"/>

    <b-row>
      <b-col cols="12" md="8" class="mx-auto">
        <b-card>
          <b-card-header class="font-weight-bold">{{ title }}</b-card-header>
          <b-card-body>
            <b-form>
              <b-form-group label="Tax Name" :state="errors.name ? false : null">
                <b-form-input v-model="tax.name" :state="errors.name ? false : null" />
                <b-form-invalid-feedback v-if="errors.name">{{ errors.name[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Type" :state="errors.type ? false : null">
                <b-form-select v-model="tax.type" :options="typeOptions" :state="errors.type ? false : null" />
                <b-form-invalid-feedback v-if="errors.type">{{ errors.type[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Value" :state="errors.value ? false : null">
                <b-form-input type="number" step="0.01" v-model="tax.value" :state="errors.value ? false : null" />
                <b-form-invalid-feedback v-if="errors.value">{{ errors.value[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Status" :state="errors.status ? false : null">
                <b-form-checkbox v-model="tax.status" switch>
                  {{ tax.status ? 'Active' : 'Inactive' }}
                </b-form-checkbox>
                <b-form-invalid-feedback v-if="errors.status">{{ errors.status[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <div class="text-right">
                <b-button type="submit" variant="primary" @click="submitTax">
                  {{ taxId ? 'Update Tax' : 'Create Tax' }}
                </b-button>
                <b-button variant="secondary" class="ml-2" @click="cancel">Cancel</b-button>
              </div>
            </b-form>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
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
      tax: {
        name: '',
        type: 'percentage',
        value: '',
        status: true,
      },
      typeOptions: [
        { value: 'fixed', text: 'Fixed' },
        { value: 'percentage', text: 'Percentage' },
      ],
      errors: {},
    };
  },
  computed: {
    taxId() {
      return this.$route?.params?.id;
    },
    title() {
      return this.taxId ? 'Edit Tax' : 'Add Tax';
    },
    items() {
      return [
        { text: "Taxes", href: "/taxes" },
        { text: this.title, active: true },
      ];
    },
  },
  created() {
    if (this.taxId) {
      this.fetchTax();
    }
  },
  methods: {
    fetchTax() {
      this.$http.get(`/tax/${this.taxId}`)
        .then(response => {
          this.tax.name = response.data.name;
          this.tax.type = response.data.type;
          this.tax.value = response.data.value;
          this.tax.status = response.data.status === 1 ? true : false;
        })
        .catch(err => {
          console.error("Error fetching tax", err);
        });
    },
    submitTax() {
      if (this.taxId) {
        this.updateTax();
      } else {
        this.createTax();
      }
    },
    createTax() {
      this.$http.post('/tax', this.tax)
        .then(() => {
          this.$router.push({ name: 'Taxes' });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    updateTax() {
      this.$http.put(`/tax/${this.taxId}`, this.tax)
        .then(() => {
          this.$router.push({ name: 'Taxes' });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    cancel() {
      this.$router.push({ name: 'Taxes' });
    },
  }
};
</script>
