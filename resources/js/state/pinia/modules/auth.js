import api from '@/services/api'; // adjust path if needed

export const authState = {
  permissions: [],
  user:  null,
  role: null,
};

export const authActions = {
  setPermissions(perms) {
    this.auth.permissions = perms;
  },
  setUser(user) {
    this.auth.user = user;
  },
  setRole(role) {
    this.auth.role = role;
  },

  async login(credentials) {
    try {
      const response = await api.post('/login', credentials);
      const { user, permissions, role } = response.data;

      this.setUser(user);
      this.setPermissions(permissions);
      this.setRole(role);
      return { success: true };
    } catch (error) {
      if (error.response?.status === 422) {
        return {
          success: false,
          errors: error.response.data.errors // ⬅️ Laravel validation errors
        };
      }

      return {
        success: false,
        errors: { general: ['Something went wrong. Please try again.'] }
      };
    }
  },
  async fetchUser() {
      try {
        const response = await api.get('/user');
        const { user, permissions, role } = response.data;
        this.setUser(user);
        this.setPermissions(permissions);
        this.setRole(role);
      } catch (error) {
        if (error.response?.status === 422) {
          return {
            success: false,
            errors: error.response.data.errors // ⬅️ Laravel validation errors
          };
        }

        return {
          success: false,
          errors: { general: ['Something went wrong. Please try again.'] }
        };
      }
  },

  async logout() {
    this.setUser(null);
    this.setPermissions([]);
    this.setRole(null);
    await api.post('/logout');
    this.$reset();
  }
};
