<template>
  <div class="max-w-3xl">
    <div class="card">
      <div class="card-header"><h3 class="font-semibold">إنشاء فاتورة</h3></div>
      <form @submit.prevent="handleSubmit" class="card-body space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div><label class="form-label">المشروع *</label>
            <select v-model="form.project_id" class="form-input" required>
              <option :value="null">-- اختر --</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div><label class="form-label">العنوان *</label><input v-model="form.title" class="form-input" required /></div>
          <div><label class="form-label">تاريخ الإصدار *</label><input v-model="form.issue_date" type="date" class="form-input" required /></div>
          <div><label class="form-label">تاريخ الاستحقاق *</label><input v-model="form.due_date" type="date" class="form-input" required /></div>
          <div><label class="form-label">نسبة الضريبة %</label><input v-model="form.tax_rate" type="number" class="form-input" min="0" max="100" /></div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="form-label">البنود</label>
            <button type="button" @click="addItem" class="text-sm text-primary-600">+ إضافة</button>
          </div>
          <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-12 gap-2 mb-2">
            <input v-model="item.description" class="form-input col-span-6" placeholder="الوصف" required />
            <input v-model="item.quantity" type="number" class="form-input col-span-2" placeholder="الكمية" min="1" required />
            <input v-model="item.unit_price" type="number" class="form-input col-span-3" placeholder="السعر" min="0" required />
            <button type="button" @click="form.items.splice(i, 1)" class="text-red-600 col-span-1">×</button>
          </div>
        </div>

        <div class="flex justify-end">
          <div class="w-64 space-y-2 text-sm">
            <div class="flex justify-between"><span>الإجمالي الفرعي:</span><span>{{ formatCurrency(subtotal) }}</span></div>
            <div class="flex justify-between"><span>الضريبة:</span><span>{{ formatCurrency(taxAmount) }}</span></div>
            <div class="flex justify-between font-bold text-lg border-t pt-2"><span>الإجمالي:</span><span>{{ formatCurrency(total) }}</span></div>
          </div>
        </div>

        <div class="flex gap-4">
          <button type="submit" :disabled="loading" class="btn btn-primary">{{ loading ? 'جاري الحفظ...' : 'حفظ' }}</button>
          <router-link to="/dashboard/invoices" class="btn btn-secondary">إلغاء</router-link>
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
const loading = ref(false);
const projects = ref([]);

const form = reactive({
  project_id: route.query.project_id ? parseInt(route.query.project_id) : null,
  contract_id: route.query.contract_id ? parseInt(route.query.contract_id) : null,
  title: '',
  issue_date: dayjs().format('YYYY-MM-DD'),
  due_date: dayjs().add(30, 'day').format('YYYY-MM-DD'),
  tax_rate: 14,
  items: [{ description: '', quantity: 1, unit_price: 0 }]
});

const subtotal = computed(() => form.items.reduce((sum, i) => sum + (i.quantity * i.unit_price), 0));
const taxAmount = computed(() => subtotal.value * (form.tax_rate / 100));
const total = computed(() => subtotal.value + taxAmount.value);

const addItem = () => form.items.push({ description: '', quantity: 1, unit_price: 0 });
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);

const fetchProjects = async () => {
  const { data } = await axios.get('/projects');
  projects.value = data.data;
};

const handleSubmit = async () => {
  loading.value = true;
  try {
    await axios.post('/invoices', form);
    router.push('/dashboard/invoices');
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchProjects);
</script>
