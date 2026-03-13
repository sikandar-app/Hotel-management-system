<template>
    <Layout>
        <PageHeader :title="title" :items="items" class="mb-3" />

        <b-card class="mb-4">
            <b-card-header>
                <b-row class="align-items-center">
                    <b-col md="4">
                        <b-button @click="addExpense" variant="primary" class="mb-2 custom-button"
                                  v-if="$hasPermission(['expenses-all', 'expenses-create'])">
                            Add Expense
                        </b-button>
                    </b-col>
                    <b-col md="4">
                        <CustomDateRange :placeHolder="'Select Date Range'" @set-date="setDate" />
                    </b-col>
                    <b-col md="4">
                        <b-form-input v-model="searchQuery" placeholder="Search Expenses..." type="text"
                                      class="form-control" @input="onSearchInput" />
                    </b-col>
                </b-row>
            </b-card-header>
            <b-card-body>
                <b-table :items="filteredExpenses" :fields="tableFields" :current-page="currentPage"
                         :per-page="perPage" :total-rows="totalExpenses" responsive>
                    <template #head(id)>
                        <span @click="onSortChange('id')" class="click-btn">
                            #
                            <SortingColumn :sortBy="sortStates.sortBy"
                                           :sortDirection="sortStates.sortDirection"
                                           columnName="id" />
                        </span>
                    </template>

                    <template #cell(status)="row">
                        <b-badge :variant="$helpers.getStatusVariant(row.item.status)"
                                 class="text-capitalize custom-badge">
                            {{ row.item.status }}
                        </b-badge>
                    </template>

                    <template #cell(actions)="row">
                        <div class="text-end">
                            <b-button v-if="$hasPermission(['expenses-all', 'expenses-edit'])"
                                      @click="editExpense(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-edit icon-edit"></i>
                            </b-button>
                            <b-button v-if="$hasPermission(['expenses-all', 'expenses-delete'])"
                                      @click="deleteExpense(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-trash icon-delete"></i>
                            </b-button>
                        </div>
                    </template>
                </b-table>

                <div v-if="!filteredExpenses.length && searchQuery" class="text-center">
                    <p>No Expenses found for "{{ searchQuery }}".</p>
                </div>

                <b-pagination v-model="currentPage" :total-rows="totalExpenses" :per-page="perPage"
                              class="mt-2 justify-content-end" size="sm" />
            </b-card-body>
        </b-card>

        <b-modal v-model="showDeleteModal" title="Confirm Delete" ok-title="Delete" @ok="confirmDelete">
            <p>Are you sure you want to delete this expense?</p>
        </b-modal>
    </Layout>
</template>

<script>
import Layout from "@/layouts/main.vue";
import {
    BTable, BButton, BModal, BCard, BCardHeader, BCardBody,
    BFormInput, BPagination, BRow, BCol
} from 'bootstrap-vue-next';
import PageHeader from "@/components/page-header.vue";
import { debounce } from 'lodash';

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
            title: "Expenses",
            items: [
                { text: "Dashboard", href: "/" },
                { text: "Expenses", active: true }
            ],
            expenses: [],
            currentPage: 1,
            perPage: 10,
            totalExpenses: 0,
            searchQuery: '',
            sortStates: { sortBy: 'id', sortDirection: 'desc' },
            showDeleteModal: false,
            expenseToDelete: null,
            dateRange: [],
        };
    },
    computed: {
        filteredExpenses() {
            return this.expenses;
        },
        tableFields() {
            return [
                { key: 'id', label: '#', thClass: 'text-start' },
                { key: 'amount', label: 'Amount' },
                { key: 'note', label: 'Note' },
                { key: 'category', label: 'Category', formatter: (value) => value.name || 'N/A' },
                { key: 'expense_date', label: 'Expense Date' },
                { key: 'created_by', label: 'Created By' },
                { key: 'status', label: 'Status' },
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' },
            ];
        }
    },
    created() {
        this.fetchExpenses();
    },
    watch: {
        searchQuery() {
            this.currentPage = 1;
            this.fetchExpenses(this.searchQuery);
        }
    },
    methods: {
        setDate(dates){
            this.dateRange = dates;
            this.fetchExpenses();
        },
        fetchExpenses(search = '') {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: search,
                sort_by: this.sortStates.sortBy,
                sort_direction: this.sortStates.sortDirection
            });

            this.$http.get(`/expenses?${params.toString()}`).then(response => {
                this.expenses = response.data.expenses;
                this.totalExpenses = response.data.total;
            });
        },
        onSortChange(sortBy) {
            if (this.sortStates.sortBy === sortBy) {
                this.sortStates.sortDirection =
                    this.sortStates.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortStates.sortBy = sortBy;
                this.sortStates.sortDirection = 'asc';
            }
            this.fetchExpenses();
        },
        addExpense() {
            this.$router.push({ name: 'AddExpense' });
        },
        editExpense(id) {
            this.$router.push({ name: 'EditExpense', params: { id } });
        },
        deleteExpense(id) {
            this.expenseToDelete = id;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            this.$http.delete(`/expenses/${this.expenseToDelete}`).then(() => {
                this.fetchExpenses(this.searchQuery);
                this.expenseToDelete = null;
                this.showDeleteModal = false;
            });
        },
        onSearchInput: debounce(function () {
            this.currentPage = 1;
            this.fetchExpenses(this.searchQuery);
        }, 300),
    }
};
</script>