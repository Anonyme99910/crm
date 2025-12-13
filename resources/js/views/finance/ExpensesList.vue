<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-bold">المصروفات</h2>
      <button @click="showModal = true" class="btn btn-primary">تسجيل مصروف</button>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>الرقم</th><th>التصنيف</th><th>الوصف</th><th>المشروع</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr>
          </thead>
          <tbody>
            <tr v-for="e in expenses" :key="e.id">
              <td class="font-medium">{{ e.expense_number }}</td>
              <td>{{ e.category }}</td>
              <td>{{ e.description?.substring(0, 30) }}...</td>
              <td>{{ e.project?.name || '-' }}</td>
              <td class="text-red-600 font-medium">{{ formatCurrency(e.amount) }}</td>
              <td><span :class="statusClass(e.status)" class="badge">{{ statusLabel(e.status) }}</span></td>
              <td>{{ formatDate(e.expense_date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Expense Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">تسجيل مصروف</h3>
        <form @submit.prevent="createExpense" class="space-y-4">
          <div><label class="form-label">التصنيف *</label>
            <select v-model="form.category" class="form-input" required>
              <option value="materials">خامات</option>
              <option value="labor">عمالة</option>
              <option value="transport">نقل</option>
              <option value="equipment">معدات</option>
              <option value="other">أخرى</option>
            </select>
          </div>
          <div><label class="form-label">المشروع</label>
            <select v-model="form.project_id" class="form-input">
              <option :value="null">-- عام --</option>
              <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div><label class="form-label">الوصف *</label><textarea v-model="form.description" class="form-input" rows="2" required></textarea></div>
          <div><label class="form-label">المبلغ *</label><input v-model="form.amount" type="number" class="form-input" min="0" required /></div>
          <div><label class="form-label">التاريخ *</label><input v-model="form.expense_date" type="date" class="form-input" required /></div>
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

const expenses = ref([]);
const projects = ref([]);
const showModal = ref(false);
const form = reactive({ category: 'materials', project_id: null, description: '', amount: 0, expense_date: dayjs().format('YYYY-MM-DD') });

const fetchData = async () => {
  const [expensesRes, projectsRes] = await Promise.all([
    axios.get('/expenses'),
    axios.get('/projects')
  ]);
  expenses.value = expensesRes.data.data;
  projects.value = projectsRes.data.data;
};

const createExpense = async () => {
  await axios.post('/expenses', form);
  showModal.value = false;
  fetchData();
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ pending: 'معلق', approved: 'موافق عليه', rejected: 'مرفوض' }[s] || s);
const statusClass = (s) => ({ pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchData);
</script>
