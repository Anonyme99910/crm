<template>
  <div class="max-w-3xl">
    <div class="card">
      <div class="card-header">
        <h3 class="font-semibold text-gray-900">{{ isEdit ? 'تعديل بيانات العميل' : 'إضافة عميل جديد' }}</h3>
      </div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-label">الاسم *</label>
            <input v-model="form.name" type="text" class="form-input" required />
          </div>
          <div>
            <label class="form-label">رقم الهاتف *</label>
            <input v-model="form.phone" type="tel" class="form-input" dir="ltr" required />
          </div>
          <div>
            <label class="form-label">البريد الإلكتروني</label>
            <input v-model="form.email" type="email" class="form-input" dir="ltr" />
          </div>
          <div>
            <label class="form-label">واتساب</label>
            <input v-model="form.whatsapp" type="tel" class="form-input" dir="ltr" />
          </div>
          <div>
            <label class="form-label">المصدر *</label>
            <select v-model="form.source" class="form-input" required>
              <option value="whatsapp">واتساب</option>
              <option value="ads">إعلانات</option>
              <option value="call">مكالمات</option>
              <option value="website">موقع</option>
              <option value="referral">إحالة</option>
              <option value="other">أخرى</option>
            </select>
          </div>
          <div>
            <label class="form-label">الحالة</label>
            <select v-model="form.status" class="form-input">
              <option value="cold">بارد</option>
              <option value="warm">دافئ</option>
              <option value="hot">ساخن</option>
            </select>
          </div>
          <div>
            <label class="form-label">نوع المشروع</label>
            <input v-model="form.project_type" type="text" class="form-input" placeholder="شقة، فيلا، مكتب..." />
          </div>
          <div>
            <label class="form-label">الميزانية المتوقعة</label>
            <input v-model="form.estimated_budget" type="number" class="form-input" min="0" />
          </div>
          <div>
            <label class="form-label">المسؤول</label>
            <select v-model="form.assigned_to" class="form-input">
              <option :value="null">-- اختر --</option>
              <option v-for="user in salesTeam" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">تاريخ الإغلاق المتوقع</label>
            <input v-model="form.expected_close_date" type="date" class="form-input" />
          </div>
        </div>
        <div>
          <label class="form-label">العنوان</label>
          <textarea v-model="form.address" class="form-input" rows="2"></textarea>
        </div>
        <div>
          <label class="form-label">ملاحظات</label>
          <textarea v-model="form.notes" class="form-input" rows="3"></textarea>
        </div>
        <div class="flex items-center gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">
            {{ loading ? 'جاري الحفظ...' : 'حفظ' }}
          </button>
          <router-link :to="{ name: 'leads' }" class="btn btn-secondary">إلغاء</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const salesTeam = ref([]);

const form = reactive({
  name: '', phone: '', email: '', whatsapp: '', address: '',
  source: 'other', status: 'cold', stage: 'new',
  assigned_to: null, notes: '', project_type: '',
  estimated_budget: null, expected_close_date: ''
});

const fetchLead = async () => {
  if (!isEdit.value) return;
  try {
    const { data } = await axios.get(`/leads/${route.params.id}`);
    Object.assign(form, data.data);
  } catch (error) {
    console.error(error);
  }
};

const fetchSalesTeam = async () => {
  try {
    const { data } = await axios.get('/users-sales-team');
    salesTeam.value = data.data;
  } catch (error) {
    console.error(error);
  }
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (isEdit.value) {
      await axios.put(`/leads/${route.params.id}`, form);
    } else {
      await axios.post('/leads', form);
    }
    router.push({ name: 'leads' });
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchLead();
  fetchSalesTeam();
});
</script>
