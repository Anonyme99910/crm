<template>
  <div class="max-w-3xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إنشاء عقد جديد</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div><label class="form-label">المشروع *</label>
            <select v-model="form.project_id" class="form-input" required>
              <option :value="null">-- اختر --</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div><label class="form-label">عنوان العقد *</label><input v-model="form.title" class="form-input" required /></div>
          <div><label class="form-label">قيمة العقد *</label><input v-model="form.total_value" type="number" class="form-input" min="0" required /></div>
          <div><label class="form-label">تاريخ البدء *</label><input v-model="form.start_date" type="date" class="form-input" required /></div>
          <div><label class="form-label">تاريخ الانتهاء *</label><input v-model="form.end_date" type="date" class="form-input" required /></div>
        </div>
        <div><label class="form-label">نطاق العمل</label><textarea v-model="form.scope_of_work" class="form-input" rows="3"></textarea></div>
        <div><label class="form-label">الشروط والأحكام</label><textarea v-model="form.terms_conditions" class="form-input" rows="3"></textarea></div>
        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/contracts" class="btn btn-secondary">إلغاء</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const loading = ref(false);
const projects = ref([]);
const form = reactive({ project_id: null, title: '', total_value: 0, start_date: '', end_date: '', scope_of_work: '', terms_conditions: '' });

const fetchProjects = async () => {
  const { data } = await axios.get('/projects');
  projects.value = data.data;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/contracts', form);
    router.push('/contracts');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProjects);
</script>
