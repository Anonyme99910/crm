<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-600 to-primary-800 py-12 px-4">
    <div class="max-w-md w-full">
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900">CRM التشطيبات</h1>
          <p class="text-gray-500 mt-2">تسجيل الدخول إلى حسابك</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-6">
          <div v-if="error" class="bg-red-50 text-red-600 p-4 rounded-lg text-sm">
            {{ error }}
          </div>

          <div>
            <label class="form-label">البريد الإلكتروني</label>
            <input
              v-model="form.email"
              type="email"
              class="form-input"
              placeholder="email@example.com"
              required
              dir="ltr"
            />
          </div>

          <div>
            <label class="form-label">كلمة المرور</label>
            <input
              v-model="form.password"
              type="password"
              class="form-input"
              placeholder="••••••••"
              required
              dir="ltr"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="btn btn-primary w-full py-3"
          >
            <span v-if="loading">جاري تسجيل الدخول...</span>
            <span v-else>تسجيل الدخول</span>
          </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
          <p>بيانات الدخول التجريبية:</p>
          <p class="mt-1 font-mono text-xs">admin@crm.com / password</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: ''
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  loading.value = true;
  error.value = '';

  const result = await authStore.login(form.email, form.password);
  
  if (result.success) {
    router.push('/dashboard');
  } else {
    error.value = result.message;
  }
  
  loading.value = false;
};
</script>
