<template>
  <div class="max-w-3xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إنشاء مشروع جديد</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-label">اسم المشروع *</label>
            <input v-model="form.name" type="text" class="form-input" required />
          </div>
          <div>
            <label class="form-label">العميل *</label>
            <select v-model="form.lead_id" class="form-input" required>
              <option :value="null">-- اختر --</option>
              <option v-for="lead in leads" :key="lead.id" :value="lead.id">{{ lead.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">مدير المشروع *</label>
            <select v-model="form.manager_id" class="form-input" required>
              <option :value="null">-- اختر --</option>
              <option v-for="user in managers" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">قيمة العقد *</label>
            <input v-model="form.contract_value" type="number" class="form-input" min="0" required />
          </div>
          <div>
            <label class="form-label">تاريخ البدء *</label>
            <input v-model="form.start_date" type="date" class="form-input" required />
          </div>
          <div>
            <label class="form-label">تاريخ الانتهاء المتوقع *</label>
            <input v-model="form.expected_end_date" type="date" class="form-input" required />
          </div>
        </div>
        <div>
          <label class="form-label">العنوان *</label>
          <textarea v-model="form.address" class="form-input" rows="2" required></textarea>
        </div>
        <div>
          <label class="form-label">الوصف</label>
          <textarea v-model="form.description" class="form-input" rows="3"></textarea>
        </div>
        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/projects" class="btn btn-secondary">إلغاء</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const loading = ref(false);
const leads = ref([]);
const managers = ref([]);

const form = reactive({
  name: '',
  lead_id: route.query.lead_id ? parseInt(route.query.lead_id) : null,
  quotation_id: route.query.quotation_id ? parseInt(route.query.quotation_id) : null,
  manager_id: null,
  contract_value: 0,
  start_date: '',
  expected_end_date: '',
  address: '',
  description: ''
});

const fetchData = async () => {
  const [leadsRes, usersRes] = await Promise.all([
    axios.get('/leads', { params: { per_page: 100 } }),
    axios.get('/users', { params: { role: 'manager' } })
  ]);
  leads.value = leadsRes.data.data;
  managers.value = usersRes.data.data;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/projects', form);
    router.push('/projects');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>
