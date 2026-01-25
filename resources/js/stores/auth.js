import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: null,
        loading: false,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'admin',
        isManager: (state) => ['admin', 'manager'].includes(state.user?.role),
    },

    actions: {
        initAuth() {
            const token = localStorage.getItem('token');
            const user = localStorage.getItem('user');
            
            if (token && user) {
                this.token = token;
                this.user = JSON.parse(user);
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            }
        },

        async login(email, password) {
            this.loading = true;
            try {
                const response = await axios.post('/login', { email, password });
                const { user, token } = response.data.data;
                
                this.user = user;
                this.token = token;
                
                localStorage.setItem('token', token);
                localStorage.setItem('user', JSON.stringify(user));
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
                
                return { success: true };
            } catch (error) {
                return { 
                    success: false, 
                    message: error.response?.data?.message || 'حدث خطأ أثناء تسجيل الدخول'
                };
            } finally {
                this.loading = false;
            }
        },

        async logout() {
            try {
                await axios.post('/logout');
            } catch (e) {
                // Ignore errors
            }
            
            this.user = null;
            this.token = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        },

        async fetchUser() {
            try {
                const response = await axios.get('/user');
                this.user = response.data.data;
                localStorage.setItem('user', JSON.stringify(this.user));
            } catch (error) {
                this.logout();
            }
        }
    }
});
