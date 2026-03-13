<template>
    <div class="table-responsive table-hover mb-0 mt-1 expenses-table" >
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h5 class="mb-0">Invoices</h5>
            <div>
                <b-button
                    v-if="invoices.length > 0 && $hasPermission(['invoices-all', 'invoices-pdf-export'])"
                    class="btn btn-info btn-sm me-2"
                    :href="`${baseUrl}/api/booking/${bookingId}/invoice/download`"
                    target="_blank"
                >
                    Download Invoice (PDF)
                </b-button>
                <button v-if="!isFullPaid" class="btn btn-success btn-sm" @click="addInvoice">
                    <i class="fa fa-plus"></i> Add Invoice
                </button>
            </div>
        </div>
        <div class="row">
          <div class="col-sm-4">
            <label class="card-radio-label custom-box">
              <div class="card-radio">
                <div class="icon-container">
                  <i class="bx bx-money font-size-24 text-warning align-middle me-1"></i>
                </div>
                <div class="text-container">
                  <span class="label">Net Total</span>
                  <b class="value">PKR {{netTotalAmount}}</b>
                </div>
              </div>
            </label>
          </div>
          <div class="col-sm-4">
            <label class="card-radio-label custom-box">
              <div class="card-radio">
                <div class="icon-container">
                  <i class="bx bx-money font-size-24 text-warning align-middle me-1"></i>
                </div>
                <div class="text-container">
                  <span class="label">Total Paid Amount</span>
                  <b class="value">PKR {{paidAmount}}</b>
                </div>
              </div>
            </label>
          </div>
          <div class="col-sm-4">
            <label class="card-radio-label custom-box">
              <div class="card-radio">
                <div class="icon-container">
                  <i class="bx bx-money font-size-24 text-warning align-middle me-1"></i>
                </div>
                <div class="text-container">
                  <span class="label">Remaining Amount</span>
                  <b class="value">PKR {{netTotal - paidAmount}}</b>
                </div>
              </div>
            </label>
          </div>
        </div>
        <b-table :items="filteredInvoices" :fields="visibleColumns" :current-page="currentPage"
            :total-rows="totalInvoices" :responsive="true">
            <template #cell(booking_number)="row">
                <span class="copy-btn" @click="$helpers.copyTextName(row.item.booking.booking_number)"
                    :title="`Click to copy ${row.item.booking.booking_number}`">
                    <i class="fa fa-copy"></i> {{ row.item.booking.booking_number }}
                </span>
            </template>
            <template #cell(status)="row">
                <b-badge :variant="$helpers.getStatusVariant(row.item.status)" class="text-capitalize custom-badge">
                {{ row.item.status }}
                </b-badge>
            </template>
            <template #cell(actions)="row">
                <div class="d-flex align-items-center">
                    <b-button  @click="approvedInvoice(row.item.id)" v-if="$hasPermission(['invoices-all', 'invoices-approved']) && row.item.status !== 'approved'" class="transparent-button border-0 bg-transparent">
                        <i class="fa fa-check icon-edit"></i>
                    </b-button>
                    <b-button  @click="deleteInvoice(row.item.id)" v-if="$hasPermission(['invoices-all', 'invoices-delete']) && row.item.status !== 'approved'" class="transparent-button border-0 bg-transparent">
                        <i class="fa fa-trash icon-delete"></i>
                    </b-button>
                    <b-button v-if="$hasPermission(['invoices-all', 'invoices-edit']) && row.item.status !== 'approved'" @click="editInvoice(row.item.id)"
                        class="transparent-button border-0 bg-transparent">
                        <i class="fa fa-edit icon-edit"></i>
                    </b-button>
                    <b-button 
                    target="_blank"
                    :href="`${baseUrl}/api/invoices/${row.item.id}/download`"
                    v-if="$hasPermission(['invoices-all', 'invoices-pdf-export']) && row.item.status == 'approved'" 
                    class="transparent-button border-0 bg-transparent"
                    >
                        <i class="fa fa-file-invoice icon-edit"></i>
                    </b-button>
                </div>
            </template>
        </b-table>
    </div>
    <div v-if="!filteredInvoices.length && searchQuery" class="text-center">
        <p>No invoices found for "{{ searchQuery }}".</p>
    </div>
    <b-pagination 
    v-model="currentPage" 
    :total-rows="totalInvoices" 
    :per-page="perPage"
    class="mt-2 justify-content-end" 
    size="sm" 
    />
</template>

