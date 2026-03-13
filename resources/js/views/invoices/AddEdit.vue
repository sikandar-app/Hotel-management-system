<template>
  <Layout>
    <PageHeader :title="title" :items="items" class="mb-3" />

    <b-row >
      <b-col cols="12" md="8" class="mx-auto">
        <b-card v-if="booking && invoice">
          <div class="text-center mb-4">
            <h4 class="font-weight-bold">PAYMENT INVOICE</h4>
            <div class="d-flex justify-content-between">
              <div><strong>Date:</strong> {{ invoice.payment_date }}</div>
              <div><strong>Customer:</strong> {{ booking.name }}</div>
            </div>
            <div class="d-flex justify-content-between">
              <div><strong>Booking #:</strong> {{ booking.booking_number }}</div>
              <div><strong>Phone:</strong> {{ booking.contact_number }}</div>
            </div>
          </div>

          <b-table 
            :items="[booking]" 
            :fields="[
              { key: 'check_in_date', label: 'Check-in' },
              {
                key: 'check_out_date',
                label: 'Check-out',
                thClass: 'text-end',
                tdClass: 'text-end'
              }
            ]" 
            small 
            bordered
            class="no-header-style"
          >
            <template #cell(check_in_date)="data">
              {{ data.item.check_in_date }}
            </template>
            <template #cell(check_out_date)="data">
                {{ data.item.check_out_date }}
            </template>
          </b-table>

          <b-table 
            :items="bookingRows" 
            :fields="[
               { key: 'number_of_nights', label: 'Nights' },
               { key: 'room', label: 'Room #' },
               { key: 'price_per_night', label: 'Rate', tdClass: 'text-end', thClass: 'text-end' },
               { key: 'room_total', label: 'Total', tdClass: 'text-end', thClass: 'text-end' },
            ]"
            small 
            bordered
            class="mb-0"
          >
            <template #cell(number_of_nights)="data">
              {{ data.item.number_of_nights }}
            </template>
            <template #cell(room)="data">
              {{ data.item?.room?.room_number }}
            </template>
            <template #cell(price_per_night)="data">
              {{ data.item.price_per_night }}
            </template>
            <template #cell(room_total)="data">
              <template v-if="data.item.price_per_night">
                {{ data.item.price_per_night * data.item.number_of_nights}}
              </template>
              <template v-else>
                <span>-</span>
              </template>
            </template>
            <!-- Handle empty row rendering -->
            <template #cell()="data">
              <span v-if="!data.item.room" class="invisible">-</span>
            </template>
          </b-table>
          <!-- Summary Table with aligned columns -->
          <table class="table table-bordered table-sm mt-0" style="table-layout: fixed; width: 100%;">
            <colgroup>
              <col style="width: 29%">
              <col style="width: 33%">
              <col style="width: 30%">
              <col style="width: 25%">
            </colgroup>
            <tbody>
              <tr>
                <td></td>
                <td class="fw-bold">Discount</td>
                <td class="text-end">{{ (booking.discount / booking.number_of_nights).toFixed(2) }}</td>
                <td class="text-end">{{ booking.discount }}</td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Due Amount</td>
                <td class="text-end">{{ booking.price_per_night }}</td>
                <td class="text-end">{{ booking.price_per_night * booking.number_of_nights }}</td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Sales Tax</td>
                <td class="text-end"></td>
                <td class="text-end">{{salesTax}}</td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Sub Total</td>
                <td class="text-end"></td>
                <td class="text-end">{{salesTax + (booking.price_per_night * booking.number_of_nights)}}</td>
              </tr>
              <tr v-if="booking?.total_paid">
                <td></td>
                <td class="fw-bold">Total Paid</td>
                <td class="text-end"></td>
                <td class="text-end">{{booking.total_paid}}</td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Received Amount</td>
                <td></td>
                <td class="text-end p-0">
                  <b-form-input class="mb-0 no-border-radius" v-model="invoice.amount_paid" step="0.01" :state="errors.amount_paid ? false : null" />
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Payment Method</td>
                <td></td>
                <td class="text-end p-0">
                    <b-form-select class="no-border-radius" v-model="invoice.payment_method" :options="paymentMethodOptions" />
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Note</td>
                <td class="text-end p-0" colspan="2">
                    <b-form-textarea v-model="invoice.note" class="no-border-radius" />
                </td>
              </tr>
              <tr>
                <td></td>
                <td class="fw-bold">Pending Dues</td>
                <td></td>
                <td class="text-end">{{ (booking.net_total - booking.total_paid) - invoice.amount_paid }}</td>
              </tr>
              <tr>
                <td class="no-border"></td>
                <td class="no-border"></td>
                <td class="no-border text-end fw-bold">Total</td>
                <td class="text-end fw-bold">{{ invoice.amount_paid }}</td>
              </tr>
            </tbody>
          </table>

          <!-- Payment Form -->
          <div class="text-right">
            <b-button type="submit" variant="primary" @click="submitTax">{{ invoiceId ? 'Update Invoice' : 'Create Invoice' }}</b-button>
            <b-button variant="secondary" class="ml-2" @click="cancel">Cancel</b-button>
          </div>

          <div class="mt-4 text-center border-top pt-3">
            <strong>HotelPro - Hotel Management System</strong><br />
            Thank you for your business!<br />
            Email: sikndarabbas27@gmail.com | Phone: +92 300 1234567
          </div>
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
    const today = new Date().toISOString().slice(0, 10);
    return {
      invoice: {
        amount_paid: 0,
        amount_due: 0,
        payment_method: 'cash',
        payment_date: today,
        note: '',
        booking_id: null,
        booking_number: null,
        name:null,
        contact_number: null,
        check_in_date: null,
        check_out_date: null,
        number_of_nights: null,
        price_per_night: null,
        room_total: null,
        discount: null,
        net_total: null,
        sales_tax: null,
      },
      paymentMethodOptions: [
        { value: 'cash', text: 'Cash' },
        { value: 'card', text: 'Card' },
        { value: 'bank_transfer', text: 'Bank Transfer' },
      ],
      errors: {},
      booking: null,
      payments: [],
    };
  },
  computed: {
    salesTax() {
      return Math.abs((this.booking.total_amount - this.booking.discount) - this.booking.net_total);
    },
    bookingRows() {
      const mainRow = this.booking ? [this.booking] : []
      const emptyRowsNeeded = 3 - mainRow.length
      const emptyRows = Array(emptyRowsNeeded).fill({}) // Add 2 empty rows
      return [...mainRow, ...emptyRows]
    },
    bookingId() {
      return this.$route?.params?.booking_id;
    },
    invoiceId() {
      return this.$route?.params?.invoice_id;
    },
    title() {
      return this.invoiceId ? 'Edit Invoice' : 'Add Invoice';
    },
    items() {
      return [
        { text: "Bookings", href: `/bookings/${this.bookingId}` },
        { text: this.title, active: true },
      ];
    },
  },
  created() {
    if (this.invoiceId) {
      this.fetchInvoice();
    }

    this.fetchBooking();
    this.fetchPayments();
  },
  methods: {
    fetchBooking() {
      this.$http.get(`/booking/${this.bookingId}`)
        .then(res => {
          this.booking = res.data;
          if (!this.invoiceId){
            this.invoice.amount_due = this.booking.net_total - this.booking.total_paid
            this.invoice.amount_paid = this.booking.net_total - this.booking.total_paid
          }
        })
        .catch(() => { });
    },
    fetchPayments() {
      this.$http.get(`/invoices/all/${this.bookingId}?status=approved`)
        .then(res => { this.payments = res.data.invoices; })
        .catch(() => { });
    },
    fetchInvoice() {
      this.$http.get(`/invoices/${this.invoiceId}`)
        .then(response => {
          this.invoice.amount_paid = response.data.amount_paid;
          this.invoice.payment_method = response.data.payment_method;
          this.invoice.payment_date = response.data.payment_date;
          this.invoice.note = response.data.note;
        })
        .catch(err => {
          console.error("Error fetching invoice", err);
        });
    },
    submitTax() {
      if (this.invoiceId) {
        this.updateInvoice();
      } else {
        this.createInvoice();
      }
    },
    createInvoice() {
      this.invoice.booking_id = this.bookingId;
      this.$http.post('/invoices', this.invoice)
        .then(() => {
          this.$router.push({ name: 'EditBooking', params: { id: this.bookingId } });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    updateInvoice() {
      this.invoice.booking_id = this.bookingId;
      this.$http.put(`/invoices/${this.invoiceId}`, this.invoice)
        .then(() => {
          this.$router.push({ name: 'EditBooking', params: { id: this.bookingId } });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    cancel() {
      this.$router.push({ name: 'EditBooking', params: { id: this.bookingId } });
    },
  }
};
</script>

<style scoped>
.no-header-style thead th {
  background-color: transparent !important;
  border: none !important;
}
</style>