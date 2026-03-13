<template>
  <Layout>
    <PageHeader :title="title" :items="items" class="mb-3"/>

    <b-row>
      <b-col cols="12" md="8" class="mx-auto">
        <b-card>
          <b-card-header class="font-weight-bold">{{ title }}</b-card-header>
          <b-card-body>
            <b-form>
              <b-form-group label="Amount" :state="errors.amount ? false : null">
                <b-form-input type="number" v-model="expense.amount" :state="errors.amount ? false : null" />
                <b-form-invalid-feedback v-if="errors.amount">{{ errors.amount[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Expense Category" :state="errors.expense_category_id ? false : null">
                <b-form-select v-model="expense.expense_category_id" :options="categoryOptions" :state="errors.expense_category_id ? false : null" />
                <b-form-invalid-feedback v-if="errors.expense_category_id">{{ errors.expense_category_id[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Expense Date" :state="errors.expense_date ? false : null">
                <b-form-input type="date" v-model="expense.expense_date" :state="errors.expense_date ? false : null" />
                <b-form-invalid-feedback v-if="errors.expense_date">{{ errors.expense_date[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Status" :state="errors.status ? false : null">
                <b-form-select v-model="expense.status" :options="statusOptions" :state="errors.status ? false : null" />
                <b-form-invalid-feedback v-if="errors.status">{{ errors.status[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Note" :state="errors.note ? false : null">
                <b-form-textarea v-model="expense.note" :state="errors.note ? false : null" />
                <b-form-invalid-feedback v-if="errors.note">{{ errors.note[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <div class="text-right">
                <b-button type="submit" variant="primary" @click="submitExpense">
                  {{ expenseId ? 'Update Expense' : 'Create Expense' }}
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
      expense: {
        amount: '',
        note: '',
        expense_category_id: 1,
        expense_date: this.getTodayDate(),
        status: 'active',
      },
      categoryOptions: [],
      statusOptions: [
        { value: 'active', text: 'Active' },
        { value: 'inactive', text: 'Inactive' },
      ],
      errors: {},
    };
  },
  computed: {
    expenseId() {
      return this.$route?.params?.id;
    },
    title() {
      return this.expenseId ? 'Edit Expense' : 'Add Expense';
    },
    items() {
      return [
        { text: "Expenses", href: "/expenses" },
        { text: this.title, active: true },
      ];
    },
  },
  created() {
    this.fetchCategories();
    if (this.expenseId) {
      this.fetchExpense();
    }
  },
  methods: {
    getTodayDate() {
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    },
    fetchExpense() {
      this.$http.get(`/expenses/${this.expenseId}`)
        .then(response => {
          this.expense = response.data;
        })
        .catch(err => {
          console.error("Error fetching expense", err);
        });
    },
    fetchCategories() {
      this.$http.get('/expense-categories')
        .then(response => {
          this.categoryOptions = response.data.map(cat => ({ value: cat.id, text: cat.name }));
        });
    },
    submitExpense() {
      if (this.expenseId) {
        this.updateExpense();
      } else {
        this.createExpense();
      }
    },
    createExpense() {
      this.$http.post('/expenses', this.expense)
        .then(() => {
          this.$router.push({ name: 'Expenses' });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    updateExpense() {
      this.$http.put(`/expenses/${this.expenseId}`, this.expense)
        .then(() => {
          this.$router.push({ name: 'Expenses' });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    cancel() {
      this.$router.push({ name: 'Expenses' });
    },
  }
};
</script>
