<template>
    <Layout>
        <PageHeader :title="title" :items="items" class="mb-3" />

        <b-card class="mb-4">
            <b-card-header>
                <b-row class="align-items-center">
                    <b-col md="4">
                        <b-button @click="addRoom" variant="primary" class="mb-2 custom-button"
                                  v-if="$hasPermission(['room-all', 'room-create'])">
                            Add Room
                        </b-button>
                    </b-col>
                    <b-col md="4">
                        <CustomDateRange :placeHolder="'Select Date Range'" @set-date="setDate" />
                    </b-col>
                    <b-col md="4">
                        <b-form-input v-model="searchQuery" placeholder="Search Rooms..." type="text"
                                      class="form-control" @input="onSearchInput" />
                    </b-col>
                </b-row>
            </b-card-header>
            <b-card-body>
                <b-table :items="filteredRooms" :fields="tableFields" :current-page="currentPage"
                         :per-page="perPage" :total-rows="totalRooms" responsive>
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
                            <!-- <b-button v-if="$hasPermission(['room-all', 'room-view'])"
                                      @click="viewRoom(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fas fa-eye icon-view"></i>
                            </b-button> -->
                            <b-button v-if="$hasPermission(['room-all', 'room-edit']) && row.item.bookings.length == 0"
                                      @click="editRoom(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-edit icon-edit"></i>
                            </b-button>
                            <b-button v-if="$hasPermission(['room-all', 'room-delete']) && row.item.bookings.length == 0"
                                      @click="deleteRoom(row.item.id)"
                                      class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-trash icon-delete"></i>
                            </b-button>
                        </div>
                    </template>
                </b-table>

                <div v-if="!filteredRooms.length && searchQuery" class="text-center">
                    <p>No Rooms found for "{{ searchQuery }}".</p>
                </div>

                <b-pagination v-model="currentPage" :total-rows="totalRooms" :per-page="perPage"
                              class="mt-2 justify-content-end" size="sm" />
            </b-card-body>
        </b-card>

        <b-modal v-model="showDeleteModal" title="Confirm Delete" ok-title="Delete" @ok="confirmDelete">
            <p>Are you sure you want to delete this room?</p>
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
            title: "Rooms",
            items: [
                { text: "Dashboard", href: "/" },
                { text: "Rooms", active: true }
            ],
            Rooms: [],
            currentPage: 1,
            perPage: 10,
            totalRooms: 0,
            searchQuery: '',
            sortStates: { sortBy: 'id', sortDirection: 'desc' },
            showDeleteModal: false,
            roomToDelete: null,
            dateRange: [],
        };
    },
    computed: {
        filteredRooms() {
            return this.Rooms;
        },
        tableFields() {
            return [
                { key: 'id', label: '#', thClass: 'text-start' },
                { key: 'room_number', label: 'Room Number' },
                { key: 'floor', label: 'Floor' },
                { key: 'building', label: 'Building' },
                { key: 'address', label: 'Address' },
                { key: 'price_per_night', label: 'Price per Night' },
                { key: 'status', label: 'Status' },
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' },
            ];
        }
    },
    created() {
        this.fetchRooms();
    },
    watch: {
        searchQuery() {
            this.currentPage = 1;
            this.fetchRooms(this.searchQuery);
        }
    },
    methods: {
        setDate(dates){
            this.dateRange = dates;
            this.fetchRooms();
        },
        fetchRooms(search = '') {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                search: search,
                sort_by: this.sortStates.sortBy,
                sort_direction: this.sortStates.sortDirection
            });

            this.$http.get(`/room?${params.toString()}`).then(response => {
                this.Rooms = response.data.data;
                this.totalRooms = response.data.total;
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
            this.fetchRooms();
        },
        addRoom() {
            this.$router.push({ name: 'AddRoom' });
        },
        editRoom(id) {
            this.$router.push({ name: 'EditRoom', params: { id } });
        },
        viewRoom(id) {
            this.$router.push({ name: 'ViewRoom', params: { id } });
        },
        deleteRoom(id) {
            this.roomToDelete = id;
            this.showDeleteModal = true;
        },
        confirmDelete() {
            this.$http.delete(`/room/${this.roomToDelete}`).then(() => {
                this.fetchRooms(this.searchQuery);
                this.roomToDelete = null;
                this.showDeleteModal = false;
            });
        },
        onSearchInput: debounce(function () {
            this.currentPage = 1;
            this.fetchRooms(this.searchQuery);
        }, 300),
    }
};
</script>
