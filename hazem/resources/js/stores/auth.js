import { defineStore } from 'pinia';
import axios from '../utils/axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  
  actions: {
    async login(credentials) {
      try {
        const response = await axios.post('api/login', credentials);
        this.token = response.data.token;
        this.user = response.data.user;
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        return true;
      } catch (error) {
        console.error('Login error:', error);
        throw error;
      }
    },
    
    async logout() {
      try {
        await axios.post('api/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
      }
    },
    
    async checkAuth() {
      if (this.token) {
        try {
          const response = await axios.get('api/me');
          this.user = response.data;
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        } catch (error) {
          this.logout();
        }
      }
    },
  },
});
