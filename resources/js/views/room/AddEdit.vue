<template>
  <Layout>
    <PageHeader :title="title" :items="items" class="mb-3"/>

    <b-row>
      <b-col cols="12" md="8" class="mx-auto">
        <b-card>
          <b-card-header class="font-weight-bold">{{ title }}</b-card-header>
          <b-card-body>
            <b-form>
              <b-form-group label="Room Number" :state="errors.room_number ? false : null">
                <b-form-input v-model="room.room_number" :state="errors.room_number ? false : null" />
                <b-form-invalid-feedback v-if="errors.room_number">{{ errors.room_number[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Floor" :state="errors.floor ? false : null">
                <b-form-input v-model="room.floor" :state="errors.floor ? false : null" />
                <b-form-invalid-feedback v-if="errors.floor">{{ errors.floor[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Building" :state="errors.building ? false : null">
                <b-form-input v-model="room.building" :state="errors.building ? false : null" />
                <b-form-invalid-feedback v-if="errors.building">{{ errors.building[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Address" :state="errors.address ? false : null">
                <b-form-input v-model="room.address" :state="errors.address ? false : null" />
                <b-form-invalid-feedback v-if="errors.address">{{ errors.address[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Price Per Night" :state="errors.price_per_night ? false : null">
                <b-form-input type="number" v-model="room.price_per_night" :state="errors.price_per_night ? false : null" />
                <b-form-invalid-feedback v-if="errors.price_per_night">{{ errors.price_per_night[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <b-form-group label="Status" :state="errors.status ? false : null">
                <b-form-select v-model="room.status" :options="statusOptions" :state="errors.status ? false : null" />
                <b-form-invalid-feedback v-if="errors.status">{{ errors.status[0] }}</b-form-invalid-feedback>
              </b-form-group>

              <div class="text-right">
                <b-button type="submit" variant="primary" @click="submitRoom">
                  {{ roomId ? 'Update Room' : 'Create Room' }}
                </b-button>
                <b-button variant="secondary" class="ml-2" @click="cancel">Cancel</b-button>
              </div>
            </b-form>
          </b-card-body>
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
    return {
      room: {
        room_number: '',
        floor: '',
        building: '',
        address: '',
        price_per_night: '',
        status: 'available',
      },
      statusOptions: [
        { value: 'available', text: 'Available' },
        { value: 'booked', text: 'Booked' },
        { value: 'not_available', text: 'Not Available' },
      ],
      errors: {},
    };
  },
  computed: {
    roomId() {
      return this.$route?.params?.id;
    },
    title() {
      return this.roomId ? 'Edit Room' : 'Add Room';
    },
    items() {
      return [
        { text: "Rooms", href: "/rooms" },
        { text: this.title, active: true },
      ];
    },
  },
  created() {
    if (this.roomId) {
      this.fetchRoom();
    }
  },
  methods: {
    fetchRoom() {
      this.$http.get(`/room/${this.roomId}`)
        .then(response => {
          this.room = response.data;
        })
        .catch(err => {
          console.error("Error fetching room", err);
        });
    },
    submitRoom() {
      if (this.roomId) {
        this.updateRoom();
      } else {
        this.createRoom();
      }
    },
    createRoom() {
      this.$http.post('/room', this.room)
        .then(() => {
          this.$router.push({ name: 'Rooms' });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    updateRoom() {
      this.$http.put(`/room/${this.roomId}`, this.room)
        .then(() => {
          this.$router.push({ name: 'Rooms' });
        })
        .catch(error => {
          if (error.response?.data?.errors) {
            this.errors = error.response.data.errors;
          }
        });
    },
    cancel() {
      this.$router.push({ name: 'Rooms' });
    },
  }
};
</script>
