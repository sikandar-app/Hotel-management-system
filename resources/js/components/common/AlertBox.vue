<!-- components/AlertBox.vue -->
<template>
  <div
    v-if="visible"
    :class="`alert alert-${type} alert-dismissible fade show`"
    role="alert"
  >
    <strong>{{ title }}</strong> {{ message }}
    <button
      type="button"
      class="btn-close"
      @click="visible = false"
      aria-label="Close"
    ></button>
  </div>
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      default: 'warning', // can be: success, danger, info, etc.
    },
    title: {
      type: String,
      default: '',
    },
    message: {
      type: String,
      default: '',
    },
    show: {
      type: Boolean,
      default: true,
    },
  },
  data() {
    return {
      visible: this.show,
    };
  },
  watch: {
    show(val) {
      this.visible = val;
    },
  },
};
</script>