<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold">المدفوعات</h2>
      <button @click="showModal = true" class="btn btn-primary">تسجيل دفعة</button>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>رقم الدفعة</th><th>المشروع</th><th>الفاتورة</th><th>المبلغ</th><th>الطريقة</th><th>التاريخ</th></tr>
          </thead>
          <tbody>
            <tr v-for="p in payments" :key="p.id">
              <td class="font-medium">{{ p.payment_number }}</td>
              <td>{{ p.project?.name }}</td>
              <td>{{ p.invoice?.invoice_number || '-' }}</td>
              <td class="text-green-600 font-medium">{{ formatCurrency(p.amount) }}</td>
              <td>{{ methodLabel(p.method) }}</td>
              <td>{{ formatDate(p.payment_date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Payment Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">تسجيل دفعة</h3>
        <form @submit.prevent="recordPayment" class="space-y-4">
          <div><label class="form-label">المشروع *</label>
            <select v-model="form.project_id" class="form-input" required>
              <option :value="null">-- اختر --</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div><label class="form-label">الفاتورة</label>
            <select v-model="form.invoice_id" class="form-input">
              <option :value="null">-- اختر --</option>
              <option v-for="inv in invoices" :key="inv.id" :value="inv.id">{{ inv.invoice_number }} - {{ formatCurrency(inv.total - inv.paid_amount) }}</option>
            </select>
          </div>
          <div><label class="form-label">المبلغ *</label><input v-model="form.amount" type="number" class="form-input" min="0" required /></div>
          <div><label class="form-label">طريقة الدفع *</label>
            <select v-model="form.method" class="form-input" required>
              <option value="cash">نقدي</option>
              <option value="bank_transfer">تحويل بنكي</option>
              <option value="check">شيك</option>
              <option value="credit_card">بطاقة ائتمان</option>
            </select>
          </div>
          <div><label class="form-label">التاريخ *</label><input v-model="form.payment_date" type="date" class="form-input" required /></div>
          <div><label class="form-label">المرجع</label><input v-model="form.reference" class="form-input" /></div>
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showModal = false" class="btn btn-secondary">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const payments = ref([]);
const projects = ref([]);
const invoices = ref([]);
const showModal = ref(false);
const form = reactive({ project_id: null, invoice_id: null, amount: 0, method: 'cash', payment_date: dayjs().format('YYYY-MM-DD'), reference: '' });

const fetchData = async () => {
  const [paymentsRes, projectsRes, invoicesRes] = await Promise.all([
    axios.get('/payments'),
    axios.get('/projects'),
    axios.get('/invoices', { params: { status: 'sent' } })
  ]);
  payments.value = paymentsRes.data.data;
  projects.value = projectsRes.data.data;
  invoices.value = invoicesRes.data.data;
};

const recordPayment = async () => {
  await axios.post('/payments', form);
  showModal.value = false;
  fetchData();
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const methodLabel = (m) => ({ cash: 'نقدي', bank_transfer: 'تحويل بنكي', check: 'شيك', credit_card: 'بطاقة ائتمان' }[m] || m);

onMounted(fetchData);
</script>
