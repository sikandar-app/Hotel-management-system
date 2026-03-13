<template>
    <Layout>
        <PageHeader :title="title" :items="items" />
        <b-card class="mb-4 mt-2">
            <b-card-body>
                <b-table :items="filteredSettings" :fields="tableFields" :current-page="currentPage"
                    :total-rows="totalSettings" :responsive="true">
                    <template #cell(actions)="row">
                        <div class="text-end">
                            <b-button @click="editSetting(row.item.id)"
                                class="transparent-button border-0 bg-transparent">
                                <i class="fa fa-edit icon-edit"></i>
                            </b-button>
                        </div>
                    </template>
                    <template #cell(index)="row">
                        {{ (currentPage - 1) * perPage + row.index + 1 }}
                    </template>
                </b-table>
                <div v-if="!filteredSettings.length && searchQuery" class="text-center">
                    <p>No settings found for "{{ searchQuery }}".</p>
                </div>

                <b-pagination v-model="currentPage" :total-rows="totalSettings" :per-page="perPage"
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
                { text: "Settings", active: true },
            ],
            title: "Settings",
            settings: [],
            totalSettings: 0,
            currentPage: 1,
            perPage: 10,
            searchQuery: '',
            showDeleteModal: false,
            userToDelete: null,
        };
    },
    created() {
        this.fetchSettings();
    },
    computed: {
        filteredSettings() {
            return this.settings;
        },
        tableFields() {
            return [
                { key: 'name', label: 'Name' },
                { key: 'email', label: 'Email' },
                { key: 'phone', label: 'phone' },
                { key: 'address', label: 'Address' },
                { key: 'facebook_link', label: 'facebook_link'},
                { key: 'instagram_link', label: 'instagram_link'},
                { key: 'twitter_link', label: 'twitter_link'},
                { key: 'youtube_link', label: 'youtube_link'},
                { key: 'advance_payment_taken_in_percentage', label: 'Advance Payment Taken In Percentage' },
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-end' },
            ];
        },
    },
    methods: {
        fetchSettings() {
            this.$http.get(`/settings`)
                .then(response => {
                    this.settings = response.data;
                });
        },
        editSetting(userId) {
            this.$router.push({ name: 'EditSetting', params: { id: userId } });
        },
    },
};
</script>