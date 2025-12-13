<template>
  <div class="space-y-6">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">{{ isEdit ? 'تعديل عرض السعر' : 'إنشاء عرض سعر جديد' }}</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-label">العميل *</label>
            <select v-model="form.lead_id" class="form-input" required>
              <option :value="null">-- اختر --</option>
              <option v-for="lead in leads" :key="lead.id" :value="lead.id">{{ lead.name }}</option>
            </select>
          </div>
          <div>
            <label class="form-label">العنوان *</label>
            <input v-model="form.title" type="text" class="form-input" required />
          </div>
          <div>
            <label class="form-label">صالح حتى *</label>
            <input v-model="form.valid_until" type="date" class="form-input" required />
          </div>
          <div>
            <label class="form-label">نسبة الضريبة %</label>
            <input v-model="form.tax_rate" type="number" class="form-input" min="0" max="100" />
          </div>
        </div>
        <div>
          <label class="form-label">الوصف</label>
          <textarea v-model="form.description" class="form-input" rows="2"></textarea>
        </div>

        <div>
          <div class="flex items-center justify-between mb-4">
            <h4 class="font-semibold">البنود</h4>
            <button type="button" @click="addItem" class="btn btn-sm btn-secondary">إضافة بند</button>
          </div>
          <div class="space-y-4">
            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 gap-3 items-end p-4 bg-gray-50 rounded-lg">
              <div class="col-span-4">
                <label class="form-label text-xs">البند</label>
                <input v-model="item.name" type="text" class="form-input" required />
              </div>
              <div class="col-span-2">
                <label class="form-label text-xs">الوحدة</label>
                <input v-model="item.unit" type="text" class="form-input" />
              </div>
              <div class="col-span-2">
                <label class="form-label text-xs">الكمية</label>
                <input v-model="item.quantity" type="number" class="form-input" min="0" step="0.01" required />
              </div>
              <div class="col-span-2">
                <label class="form-label text-xs">سعر الوحدة</label>
                <input v-model="item.unit_price" type="number" class="form-input" min="0" step="0.01" required />
              </div>
              <div class="col-span-1 text-center font-medium">{{ formatCurrency(item.quantity * item.unit_price) }}</div>
              <div class="col-span-1">
                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-end">
          <div class="w-64 space-y-2 text-sm">
            <div class="flex justify-between"><span>الإجمالي الفرعي:</span><span>{{ formatCurrency(subtotal) }}</span></div>
            <div class="flex justify-between"><span>الضريبة ({{ form.tax_rate }}%):</span><span>{{ formatCurrency(taxAmount) }}</span></div>
            <div class="flex justify-between font-bold text-lg border-t pt-2"><span>الإجمالي:</span><span>{{ formatCurrency(total) }}</span></div>
          </div>
        </div>

        <div>
          <label class="form-label">الشروط والأحكام</label>
          <textarea v-model="form.terms_conditions" class="form-input" rows="3"></textarea>
        </div>

        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/quotations" class="btn btn-secondary">إلغاء</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const router = useRouter();
const isEdit = computed(() => !!route.params.id);
const loading = ref(false);
const leads = ref([]);

const form = reactive({
  lead_id: route.query.lead_id ? parseInt(route.query.lead_id) : null,
  site_visit_id: route.query.site_visit_id ? parseInt(route.query.site_visit_id) : null,
  title: '',
  description: '',
  tax_rate: 14,
  valid_until: dayjs().add(30, 'day').format('YYYY-MM-DD'),
  terms_conditions: '',
  items: [{ name: '', unit: 'وحدة', quantity: 1, unit_price: 0 }]
});

const subtotal = computed(() => form.items.reduce((sum, i) => sum + (i.quantity * i.unit_price), 0));
const taxAmount = computed(() => subtotal.value * (form.tax_rate / 100));
const total = computed(() => subtotal.value + taxAmount.value);

const addItem = () => form.items.push({ name: '', unit: 'وحدة', quantity: 1, unit_price: 0 });
const removeItem = (index) => form.items.length > 1 && form.items.splice(index, 1);
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);

const fetchData = async () => {
  const { data } = await axios.get('/leads', { params: { per_page: 100 } });
  leads.value = data.data;
  if (isEdit.value) {
    const res = await axios.get(`/quotations/${route.params.id}`);
    Object.assign(form, res.data.data);
  }
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    if (isEdit.value) {
      await axios.put(`/quotations/${route.params.id}`, form);
    } else {
      await axios.post('/quotations', form);
    }
    router.push('/quotations');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);
</script>
