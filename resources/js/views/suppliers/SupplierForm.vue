<template>
  <div class="max-w-2xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إضافة مورد/مقاول</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div><label class="form-label">الاسم *</label><input v-model="form.name" class="form-input" required /></div>
          <div><label class="form-label">النوع *</label>
            <select v-model="form.type" class="form-input" required>
              <option value="supplier">مورد</option>
              <option value="contractor">مقاول</option>
              <option value="both">مورد ومقاول</option>
            </select>
          </div>
          <div><label class="form-label">الهاتف *</label><input v-model="form.phone" class="form-input" dir="ltr" required /></div>
          <div><label class="form-label">البريد</label><input v-model="form.email" type="email" class="form-input" dir="ltr" /></div>
          <div><label class="form-label">جهة الاتصال</label><input v-model="form.contact_person" class="form-input" /></div>
          <div><label class="form-label">التخصص</label><input v-model="form.specialization" class="form-input" /></div>
        </div>
        <div><label class="form-label">العنوان</label><textarea v-model="form.address" class="form-input" rows="2"></textarea></div>
        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/dashboard/suppliers" class="btn btn-secondary">إلغاء</router-link>
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
const form = reactive({ name: '', type: 'supplier', phone: '', email: '', contact_person: '', specialization: '', address: '' });

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/suppliers', form);
    router.push('/dashboard/suppliers');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};
</script>
