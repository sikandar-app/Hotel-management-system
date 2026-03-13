import axios from 'axios';
import { useAppStore } from '@/state/pinia/appStore';
import router from '../router/index'
import { notify } from '@kyvg/vue3-notification';

// Create an Axios instance
const api = axios.create({
  baseURL: "/api",
  headers: {
    'Content-Type': 'application/json',
  },
});


// Add a request interceptor to dynamically set the token
api.interceptors.request.use(
  (config) => {
    const store = useAppStore();
    if(config.method !== 'get')store.setLoader(true); // Show the loader
    return config;
  },
  (error) => Promise.reject(error)
);

// Add a response interceptor
api.interceptors.response.use(
  (response) => {
    const store = useAppStore();
    store.setLoader(false);
    if (response.config.method !== 'get') {
      notify({
        type: 'success',
        title: 'Success',
        text: response.data.message || 'Request completed successfully',
      });
    }
    return response.data; // Centralize response handling
  },
  (error) => {
    const store = useAppStore();
    store.setLoader(false); // Show the loader
    if (error.response?.status === 401) {
      store.logout(); // Clear the token and logout
      router.push({ name: 'login' });
    }
    notify({
      type: 'error',
      title: 'Error',
      text: error?.response?.data?.data ?? error?.response?.data?.message ?? 'An error occurred',
    });
    return Promise.reject(error);
  }
);

export default api;
