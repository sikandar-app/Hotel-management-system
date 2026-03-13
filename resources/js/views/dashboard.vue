<template>
  <Layout>
    <PageHeader :title="title" :items="items" />

    <!-- Summary Cards -->
    <div class="row">
      <div class="col-md-4 offset-md-8 my-3 mt-0">
        <custom-date-range :place-holder="placeholder" @set-date="setDate"></custom-date-range>
      </div>
    </div>

    <div class="row">
      <div class="col-md-3">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Total Bookings</p>
                <h4 class="mb-0">{{ totalBookings }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                  <span class="avatar-title">
                    <i class="bx bx-calendar-check font-size-24"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Total Revenue</p>
                <h4 class="mb-0">{{ currencyFormat(totalRevenue) }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                  <span class="avatar-title">
                    <i class="bx bx-dollar font-size-24"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Occupancy Rate</p>
                <h4 class="mb-0">{{ occupancyRate }} %</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                  <span class="avatar-title">
                    <i class="bx bx-building-house font-size-24"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card mini-stats-wid">
          <div class="card-body">
            <div class="d-flex">
              <div class="flex-grow-1">
                <p class="text-muted fw-medium">Net Profit</p>
                <h4 class="mb-0">{{ currencyFormat(netProfit) }}</h4>
              </div>
              <div class="flex-shrink-0 align-self-center">
                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                  <span class="avatar-title">
                    <i class="bx bx-trending-up font-size-24"></i>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
      <div class="chart-card">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Booking Status</h2>
        <div class="chart-container">
          <canvas id="bookingStatusChart"></canvas>
        </div>
      </div>
      <div class="chart-card">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Revenue vs. Expenses</h2>
        <div class="chart-container">
          <canvas id="revenueExpenseChart"></canvas>
        </div>
      </div>
    </div>
    <!-- Bookings Table -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Bookings List</h4>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Booking #</th>
                  <th>Name</th>
                  <th>Contact</th>
                  <th>Room</th>
                  <th>Check In</th>
                  <th>Check Out</th>
                  <th>Status</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="booking in bookings" :key="booking.id">
                  <td>{{ booking.booking_number }}</td>
                  <td>{{ booking.name }}</td>
                  <td>{{ booking.contact_number }}</td>
                  <td>{{ booking.room_number }}</td>
                  <td>{{ booking.check_in_date }}</td>
                  <td>{{ booking.check_out_date }}</td>
                  <td>
                    <span :class="`badge bg-${statusColor(booking.status)}`">{{ booking.status }}</span>
                  </td>
                  <td>{{ booking.total_amount }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Expenses Table -->
    <div class="row mt-4">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Expenses List</h4>
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Amount</th>
                  <th>Note</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="expense in expenses" :key="expense.id">
                  <td>{{ expense.amount }}</td>
                  <td>{{ expense.note }}</td>
                  <td>{{ expense.expense_date }}</td>
                  <td>
                    <span :class="`badge bg-${statusColor(expense.status)}`">{{ expense.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </Layout>
</template>

<script>
import Layout from "@/layouts/main.vue";
import PageHeader from "@/components/page-header.vue";
import Chart from "chart.js/auto";

export default {
  components: { Layout, PageHeader },
  data() {
    return {
      title: "Booking Dashboard",
      items: [
        { text: "Dashboards", href: "/" },
        { text: "Bookings", active: true },
      ],
      totalBookings: 0,
      occupancyRate: 0,
      totalRevenue: 0,
      netProfit: 0,
      totalExpenses: 0,
      bookings: [],
      expenses: [],
      confirmedBookings: 0,
      draftBookings: 0,
      chartInstances: {},
      dateRange: [],
    };
  },
  methods: {
    fetchBookingDashboard() {
      this.$http.get("/booking-dashboard", {
        params: {
          start_date: this.dateRange.start_date,
          end_date: this.dateRange.end_date,
        },
      }).then(data => {
        this.occupancyRate = data.occupancy_rate;
        this.totalBookings = data.total_bookings;
        this.totalRevenue = data.total_revenue;
        this.netProfit = data.net_profit;
        this.totalExpenses = data.total_expenses;
        this.bookings = data.bookings;
        this.expenses = data.expenses;
        this.confirmedBookings = data.confirmed_bookings;
        this.draftBookings = data.draft_bookings;
        this.$nextTick(() => {
          this.renderCharts();
        });
      });
    },
    renderCharts() {
      // Destroy previous charts if they exist
      if (this.chartInstances.bookingStatusChart) this.chartInstances.bookingStatusChart.destroy();
      if (this.chartInstances.revenueExpenseChart) this.chartInstances.revenueExpenseChart.destroy();

      // Booking Status Pie Chart
      const bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
      this.chartInstances.bookingStatusChart = new Chart(bookingStatusCtx, {
        type: 'doughnut',
        data: {
          labels: ['Confirmed', 'Draft'],
          datasets: [{
            data: [this.confirmedBookings, this.draftBookings],
            backgroundColor: ['#34D399', '#9CA3AF'],
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom',
              labels: { font: { family: 'Inter' } }
            }
          }
        }
      });

      // Revenue vs. Expenses Bar Chart
      const revenueExpenseCtx = document.getElementById('revenueExpenseChart').getContext('2d');
      this.chartInstances.revenueExpenseChart = new Chart(revenueExpenseCtx, {
        type: 'bar',
        data: {
          labels: ['Revenue', 'Expenses'],
          datasets: [{
            label: 'Amount (PKR)',
            data: [this.totalRevenue, this.totalExpenses],
            backgroundColor: [
              'rgba(54, 162, 235, 0.7)',
              'rgba(255, 99, 132, 0.7)'
            ],
            borderColor: [
              'rgba(54, 162, 235, 1)',
              'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return 'PKR ' + value;
                },
                font: { family: 'Inter' }
              }
            },
            x: {
              ticks: { font: { family: 'Inter' } }
            }
          }
        }
      });
    },
    currencyFormat(value) {
      if (!value) return "PKR 0.00";
      return "PKR " + Number(value).toLocaleString("en-PK", { minimumFractionDigits: 2 });
    },
    setDate(dates) {
      this.dateRange = dates;
      this.fetchBookingDashboard();
    },
    statusColor(status) {
      switch (status) {
        case "confirmed": return "success";
        case "draft": return "warning";
        case "cancelled": return "danger";
        case "active": return "primary";
        default: return "secondary";
      }
    }
  },
  mounted() {
    this.fetchBookingDashboard();
  }
};
</script>

<style scoped>
.chart-card {
  background-color: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.chart-container {
  position: relative;
  height: 300px;
  width: 100%;
}
</style>