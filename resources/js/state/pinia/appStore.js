import { defineStore } from 'pinia';
import { authState, authActions } from './modules/auth';

export const useAppStore = defineStore('app', {
  state: () => ({
    auth: { ...authState },
    loader: false,
  }),

  actions: {
    ...authActions,
    setLoader(loader) {
      this.loader = loader;
    },
  },
  persist: true,
});
