<template>
  <Layout>
    <PageHeader
      :title="'Occupancy Calendar'"
      :items="[
        { text: 'Dashboard', href: '/' },
        { text: 'Occupancy', active: true }
      ]"
      class="mb-3"
    />

    <b-card>
      <b-card-header>
        <b-row>
          <b-col md="3">
            <b-form-select v-model="filters.status" :options="statusOptions" />
          </b-col>
          <b-col md="3">
            <b-form-select v-model="filters.room">
              <option value="all">All Rooms</option>
              <option v-for="room in rooms" :key="room.id" :value="room.id">
                Room {{ room.room_number }}
              </option>
            </b-form-select>
          </b-col>
          <b-col md="3">
            <!-- <b-form-input v-model="filters.monthYear" type="month" /> -->
            <flat-pickr 
                v-model="filters.monthYear" 
                :config="flatpickrOptions" 
                :placeHolder="'Select Month'" 
                class="form-control" 
            ></flat-pickr>
          </b-col>
          <b-col md="3">
            <b-form-select v-model="calendarView" :options="viewOptions" />
          </b-col>
        </b-row>
      </b-card-header>

      <b-card-body>
        <div v-if="calendarView === 'table'">
          <table class="table table-bordered table-sm text-center">
            <thead>
              <tr>
                <th>Room</th>
                <th v-for="day in daysInMonth" :key="day">{{ day }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="room in filteredRooms" :key="room.id">
                <td>Room {{ room.room_number }}</td>
                <td
                  v-for="day in daysInMonth"
                  :key="day"
                  :class="getCellClass(room.id, day)"
                >
                  <span
                    v-if="getOccupancyStatus(room.id, day)"
                    @click="openModal(room.id, day)"
                    style="cursor: pointer;"
                  >
                    {{ getOccupancyStatus(room.id, day) }}
                  </span>
                  <span v-else>
                    {{ getOccupancyStatus(room.id, day) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else-if="calendarView === 'calendar'">
          <FullCalendar
            :options="calendarOptions"
          />
        </div>
      </b-card-body>
    </b-card>
    <div
      class="modal fade"
      tabindex="-1"
      :class="{ show: showModal }"
      :style="{ display: showModal ? 'block' : 'none' }"
      @mouseleave="closeModal"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Booking Details</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body" v-if="modalBooking">
            <p>
              <strong>Booking #: </strong>
              <a :href="`/bookings/${modalBooking.id}`" target="_blank">{{ modalBooking.booking_number }}</a>
            </p>
            <p><strong>Guest: </strong> {{ modalBooking.name }}</p>
            <p><strong>Check-in: </strong> {{ modalBooking.check_in_date }}</p>
            <p><strong>Check-out: </strong> {{ modalBooking.check_out_date }}</p>
            <p><strong>Status: </strong> 
              <b-badge :variant="$helpers.getStatusVariant(modalBooking.status)" class="text-capitalize custom-badge">
                {{ modalBooking.status }}
              </b-badge>
            </p>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script>
import Layout from '@/layouts/main.vue';
import PageHeader from '@/components/page-header.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import monthSelectPlugin from 'flatpickr/dist/plugins/monthSelect';
import flatPickr from 'vue-flatpickr-component';
import "flatpickr/dist/flatpickr.css";
import { onMounted, nextTick } from 'vue'
import {
  BCard,
  BCardHeader,
  BCardBody,
  BFormInput,
  BFormSelect,
  BRow,
  BCol
} from 'bootstrap-vue-next';

export default {
  name: 'OccupancyCalendarTable',
  components: {
    Layout,
    PageHeader,
    FullCalendar,
    BCard,
    BCardHeader,
    BCardBody,
    BFormInput,
    BFormSelect,
    BRow,
    BCol,
    flatPickr
  },
  data() {
    return {
      rooms: [],
      occupancyData: [],
      calendarView: 'table',
      filters: {
        status: 'any',
        room: 'all',
        monthYear: new Date().toISOString().slice(0, 7)
      },
      statusOptions: [
        { value: 'any', text: 'All Statuses' },
        { value: 'draft', text: 'Draft' },
        { value: 'confirmed', text: 'Confirmed' }
      ],
      viewOptions: [
        { value: 'table', text: 'Table View' },
        { value: 'calendar', text: 'Calendar View' }
      ],
      flatpickrOptions: {
        dateFormat: 'Y-m',
        plugins: [
          new monthSelectPlugin({
            shorthand: true,
            dateFormat: 'Y-m',
            altFormat: 'F Y'
          })
        ]
      },
      showModal: false,
      modalBooking: null,
    };
  },
  computed: {
    daysInMonth() {
      const [year, month] = this.filters.monthYear.split('-').map(Number);
      return Array.from({ length: new Date(year, month, 0).getDate() }, (_, i) => i + 1);
    },
    filteredRooms() {
      return this.filters.room === 'all'
        ? this.rooms
        : this.rooms.filter(r => r.id === parseInt(this.filters.room));
    },
    calendarEvents() {
      return this.occupancyData.map(entry => (
      {
        title: `Name: ${this.getRoomNumber(entry.name)} - Booking: ${entry.booking_number}`,
        start: entry.check_in_date,
        end: entry.check_out_date,
        url: `/bookings/${entry.id}`,
        color: entry.status === 'confirmed' ? '#34c38f' : '#74788d'
      }));
    },
    calendarOptions() {
      const [year, month] = this.filters.monthYear.split('-').map(Number);
      return {
        plugins: [dayGridPlugin, interactionPlugin],
        initialView: 'dayGridMonth',
        initialDate: `${year}-${String(month).padStart(2, '0')}-01`,
        editable: false,
        selectable: false,
        height: 'auto',
        events: this.calendarEvents // ✅ Pass here
      };
    }
  },
  methods: {
    openModal(roomId, day) {
      if (!this.occupancyData || !this.occupancyData.length) return;
      const booking = this.occupancyData.find(
        b =>
          b.room_id === roomId &&
          new Date(b.check_in_date).getDate() <= day &&
          new Date(b.check_out_date).getDate() >= day
      )
      if (booking) {
        this.modalBooking = booking
        this.showModal = true
      }
    },
    closeModal() {
      this.showModal = false
      this.modalBooking = null
    },
    getRoomNumber(id) {
      const r = this.rooms.find(r => r.id === id);
      return r ? r.room_number : id;
    },
    async fetchData() {
      try {
        const response = await this.$http.get('/occupancy', {
          params: this.filters
        });
        this.occupancyData = response.occupancy;
        this.rooms = response.rooms;
      } catch (err) {
        console.error('Fetch error', err);
      }
    },
    getCellClass(roomId, day) {
      const [year, month] = this.filters.monthYear.split('-').map(Number);
      const date = new Date(year, month - 1, day);
      date.setHours(0, 0, 0, 0);

      const booking = this.occupancyData.find(entry => {
        const checkIn = new Date(entry.check_in_date);
        const checkOut = new Date(entry.check_out_date);
        checkIn.setHours(0, 0, 0, 0);
        checkOut.setHours(0, 0, 0, 0);
        return (
          entry.room_id === roomId &&
          checkIn <= date &&
          date <= checkOut
        );
      });
      if (!booking) return 'bg-white';

      return booking.status == 'confirmed'
        ? 'bg-confirmed text-white'
        : 'bg-draft text-white';
    },
    getOccupancyStatus(roomId, day) {
      return this.isOccupied(roomId, day) ? '✓' : '';
    },
    isOccupied(roomId, day) {
      const [year, month] = this.filters.monthYear.split('-').map(Number);
      const date = new Date(year, month - 1, day);
      date.setHours(0, 0, 0, 0); // normalize

      return this.occupancyData.some(booking => {
        const checkIn = new Date(booking.check_in_date);
        const checkOut = new Date(booking.check_out_date);
        checkIn.setHours(0, 0, 0, 0);
        checkOut.setHours(0, 0, 0, 0);
        return (
          booking.room_id === roomId &&
          checkIn <= date &&
          date <= checkOut // ✅ now includes last day
        );
      });
    }

  },
  watch: {
    filters: {
      handler: 'fetchData',
      deep: true
    }
  },
  mounted() {
    this.fetchData();
  }
};
</script>

<style scoped>
.table th,
.table td {
  text-align: center;
  vertical-align: middle;
  padding: 0.4rem;
}
.bg-confirmed {
  background-color: #34c38f !important;
}
.bg-draft {
  background-color: #74788d !important;
}
a.fc-event.fc-event-start.fc-event-past.fc-daygrid-event.fc-daygrid-block-event.fc-h-event{
  text-align: left !important;
}
</style>
