<template>
    <Layout>
        <PageHeader :title="title" :items="items" class="mb-3" />

        <b-card class="mb-4">
            <b-card-header>
                <b-row class="align-items-center">
                    <b-col md="4">
                        <b-button @click="addTax" variant="primary" class="mb-2 custom-button"
                                  v-if="$hasPermission(['tax-all', 'tax-create'])">
                            Add Tax
                        </b-button>
                    </b-col>
                    <b-col md="4">
                        <CustomDateRange :placeHolder="'Select Date Range'" @set-date="setDate" />
                    </b-col>
                    <b-col md="4">
                        <b-form-input v-model="searchQuery" placeholder="Search Taxes..." type="text"
                                      class="form-control" @input="onSearchInput" />
                    </b-col>
                </b-row>
            </b-card-header>
            <b-card-body>
                <b-table :items="filteredTaxes" :fields="tableFields" :current-page="currentPage"
                         :per-page="perPage" :total-rows="totalTaxes" responsive>
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
                            {{ row.item.status ? 'Active' : 'Inactive' }}
                        </b-badge>
                    </template>

                    <template #cell(actions)="row">
                        <div class="text-end">
                            <!-- <b-button v-if="$hasPermission(['tax-all', 'tax-view'])"
                                      @click="viewTax(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fas fa-eye icon-view"></i>
                            </b-button> -->
                            <b-button v-if="$hasPermission(['tax-all', 'tax-edit'])"
                                      @click="editTax(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-edit icon-edit"></i>
                            </b-button>
                            <b-button v-if="$hasPermission(['tax-all', 'tax-delete'])"
                                      @click="deleteTax(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-trash icon-delete"></i>
                            </b-button>
                        </div>
                    </template>
                </b-table>

                <div v-if="!filteredTaxes.length && searchQuery" class="text-center">
                    <p>No Taxes found for "{{ searchQuery }}".</p>
                </div>

                <b-pagination v-model="currentPage" :total-rows="totalTaxes" :per-page="perPage"
                              class="mt-2 justify-content-end" size="sm" />
            </b-card-body>
        </b-card>

        <b-modal v-model="showDeleteModal" title="Confirm Delete" ok-title="Delete" @ok="confirmDelete">
            <p>Are you sure you want to delete this tax?</p>
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
            title: "Taxes",
            items: [
                { text: "Dashboard", href: "/" },
                { text: "Taxes", active: true }
            ],
            Taxes: [],
            currentPage: 1,
            perPage: 10,
            totalTaxes: 0,
            searchQuery: '',
            sortStates: { sortBy: 'id', sortDirection: 'desc' },
            showDeleteModal: false,
            taxToDelete: null,
            dateRange: [],
        };
    },
    computed: {
        filteredTaxes() {
            return this.Taxes;
        },
        tableFields() {
            return [
                { key: 'id', label: '#', thClass: 'text-start' },
                { key: 'name', label: 'Name' },
                { key: 'type', label: 'Type' },
                { key: 'value', label: 'Value' },
                { key: 'status', label: 'Status' },
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' },
            ];
        }
    },
    created() {
        this.fetchTaxes();
    },
    watch: {
        searchQuery() {
            this.currentPage = 1;
            this.fetchTaxes(this.searchQuery);
        }
    },
    methods: {
        setDate(dates){
            this.dateRange = dates;
            this.fetchTaxes();
        },
        fetchTaxes(search = '') {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: search,
                sort_by: this.sortStates.sortBy,
                sort_direction: this.sortStates.sortDirection
            });

            this.$http.get(`/tax?${params.toString()}`).then(response => {
                this.Taxes = response.data.data;
                this.totalTaxes = response.data.total;
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
            this.fetchTaxes();
        },
        addTax() {
            this.$router.push({ name: 'AddTax' });
        },
        editTax(id) {
            this.$router.push({ name: 'EditTax', params: { id } });
        },
        viewTax(id) {
            this.$router.push({ name: 'ViewTax', params: { id } });
        },
        deleteTax(id) {
            this.taxToDelete = id;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            this.$http.delete(`/tax/${this.taxToDelete}`).then(() => {
                this.fetchTaxes(this.searchQuery);
                this.taxToDelete = null;
                this.showDeleteModal = false;
            });
        },
        onSearchInput: debounce(function () {
            this.currentPage = 1;
            this.fetchTaxes(this.searchQuery);
        }, 300),
    }
};
</script>
