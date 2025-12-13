<template>
  <div class="max-w-2xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">جدولة معاينة جديدة</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div>
          <label class="form-label">العميل *</label>
          <select v-model="form.lead_id" class="form-input" required>
            <option :value="null">-- اختر العميل --</option>
            <option v-for="lead in leads" :key="lead.id" :value="lead.id">{{ lead.name }} - {{ lead.phone }}</option>
          </select>
        </div>
        <div>
          <label class="form-label">المهندس *</label>
          <select v-model="form.engineer_id" class="form-input" required>
            <option :value="null">-- اختر المهندس --</option>
            <option v-for="eng in engineers" :key="eng.id" :value="eng.id">{{ eng.name }}</option>
          </select>
        </div>
        <div>
          <label class="form-label">موعد المعاينة *</label>
          <input v-model="form.scheduled_at" type="datetime-local" class="form-input" required />
        </div>
        <div>
          <label class="form-label">العنوان *</label>
          <textarea v-model="form.address" class="form-input" rows="2" required></textarea>
        </div>
        <div>
          <label class="form-label">متطلبات العميل</label>
          <textarea v-model="form.client_requirements" class="form-input" rows="3"></textarea>
        </div>
        <div>
          <label class="form-label">ملاحظات</label>
          <textarea v-model="form.notes" class="form-input" rows="2"></textarea>
        </div>
        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/site-visits" class="btn btn-secondary">إلغاء</router-link>
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
const engineers = ref([]);

const form = reactive({
  lead_id: route.query.lead_id ? parseInt(route.query.lead_id) : null,
  engineer_id: null,
  scheduled_at: '',
  address: '',
  client_requirements: '',
  notes: ''
});

const fetchData = async () => {
  const [leadsRes, engRes] = await Promise.all([
    axios.get('/leads', { params: { per_page: 100 } }),
    axios.get('/users-engineers')
  ]);
  leads.value = leadsRes.data.data;
  engineers.value = engRes.data.data;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/site-visits', form);
    router.push('/site-visits');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>
