<script>
import Vertical from "./vertical.vue";
import Horizontal from "./horizontal.vue";
import { useAppStore } from '@/state/pinia/appStore';
import '../../css/app.css';
import 'flatpickr/dist/flatpickr.min.css';

export default {
  components: { Vertical, Horizontal },
  data() {
    return {
      store: null,
    };
  },
  beforeCreate() {
    if (localStorage.getItem("layout")) {
      var layout = localStorage.getItem("layout");
      layout = JSON.parse(layout);

      // layout boxed
      if (layout.width == "boxed") {
        document.body.setAttribute('data-layout-size', 'boxed');
      }

      // sidebar
      if (layout.sidebar) {
        this.$root.changeSidebar(layout.sidebar);
      }

      if (layout.mode == "dark") {
        document.body.setAttribute('data-layout-mode', 'dark');
      }

    }
  },
  computed: {
    layoutType() {
      return this.$root.layout?.type;
    },
    loader() {
      return this.store?.loader;
    }
  },
  methods: {
    onRoutechange() {
      var layout = localStorage.getItem("layout");
      if (layout) {
        layout = JSON.parse(layout);
        if (layout.loader == true) {
          this.store.setLoader(true);
        }
      }
    }
  },
  created(){
    this.store = useAppStore();
  },
  mounted() {

    // set loader
    setTimeout(() => {
      this.store.setLoader(false);
    }, 400);

    this.$root.loadRightCollapse();

    // document.querySelector("html").setAttribute('dir', 'rtl');
  },
  watch: {
    $route: {
      handler: "onRoutechange",
      immediate: true,
      deep: true,
    },
  }
};
</script>

<template>
  <!-- Loader -->
  <div id="preloader" v-if="loader">
    <div id="status">
      <div class="spinner-chase">
        <div class="chase-dot"></div>
        <div class="chase-dot"></div>
        <div class="chase-dot"></div>
        <div class="chase-dot"></div>
        <div class="chase-dot"></div>
        <div class="chase-dot"></div>
      </div>
    </div>
  </div>
  <div>
    <Vertical v-if="layoutType === 'vertical'">
      <slot />
    </Vertical>

    <Horizontal v-if="layoutType === 'horizontal'">
      <slot />
    </Horizontal>
  </div>
</template>


<!--<style src="node_modules/flatpickr/dist/flatpickr.css"></style>-->
