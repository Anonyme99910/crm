<template>
  <div class="max-w-2xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إضافة مهمة جديدة</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div>
          <label class="form-label">عنوان المهمة *</label>
          <input v-model="form.title" type="text" class="form-input" required />
        </div>
        <div class="grid grid-cols-2 gap-6">
          <div>
            <label class="form-label">المشروع</label>
            <select v-model="form.project_id" class="form-input">
              <option :value="null">-- اختر --</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">المسؤول</label>
            <select v-model="form.assigned_to" class="form-input">
              <option :value="null">-- اختر --</option>
              <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">الأولوية</label>
            <select v-model="form.priority" class="form-input">
              <option value="low">منخفض</option>
              <option value="medium">متوسط</option>
              <option value="high">مرتفع</option>
              <option value="urgent">عاجل</option>
            </select>
          </div>
          <div>
            <label class="form-label">تاريخ الاستحقاق</label>
            <input v-model="form.due_date" type="date" class="form-input" />
          </div>
        </div>
        <div>
          <label class="form-label">الوصف</label>
          <textarea v-model="form.description" class="form-input" rows="3"></textarea>
        </div>
        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/dashboard/tasks" class="btn btn-secondary">إلغاء</router-link>
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
const users = ref([]);

const form = reactive({
  title: '', description: '', project_id: null, assigned_to: null,
  priority: 'medium', due_date: ''
});

const fetchData = async () => {
  const [projectsRes, usersRes] = await Promise.all([
    axios.get('/projects', { params: { per_page: 100 } }),
    axios.get('/users', { params: { per_page: 100 } })
  ]);
  projects.value = projectsRes.data.data;
  users.value = usersRes.data.data;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/tasks', form);
    router.push('/dashboard/tasks');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>
