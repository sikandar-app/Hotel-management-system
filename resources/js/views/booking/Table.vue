<template>
    <Layout>
        <PageHeader :title="title" :items="items" class="mb-3" />
        <b-card class="mb-4">
            <b-card-header>
                <b-row class="align-items-center">
                    <b-col md="4">
                        <b-button @click="addBooking" variant="primary" class="mb-2 custom-button" v-if="$hasPermission(['booking-all', 'booking-create'])">Add Booking</b-button>
                    </b-col>
                    <b-col md="4">
                        <CustomDateRange :placeHolder="'Select Date Range'" @set-date="setDate" />
                    </b-col>
                    <b-col md="4">
                        <b-form-input v-model="searchQuery" placeholder="Search Bookings..." type="text"
                            class="form-control"  @input="onSearchInput" />
                    </b-col>
                </b-row>
            </b-card-header>
            <b-card-body>
                <b-table :items="filteredBookings" :fields="tableFields" :current-page="currentPage"
                    :total-rows="totalBookings" :responsive="true">
                    <template #head(id)>
                        <span @click="onSortChange('id')" class="click-btn">
                            #
                            <SortingColumn 
                            :sortBy="sortStates?.sortBy"
                            :sortDirection="sortStates?.sortDirection"
                            columnName="id"
                            ></SortingColumn>
                        </span>
                    </template>
                    <template #cell(booking_number)="row">
                        <a href="javascript:void(0);" @click="viewBooking(row.item.id)" class="click-btn">
                            {{ row.item.booking_number }}
                        </a>
                    </template>
                    <template #cell(status)="row">
                        <b-badge :variant="$helpers.getStatusVariant(row.item.status)" class="text-capitalize custom-badge">
                        {{ row.item.status }}
                        </b-badge>
                    </template>
                    <template #cell(actions)="row">
                        <div class="text-end">
                            <b-button v-b-tooltip.hover title="Confirm Action" v-if="$hasPermission(['booking-all', 'booking-confirmed']) && row.item.status !== 'confirmed'"
                                @click="confirmedBooking(row.item.id)" class="transparent-button border-0 bg-transparent">
                                <i class="fas fa-check icon-view"></i>
                            </b-button>
                            <!-- <b-button v-if="$hasPermission(['booking-all', 'booking-view'])"
                                @click="viewBooking(row.item.id)" class="transparent-button border-0 bg-transparent">
                                <i class="fas fa-eye icon-view"></i>
                            </b-button> -->
                            <b-button v-if="$hasPermission(['booking-all', 'booking-edit'])" @click="editBooking(row.item.id)"
                                class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-edit icon-edit"></i>
                            </b-button>
                            <b-button v-if="$hasPermission(['booking-all', 'booking-delete']) && row.item.status !== 'confirmed'"
                                @click="deleteBooking(row.item.id)" class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-trash icon-delete"></i>
                            </b-button>
                            <b-button 
                            class="transparent-button border-0 bg-transparent" 
                            target="_blank"
                            :href="`${baseUrl}/api/booking/export-pdf/${row.item.id}`"
                            variant="primary" 
                            v-if="$hasPermission(['booking-all', 'booking-export'])"
                            >
                                <i class="fa fa-file-pdf" style="color: red;"></i>                
                            </b-button>
                        </div>
                    </template>
                    <template #cell(index)="row">
                        {{ (currentPage - 1) * perPage + row.index + 1 }}
                    </template>
                </b-table>
                <div v-if="!filteredBookings.length && searchQuery" class="text-center">
                    <p>No Bookings found for "{{ searchQuery }}".</p>
                </div>

                <b-pagination v-model="currentPage" :total-rows="totalBookings" :per-page="perPage"
                    class="mt-2 justify-content-end" size="sm" />
            </b-card-body>
        </b-card>
        <b-modal v-model="showDeleteModal" title="Confirm Delete" ok-title="Delete" @ok="confirmDelete">
            <p>Are you sure you want to delete this Booking?</p>
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
                { text: "Bookings", active: true },
            ],
            title: "Bookings",
            Bookings: [], // keep this but understand it's booking data
            totalBookings: 0,
            currentPage: 1,
            perPage: 10,
            searchQuery: '',
            showDeleteModal: false,
            BookingToDelete: null,
            searchParams: {},
            sortStates: {},
            dateRange: [],
        };
    },
    created() {
        this.fetchBookings();
    },
    computed: {
        filteredBookings() {
            // Filter Bookings based on search query
            return this.Bookings;
        },
        tableFields() {
            return [
                { key: 'id', label: '#', tdClass: 'text-start', thClass: 'text-start'},
                { key: 'booking_number', label: 'Booking No' },
                { key: 'name', label: 'Name' },
                { key: 'contact_number', label: 'Contact Number' },
                { key: 'check_in_date', label: 'Check-in' },
                { key: 'check_out_date', label: 'Check-out' },
                { key: 'net_total', label: 'Net Total' },
                { key: 'status', label: 'Status' },
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' },
            ];
        },
    },
    watch: {
        // Watch for changes to searchQuery and reset pagination
        searchQuery(newQuery) {
            this.currentPage = 1; // Reset to the first page when searching
            this.fetchBookings(newQuery); // Fetch Bookings based on search query
        },
    },
    methods: {
        setDate(dates){
            this.dateRange = dates;
            this.fetchBookings();
        },
        onSortChange(sortBy) {
            // Initialize the sorting state for the table if it doesn't exist
            if (!this.sortStates) {
                this.sortStates.push({
                    sortBy: '',
                    sortDirection: '',
                });
            }

            // Toggle sorting direction if the same column is clicked
            if (this.sortStates.sortBy === sortBy) {
                this.sortStates.sortDirection =
                this.sortStates.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                // Set new sorting column and default direction
                this.sortStates.sortBy = sortBy;
                this.sortStates.sortDirection = 'asc';
            }
            this.fetchBookings();
        },
        fetchBookings(search = '') {
            this.searchParams.page = this.currentPage;
            this.searchParams.per_page = this.perPage;
            this.searchParams.search = search;
            this.searchParams.sort_by = this.sortStates.sortBy || 'date';
            this.searchParams.sort_direction = this.sortStates.sortDirection || 'desc';
            if (this.dateRange && this.dateRange.start_date && this.dateRange.end_date) {
                this.searchParams.start_date = this.dateRange.start_date;
                this.searchParams.end_date = this.dateRange.end_date;
            } 

            const params = new URLSearchParams(this.searchParams);
            this.$http.get(`/booking?${params.toString()}`)
                .then(response => {
                    this.Bookings = response.data.bookings;
                    this.totalBookings = response.data.total;
                });
        },
        addBooking() {
            this.$router.push({ name: 'AddBooking' });
        },
        editBooking(bookingId) {
            this.$router.push({ name: 'EditBooking', params: { id: bookingId } });
        },
        viewBooking(bookingId) {
            this.$router.push({ name: 'ViewBooking', params: { id: bookingId } });
        },
        deleteBooking(bookingId) {
            this.bookingToDelete = bookingId;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            this.$http.delete(`/booking/${this.bookingToDelete}`).then(() => {
                this.fetchBookings(this.searchQuery); // Refetch Bookings after delete
                this.showDeleteModal = false;
                this.bookingToDelete = null;
            });
        },
        confirmedBooking(bookingId) {
            this.$http.post(`/booking/confirmed/${bookingId}`).then(() => {
                this.fetchBookings(this.searchQuery);
            });
        },
        onSearchInput: debounce(function () {
            this.currentPage = 1; // Reset to the first page on new search
            this.fetchBookings(this.searchQuery); // Fetch expenses based on search query
        }, 300),
    },
};
</script>