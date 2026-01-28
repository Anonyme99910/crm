<template>
  <div class="max-w-2xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إضافة خامة جديدة</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div><label class="form-label">الكود *</label><input v-model="form.code" class="form-input" required /></div>
          <div><label class="form-label">الاسم *</label><input v-model="form.name" class="form-input" required /></div>
          <div><label class="form-label">الوحدة *</label><input v-model="form.unit" class="form-input" required /></div>
          <div><label class="form-label">سعر الوحدة *</label><input v-model="form.unit_price" type="number" class="form-input" min="0" step="0.01" required /></div>
          <div><label class="form-label">المخزون الحالي</label><input v-model="form.current_stock" type="number" class="form-input" min="0" /></div>
          <div><label class="form-label">الحد الأدنى</label><input v-model="form.minimum_stock" type="number" class="form-input" min="0" /></div>
        </div>
        <div><label class="form-label">الوصف</label><textarea v-model="form.description" class="form-input" rows="2"></textarea></div>
        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/dashboard/inventory" class="btn btn-secondary">إلغاء</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const loading = ref(false);
const form = reactive({ code: '', name: '', unit: '', unit_price: 0, current_stock: 0, minimum_stock: 0, description: '' });

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/materials', form);
    router.push('/dashboard/inventory');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};
</script>
