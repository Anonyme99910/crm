<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">التحويلات بين الحسابات</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
        </svg>
        تحويل جديد
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
          <select v-model="filters.status" @change="loadTransfers" class="input-field">
            <option value="">الكل</option>
            <option value="pending">معلق</option>
            <option value="completed">مكتمل</option>
            <option value="rejected">مرفوض</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
          <input v-model="filters.from_date" type="date" @change="loadTransfers" class="input-field">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
          <input v-model="filters.to_date" type="date" @change="loadTransfers" class="input-field">
        </div>
      </div>
    </div>

    <!-- Transfers List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم التحويل</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">من حساب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">إلى حساب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="transfer in transfers" :key="transfer.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ transfer.reference_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ transfer.from_account?.account_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ transfer.to_account?.account_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-bold">{{ formatCurrency(transfer.amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ formatDate(transfer.transfer_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(transfer.status)">
                  {{ getStatusLabel(transfer.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div v-if="transfer.status === 'pending'" class="flex gap-2">
                  <button @click="approveTransfer(transfer)" class="text-green-600 hover:text-green-800">اعتماد</button>
                  <button @click="rejectTransfer(transfer)" class="text-red-600 hover:text-red-800">رفض</button>
                </div>
                <span v-else class="text-gray-400">-</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Transfer Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-lg w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">تحويل جديد</h2>
        </div>
        <form @submit.prevent="createTransfer" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">من حساب</label>
            <select v-model="form.from_account_id" required class="input-field">
              <option value="">اختر الحساب</option>
              <option v-for="account in accounts" :key="account.id" :value="account.id">
                {{ account.account_name }} ({{ formatCurrency(account.current_balance) }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">إلى حساب</label>
            <select v-model="form.to_account_id" required class="input-field">
              <option value="">اختر الحساب</option>
              <option v-for="account in accounts" :key="account.id" :value="account.id" :disabled="account.id === form.from_account_id">
                {{ account.account_name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ</label>
            <input v-model.number="form.amount" type="number" step="0.01" min="0.01" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التحويل</label>
            <input v-model="form.transfer_date" type="date" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الغرض</label>
            <input v-model="form.purpose" type="text" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea v-model="form.notes" rows="2" class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'إنشاء التحويل' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/utils/axios';

const transfers = ref([]);
const accounts = ref([]);
const showCreateModal = ref(false);
const saving = ref(false);

const filters = ref({ status: '', from_date: '', to_date: '' });

const form = ref({
  from_account_id: '',
  to_account_id: '',
  amount: 0,
  transfer_date: new Date().toISOString().split('T')[0],
  purpose: '',
  notes: '',
});

const loadTransfers = async () => {
  try {
    const params = new URLSearchParams();
    if (filters.value.status) params.append('status', filters.value.status);
    if (filters.value.from_date) params.append('from_date', filters.value.from_date);
    if (filters.value.to_date) params.append('to_date', filters.value.to_date);
    
    const response = await axios.get(`/api/treasury/transfers?${params}`);
    transfers.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading transfers:', error);
  }
};

const loadAccounts = async () => {
  try {
    const response = await axios.get('/api/treasury/bank-accounts');
    accounts.value = response.data;
  } catch (error) {
    console.error('Error loading accounts:', error);
  }
};

const createTransfer = async () => {
  saving.value = true;
  try {
    await axios.post('/api/treasury/transfers', form.value);
    showCreateModal.value = false;
    resetForm();
    await loadTransfers();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const approveTransfer = async (transfer) => {
  if (!confirm('هل أنت متأكد من اعتماد هذا التحويل؟')) return;
  try {
    await axios.post(`/api/treasury/transfers/${transfer.id}/approve`);
    await loadTransfers();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const rejectTransfer = async (transfer) => {
  const reason = prompt('سبب الرفض:');
  if (!reason) return;
  try {
    await axios.post(`/api/treasury/transfers/${transfer.id}/reject`, { reason });
    await loadTransfers();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const resetForm = () => {
  form.value = { from_account_id: '', to_account_id: '', amount: 0, transfer_date: new Date().toISOString().split('T')[0], purpose: '', notes: '' };
};

const formatCurrency = (amount) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(amount || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('ar-EG');

const getStatusLabel = (status) => {
  const labels = { pending: 'معلق', approved: 'معتمد', completed: 'مكتمل', rejected: 'مرفوض', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-100 text-gray-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadTransfers();
  loadAccounts();
});
</script>
