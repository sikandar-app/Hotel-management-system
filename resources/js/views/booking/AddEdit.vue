<template>
  <Layout>
    <PageHeader :title="titleTxt" :items="items" class="mb-3"/>
    <b-row>
      <b-col cols="12" md="12" class="text-right mb-1">
        <b-button
          v-if="bookingId && $hasPermission(['booking-all', 'booking-export'])"
          variant="outline-info"
          class="ml-2"
          target="_blank"
          :href="`${baseUrl}/api/booking/export-pdf/${bookingId}`"
        >
          Download Booking Form
        </b-button>

        <b-button
          v-if="booking.status == 'draft' && $hasPermission(['booking-all', 'booking-confirmed']) && bookingId"
          variant="outline-success"
          class="ml-2"
          target="_blank"
          @click="confirmedBooking(booking.id)"
        >
          Confirm Booking
        </b-button>
      </b-col>
      <b-col cols="12" md="12" v-if="bookingId && !booking?.is_valid_dates">
        <AlertBox
          type="danger"
          title="Danger:"
          message="Selected check-in and check-out dates conflict with another booking."
          :show="true"
        />
      </b-col>
    </b-row>

    <b-form @submit.prevent="submitBooking">
      <b-row>
        <!-- Guest Details Section -->
        <b-col cols="12" md="6">
          <b-card class="mb-4 guest-details-card">
            <b-card-header class="font-weight-bold">Guest Details</b-card-header>
            <b-card-body>
              <b-row>
                <b-col cols="12" md="12">
                  <b-form-group label="Name" :state="errors.name ? false : null">
                    <b-form-input :disabled="isBookingViewPage" v-model="booking.name" :state="errors.name ? false : null" />
                    <b-form-invalid-feedback v-if="errors.name">{{ errors.name[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Contact Number" :state="errors.contact_number ? false : null">
                    <b-form-input :disabled="isBookingViewPage" v-model="booking.contact_number" :state="errors.contact_number ? false : null" />
                    <b-form-invalid-feedback v-if="errors.contact_number">{{ errors.contact_number[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <CustomSelectBox
                    :rooms="roomOptions"
                    v-model:selected="booking.room_id"
                    :errors="errors"
                  />
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="No of Guests" :state="errors.number_of_guests ? false : null">
                    <b-form-input :disabled="isBookingViewPage" type="number" v-model="booking.number_of_guests" :state="errors.number_of_guests ? false : null" />
                    <b-form-invalid-feedback v-if="errors.number_of_guests">{{ errors.number_of_guests[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Booking Date" :state="errors.date ? false : null">
                    <b-form-input disabled type="date" v-model="booking.date" :state="errors.date ? false : null" />
                    <b-form-invalid-feedback v-if="errors.date">{{ errors.date[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="CNIC Number" :state="errors.cnic_number ? false : null">
                    <template #label>
                      <div class="d-flex align-items-center justify-content-between w-100">
                        <span v-if="!usePassport">CNIC No</span>
                        <span v-else>Passport No</span>
                        <b-form-checkbox switch v-model="usePassport" size="sm">
                          Use Passport
                        </b-form-checkbox>
                      </div>
                    </template>
                    <template v-if="!usePassport">
                      <b-form-input placeholder="CNIC No" :disabled="isBookingViewPage" v-model="booking.cnic_number" :state="errors.cnic_number ? false : null" />
                      <b-form-invalid-feedback v-if="errors.cnic_number">{{ errors.cnic_number[0] }}</b-form-invalid-feedback>
                    </template>
                    <template v-else>
                      <b-form-input placeholder="Passport No" :disabled="isBookingViewPage" v-model="booking.passport" :state="errors.passport ? false : null" />
                      <b-form-invalid-feedback v-if="errors.passport">{{ errors.passport[0] }}</b-form-invalid-feedback>
                    </template>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">                    
                  <b-form-group v-if="!isBookingViewPage" :label="usePassport ? 'Upload Passport Image' : 'Upload CNIC Image'" :state="errors.document_image ? false : null">
                      <input type="file" class="form-control" @change="handleCnicImageUpload" accept="image/*"/>
                      <b-form-invalid-feedback v-if="errors.document_image">
                        {{ errors.document_image[0] }}
                      </b-form-invalid-feedback>
                  </b-form-group>
                  <div v-if="booking.document_image_url">
                    <a :href="`${baseUrl}/storage/${booking.document_image_url}`" target="_blank">View Uploaded Image</a>
                  </div>
                </b-col>
              </b-row>
            </b-card-body>
          </b-card>
        </b-col>
        <b-col cols="12" md="6">
          <b-card class="mb-4">
            <b-card-header class="font-weight-bold">Booking and Payment Details</b-card-header>
            <b-card-body class="pb-0">
              <b-row>
                <b-col cols="12" md="6">
                  <b-form-group label="Check-in Date" :state="errors.check_in_date ? false : null">
                    <flat-pickr
                      v-if="availableDates.length > 0"
                      :key="'checkIn-'+ availableDates.join(',')"
                      v-model="booking.check_in_date"
                      :config="{
                        dateFormat: 'Y-m-d',
                        enable: availableDates,
                        defaultDate: booking.check_in_date
                      }"
                      class="form-control"
                    />
                    <b-form-invalid-feedback v-if="errors.check_in_date">
                      {{ errors.check_in_date[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Check-out Date" :state="errors.check_out_date ? false : null">
                    <flat-pickr
                      v-if="availableDates.length > 0"
                      :key="'checkOut-'+availableDates.join(',')"
                      v-model="booking.check_out_date"
                      :config="{
                        dateFormat: 'Y-m-d',
                        enable: availableDates,
                        defaultDate: booking.check_out_date
                      }"
                      class="form-control"
                    />

                    <b-form-invalid-feedback v-if="errors.check_out_date">
                      {{ errors.check_out_date[0] }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="12">
                  <b-form-group label="Approach Via" :state="errors.approach_via ? false : null">
                    <b-form-input :disabled="isBookingViewPage" v-model="booking.approach_via" :state="errors.approach_via ? false : null" />
                    <b-form-invalid-feedback v-if="errors.approach_via">{{ errors.approach_via[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Price per Night" :state="errors.price_per_night ? false : null">
                    <b-form-input :disabled="isBookingViewPage || !booking.room_id" type="number" v-model="booking.price_per_night" :state="errors.price_per_night ? false : null" />
                    <b-form-invalid-feedback v-if="errors.price_per_night">{{ errors.price_per_night[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group :label=" taxAmount ? 'Tax (' + taxAmount + ' PKR)' : 'Tax'">
                    <b-form-select
                      v-model="booking.tax_id"
                      :options="taxOptions"
                      @change="calculateBookingSummary"
                    />
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Number Of Nights">
                    <b-form-input type="number_of_nights" disabled v-model="booking.number_of_nights" />
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Discount" :state="errors.discount ? false : null">
                    <b-form-input type="number" v-model="booking.discount" :state="errors.discount ? false : null" />
                    <b-form-invalid-feedback v-if="errors.discount">{{ errors.discount[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6">
                  <b-form-group label="Total Amount">
                    <b-form-input type="total_amount" disabled v-model="booking.total_amount" />
                  </b-form-group>
                </b-col>             
                <b-col cols="12" md="6"> 
                  <b-form-group label="Net Total">
                    <b-form-input type="net_total" disabled v-model="booking.net_total" />
                  </b-form-group>
                </b-col>
              </b-row>
            </b-card-body>
          </b-card>
        </b-col>
      </b-row>
      <!-- Submit Buttons -->
      <div class="text-right mb-4" v-if="!isBookingViewPage">
        <b-button type="submit" variant="primary">
          {{ bookingId ? 'Update Booking' : 'Create Booking' }}
        </b-button>
        <b-button @click="cancelBooking" variant="secondary" class="ml-2">Cancel</b-button>
      </div>
    </b-form>
    <b-row v-if="bookingId && booking?.is_valid_dates">
        <b-col cols="12" md="12">
          <b-card>
            <b-card-body class="p-0">
              <InvoicesTable :bookingId="bookingId" :isFullPaid="booking.is_fully_paid" :netTotal="booking.net_total"></InvoicesTable>
            </b-card-body>
          </b-card>
        </b-col>
    </b-row>
    <!-- Accommodation Policies Section -->
    <b-card class="mb-4">
      <b-card-header class="font-weight-bold d-flex justify-content-between align-items-center">
        Accommodation Policies
        <b-button
          v-b-toggle.accommodation-policies
          variant="link"
          size="sm"
          class="p-0"
          @click="toggleCollapse()"
        >
          Toggle
        </b-button>
      </b-card-header>

      <b-collapse id="accommodation-policies" :visible="accommodationPoliciesToggled">
        <b-card-body>
          <b-row>
            <b-col cols="12" md="6">
              <p><strong>Check In:</strong></p>
              <ul>
                <li>Standard check-in is from 2:00 PM. Early check-ins may be allowed based on availability.</li>
                <li>A valid photo ID (e.g., CNIC, Passport) is required at check-in. A security deposit is required for incidentals.</li>
                <li>Provide your reservation number or booking confirmation at check-in.</li>
                <li>Pets are not allowed in designated rooms.</li>
              </ul>
            </b-col>
            <b-col cols="12" md="6">
              <p><strong>Check Out:</strong></p>
              <ul>
                <li>Standard check-out is by 12:00 PM. Late check-outs may be arranged based on room availability.</li>
                <li>Guests are advised to check the room for personal items before departure.</li>
                <li>Rooms will be inspected for damages or missing items after check-out. Additional charges may apply for damages.</li>
                <li>Respectful Environment: No parties or events permitted.</li>
              </ul>
            </b-col>
          </b-row>
        </b-card-body>
      </b-collapse>
    </b-card>
  </Layout>
</template>

<script>
import Layout from "@/layouts/main.vue";
import PageHeader from "@/components/page-header.vue";
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import InvoicesTable from "../../components/invoices/InvoicesTable.vue";

export default {
  components: {
    Layout,
    PageHeader,
    flatPickr,
    InvoicesTable
  },
  watch: {
    'booking.check_in_date': function () {
      this.calculateBookingSummary();
    },
    'booking.check_out_date': function () {
      this.calculateBookingSummary();
    },
    'booking.discount': 'calculateBookingSummary',
    'booking.price_per_night': 'calculateBookingSummary',
    'availableDates': 'calculateBookingSummary',
    'booking.tax_id': 'calculateBookingSummary',
    'booking.room_id'(newRoomId) {
      this.fetchRoomAvailability(newRoomId);
      this.calculateBookingSummary();
      if(this.roomOptions.length === 0) return;
      const room = this.roomOptions.find(room => room.id == newRoomId);
      const price = parseFloat(room?.price_per_night || 0);
      this.booking.price_per_night = price;
      this.booking.original_price_per_night = price;
    },
  },
  data() {
    return {
      booking: {
        name: '',
        date: this.getTodayDate(),
        contact_number: '',
        number_of_guests: '',
        cnic_number: '',
        room_id: '',
        check_in_date: this.getTodayDate(),
        check_out_date: this.getTodayDate(),
        approach_via: '',
        price_per_night: '',
        discount: 0,
        total_amount: 0,
        net_total: 0,
        number_of_nights: 0,
        document_image: "",
        tax_id: '',
        original_price_per_night: 0,
      },
      upload_document_image: null,
      errors: {},
      accommodationPoliciesToggled: false,
      roomOptions: [],
      availableDates: [],
      taxOptions: [],
      taxDetails: [],
      usePassport: false,
      taxAmount: 0,
      totalPaid: 0
    };
  },
  created() {
    this.getBooking();
    this.fetchRooms();
    this.fetchTaxes();
    this.accommodationPoliciesToggled = this.isBookingViewPage;
  },
  computed: {
    bookingId() {
      return this.$route?.params?.id;
    },
    titleTxt() {
      return this.bookingId && !this.isBookingViewPage ? "Edit Booking" : (this.isBookingViewPage ? "View Booking" :"Add Booking");
    },
    items() {
      return [
        { text: "Bookings", href: "/bookings" },
        { text: this.titleTxt, active: true },
      ];
    },
    isBookingViewPage() {
      return this.$route.name === "ViewBooking";
    },
  },
  methods: {
    async fetchTaxes() {
      try {
        const response = await this.$http.get('booking/tax');
        this.taxOptions = response.data.map(tax => ({
          value: tax.id,
          text: `${tax.name} (${tax.type === 'percentage' ? tax.value + '%' : 'PKR ' + tax.value})`,
        }));

        this.taxDetails = response.data.reduce((acc, tax) => {
          acc[tax.id] = tax;
          return acc;
        }, {});
      } catch (error) {
        console.error("Error fetching taxes", error);
      }
    },
    async fetchRoomAvailability(roomId) {
      if (!roomId) return;
      try {
        const response = await this.$http.get(`booking/room/${roomId}/availability/${this.bookingId || ''}`);
        this.availableDates = response.data;
      } catch (error) {
        this.availableDates = [];
      }
    },
    getTodayDate() {
      const today = new Date();
      const yyyy = today.getFullYear();
      const mm = String(today.getMonth() + 1).padStart(2, '0');
      const dd = String(today.getDate()).padStart(2, '0');
      return `${yyyy}-${mm}-${dd}`;
    },
    handleCnicImageUpload(event) {
      this.upload_document_image = event.target.files[0];
    },
    async fetchRooms() {
      try {
        const response = await this.$http.get('/room/list');
        this.roomOptions = response.data;
      } catch (error) {
        console.error("Error fetching expense types:", error);
      }
    },
    toggleCollapse() {
      this.accommodationPoliciesToggled = !this.accommodationPoliciesToggled;
    },
    getBooking() {
      const bookingId = this.bookingId;
      if (!bookingId) return;
      this.$http.get(`/booking/${bookingId}`)
        .then(response => {
          const data = response.data;
          this.booking.id = data.id;
          this.booking.booking_number = data.booking_number;
          this.booking.name = data.name;
          this.booking.date = data.date;
          this.booking.contact_number = data.contact_number;
          this.booking.number_of_guests = data.number_of_guests;
          this.booking.cnic_number = data.cnic_number;
          this.booking.passport = data.passport;
          if (data.passport) {
            this.usePassport = true;
          } else {
            this.usePassport = false;
          }
          this.booking.room_id = data.room_id;
          this.booking.check_in_date = data.check_in_date;
          this.booking.check_out_date = data.check_out_date;
          this.booking.approach_via = data.approach_via;
          this.booking.price_per_night = parseFloat(data.price_per_night) || 0;
          this.booking.discount = parseFloat(data.discount) || 0;
          this.booking.total_amount = data.total_amount;
          this.booking.net_total = parseFloat(data.net_total) || 0;
          this.booking.tax_id = data.tax_id;
          this.booking.number_of_nights = data.number_of_nights;
          this.booking.document_image_url = data.document_image || '';
          this.booking.is_fully_paid = data.is_fully_paid || false;
          this.booking.is_valid_dates = data.is_valid_dates || false;
          this.booking.original_price_per_night = parseFloat(data.price_per_night) || 0;
          this.booking.status = data.status;
          this.totalPaid = parseFloat(data.total_paid) || 0;
          this.fetchRoomAvailability(this.booking.room_id);
        })
        .catch(error => {
          console.error("Error fetching booking:", error);
        });
    },
    submitBooking() {
      if (this.bookingId) {
        this.updateBooking();
      } else {
        this.addBooking();
      }
    },
    addBooking() {
      const formData = new FormData();

      for (const key in this.booking) {
        formData.append(key, this.booking[key]);
      }
      
      if (this.upload_document_image) {
        formData.append("document_image", this.upload_document_image);
      }

      this.$http.post('/booking', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })
        .then(() => {
          this.$router.push({ name: 'Bookings' });
        })
        .catch(error => {
          if (error.response && error.response.data.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    updateBooking() {
      const formData = new FormData();
      
      for (const key in this.booking) {
        formData.append(key, this.booking[key] ? this.booking[key] : '');
      }
      
      if (this.upload_document_image) {
        formData.append("document_image", this.upload_document_image);
      }

      if (this.bookingId) {
        formData.append('_method', 'PUT');
      }

      this.$http.post(`/booking/${this.bookingId}`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      .then(() => {
        this.$router.push({ name: 'Bookings' });
      })
      .catch(error => {
        if (error.response && error.response.data.errors) {
          this.errors = error.response.data.errors;
        }
      });
    },
    cancelBooking() {
      this.$router.push('/bookings');
    },
    calculateBookingSummary() {
      const checkIn = new Date(this.booking.check_in_date);
      const checkOut = new Date(this.booking.check_out_date);
      if (this.booking.check_in_date && this.booking.check_out_date && checkOut > checkIn) {
        const selectedDates = this.availableDates.filter(date => date >= this.booking.check_in_date && date <= this.booking.check_out_date);
        const numberOfDays = selectedDates.length;
        this.booking.number_of_nights = numberOfDays;

        const original = parseFloat(this.booking.original_price_per_night || 0);
        const current = parseFloat(this.booking.price_per_night || 0);

        let discount = this.booking.discount || 0;
        if (current < original) {
          this.booking.discount = 0;
          discount = (original - current);
          this.booking.discount = discount;
        }

        const total = numberOfDays * current;

        let taxAmount = 0;
        if (this.booking.tax_id && this.taxDetails[this.booking.tax_id]) {
          const tax = this.taxDetails[this.booking.tax_id];
          if (tax.type === 'percentage') {
            taxAmount = (total - discount) * parseFloat(tax.value) / 100;
          } else {
            taxAmount = parseFloat(tax.value);
          }
        }

        this.taxAmount = taxAmount;
        this.booking.total_amount = total;
        this.booking.net_total = (total - discount) + taxAmount;
      } else {
        this.booking.number_of_nights = 0;
        this.booking.total_amount = 0;
        this.booking.net_total = 0;
      }
    },
    confirmedBooking(bookingId) {
        this.$http.post(`/booking/confirmed/${bookingId}`).then(() => {
            this.fetchBookings(this.searchQuery);
        });
    },
  }
};
</script>

<style>
.guest-details-card .card-body {
  /* padding-bottom: 0px !important; */
}
</style>