<script>
import { BTable, BBadge } from 'bootstrap-vue-next';
export default {
    props: {
        bookingId: {
            type: Number,
            required: true,
        },
        isFullPaid: {
            type: Boolean,
            default: false
        },
        netTotal: {
            type: Number,
            default: 0
        }
    },
    components: {
        BTable,
        BBadge
    },
    watch: {
        currentPage(val) {
            if (val) {
                this.fetchInvoices();
            }
        },
    },
    data() {
        return {
            invoices: [],
            totalInvoices: 0,
            searchQuery: '',
            currentPage: 1,
            perPage: 10,
            dropdownVisible: false,
            dateRange: [],
            columnOptions: [
                { id: 1, value: 'id', text: '#'},
                { id: 2, value: 'invoice_number', text: 'Invoice No' },
                { id: 2, value: 'booking_number', text: 'Booking No' },
                { id: 3, value: 'amount_paid', text: 'Amount Paid' },
                { id: 6, value: 'payment_method', text: 'Payment Method' },
                { id: 6, value: 'payment_date', text: 'Payment Date' },
                { id: 7, value: 'note', text: 'Note' },
                { id: 8, value: 'status', text: 'Status' },
             ],
            searchParams: {},
            loading: false,
            paidAmount: 0
        };
    },
    async created() {
        await this.fetchInvoices();
    },
    computed: {
        visibleColumns() {
            return [
                ...this.tableFields,
            ];
        },
        netTotalAmount() {
          return parseFloat(this.netTotal).toFixed(2);  
        },
        filteredInvoices() {
            let filtered = [...this.invoices];
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(invoice =>
                    Object.keys(invoice).some(key =>
                        String(invoice[key]).toLowerCase().includes(query)
                    )
                );
            }
            return filtered;
        },
        tableFields() {
            return [
                { key: 'id', label: '#', tdClass: 'text-start', thClass: 'text-start'},
                { key: 'invoice_number', label: 'Invoice Number', tdClass: 'text-start', thClass: 'text-start'},
                { key: 'booking_number', label: 'Booking Number', tdClass: 'text-start', thClass: 'text-start'},
                { key: 'amount_paid', label: 'Amount Paid', tdClass: 'text-start', thClass: 'text-start'},
                { key: 'payment_method', label: 'Payment Method', tdClass: 'text-center', thClass: 'text-center'},
                { key: 'payment_date', label: 'Payment Date', tdClass: 'text-center', thClass: 'text-center'},
                { key: 'note', label: 'Note', tdClass: 'text-center', thClass: 'text-center'},
                { key: 'status', label: 'Status', tdClass: 'text-center', thClass: 'text-center'},
                { key: 'actions', label: 'Actions', sortable: false, thClass: 'text-center', tdClass: 'text-end' },
            ];
        },
    },
    methods: {
        setDate(dates) {
            this.dateRange = dates;
            this.fetchInvoices();
        },
        async fetchInvoices() {
            this.searchParams.page = this.currentPage;
            this.searchParams.search = this.searchQuery;
            if (this.dateRange && this.dateRange.start_date && this.dateRange.end_date) {
                this.searchParams.start_date = this.dateRange.start_date;
                this.searchParams.end_date = this.dateRange.end_date;
            } else {
                delete this.searchParams.start_date;
                delete this.searchParams.end_date;
            }
            this.searchParams.per_page = this.perPage;
            const params = new URLSearchParams(this.searchParams);
            await this.$http.get(`/invoices/all/${this.bookingId}?${params.toString()}`).then(response => {
                this.invoices = response.data.invoices;
                this.totalInvoices = response.data.total;
                this.perPage = response.data.per_page;
                this.paidAmount = response.data.paid_amount;
            });
        },
        onPerPageUpdate(newPerPage) {
            this.perPage = newPerPage;
            this.fetchInvoices();
        },
        onSearchInput() {
            this.currentPage = 1; // Reset to the first page on new search
            this.fetchInvoices(); // Fetch expenses based on search query
        },
        addInvoice() {
            this.$router.push({ name: 'AddInvoice', params: { booking_id: this.bookingId } });
        },
        approvedInvoice(invoiceId) {
            this.$http.get(`/invoices/${invoiceId}/approved`)
                .then(res => { this.fetchInvoices(); })
                .catch(() => {});
        },
        deleteInvoice(invoiceId) {
            this.$http.delete(`/invoices/${invoiceId}`)
                .then(res => { this.fetchInvoices(); })
                .catch(() => {});
        },
        editInvoice(id){
            this.$router.push({ name: 'EditInvoice', params: { booking_id: this.bookingId, invoice_id: id } });
        }
    }
};
</script>