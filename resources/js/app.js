/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

// import './bootstrap';
import { createApp } from 'vue';
import routes from './router/index'
import App from '../js/App.vue'
import VueApexCharts from "vue3-apexcharts";
import helper from "./mixins/layouts.mixin";
import Vuelidate from "vuelidate";
import Maska from 'maska'
import { hasPermission, isAdmin } from './helpers/permission.js';
import * as helpers from './helpers/helpers.js';
import SortingColumn from './components/common/SortingColumn.vue';
import api from './services/api';
import { createPinia } from 'pinia';
import piniaPersistedstate from 'pinia-plugin-persistedstate';
import Notifications from '@kyvg/vue3-notification';
import CustomDateRange from './components/common/CustomDateRange.vue';
import CustomSelectBox from './components/common/CustomSelectBox.vue';
import AlertBox from "./components/common/AlertBox.vue";

import i18n from './i18n'
import { BootstrapVueNext } from 'bootstrap-vue-next';

// import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue-next/dist/bootstrap-vue-next.css'
import "leaflet/dist/leaflet.css";

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance so they are ready
 * to use in your application's views. An example is included for you.
 */
// console.log("STORE TEST:", store); // <== Add this

const app = createApp({});
const pinia = createPinia();
pinia.use(piniaPersistedstate);

app.config.globalProperties.$hasPermission = hasPermission;
app.config.globalProperties.$isAdmin = isAdmin;
app.config.globalProperties.$helpers = helpers;
app.config.globalProperties.$http = api;
app.config.globalProperties.appName = import.meta.env.VITE_APP_NAME;
app.config.globalProperties.baseUrl = import.meta.env.VITE_APP_URL;

app.component("elitestays-component", App);
app.component('SortingColumn', SortingColumn);
app.component('CustomDateRange', CustomDateRange);
app.component('CustomSelectBox', CustomSelectBox);
app.component('AlertBox', AlertBox);
app.use(routes);
app.use(pinia);
app.use(App);
app.use(i18n);
app.use(Maska);
app.mixin(helper);
app.use(Vuelidate);
app.use(VueApexCharts);
app.use(BootstrapVueNext);
app.use(Notifications);
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// Object.entries(import.meta.glob('./**/*.vue', { eager: true })).forEach(([path, definition]) => {
//     app.component(path.split('/').pop().replace(/\.\w+$/, ''), definition.default);
// });

/**
 * Finally, we will attach the application instance to a HTML element with
 * an "id" attribute of "app". This element is included with the "auth"
 * scaffolding. Otherwise, you will need to add an element yourself.
 */

app.mount('#app');
