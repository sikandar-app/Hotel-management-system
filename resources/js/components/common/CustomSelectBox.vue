<template>
  <b-form-group label="Select Room">
    <b-form-select
      v-model="selectedRoomId"
      :options="roomOptions"
      :state="errors.room_id ? false : null"
      :disabled="isBookingViewPage"
    ></b-form-select>
    <b-form-invalid-feedback v-if="errors.room_id">
      {{ errors.room_id[0] }}
    </b-form-invalid-feedback>
  </b-form-group>
</template>
<script>
export default {
  props: {
    rooms: {
      type: Array,
      required: true
    },
    disabled: {
      type: Boolean,
      default: false
    },
    selected: {
      type: [Number, String],
      default: null
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  data() {
    return {
      selectedRoomId: this.selected
    };
  },
  watch: {
    selectedRoomId(newVal) {
      this.$emit('update:selected', newVal);
    },
    selected(newVal) {
      this.selectedRoomId = newVal;
    }
  },
  computed: {
    roomOptions() {
      return this.rooms.map(room => ({
        value: room.id,
        text: `Room ${room.room_number}`,
      }));
    },
    isBookingViewPage() {
      return this.$route.name === "ViewBooking";
    },
  }
};
</script>
