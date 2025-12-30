<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100">
    <div class="max-w-md w-full mx-4">
      <div class="bg-white rounded-2xl shadow-2xl p-8">
        <div class="text-center mb-8">
          <img src="/hazem/Logo Transparent 2.png" alt="Hazem Abdullah" class="h-20 w-auto mx-auto mb-4">
          <h2 class="text-3xl font-bold text-gray-900">Admin Login</h2>
          <p class="text-gray-600 mt-2">Sign in to manage your website</p>
        </div>
        
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label class="block text-gray-700 font-medium mb-2">Email</label>
            <input 
              v-model="form.email" 
              type="email" 
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition"
              placeholder="admin@hazem.com"
            >
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Password</label>
            <input 
              v-model="form.password" 
              type="password" 
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition"
              placeholder="••••••••"
            >
          </div>
          
          <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ error }}
          </div>
          
          <button type="submit" class="btn-primary w-full" :disabled="loading">
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
          <p>Default credentials:</p>
          <p class="font-mono">admin@hazem.com / admin123</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useToast } from 'vue-toastification';

const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const form = ref({
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    await authStore.login(form.value);
    toast.success('Login successful!');
    router.push({ name: 'AdminDashboard' });
  } catch (err) {
    error.value = 'Invalid email or password';
    toast.error('Login failed');
  } finally {
    loading.value = false;
  }
};
</script>
