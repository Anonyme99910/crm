<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">القيود المحاسبية</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        قيد جديد
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
          <select v-model="filters.status" @change="loadEntries" class="input-field">
            <option value="">الكل</option>
            <option value="draft">مسودة</option>
            <option value="posted">مرحل</option>
            <option value="reversed">معكوس</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
          <input v-model="filters.from_date" type="date" @change="loadEntries" class="input-field">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
          <input v-model="filters.to_date" type="date" @change="loadEntries" class="input-field">
        </div>
      </div>
    </div>

    <!-- Entries List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم القيد</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الوصف</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">مدين</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">دائن</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="entry in entries" :key="entry.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ entry.entry_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ formatDate(entry.entry_date) }}</td>
              <td class="px-6 py-4">{{ entry.description }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-bold text-green-600">{{ formatCurrency(entry.total_debit) }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-bold text-red-600">{{ formatCurrency(entry.total_credit) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(entry.status)">
                  {{ getStatusLabel(entry.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <button @click="viewEntry(entry)" class="text-blue-600 hover:text-blue-800">عرض</button>
                  <button v-if="entry.status === 'draft'" @click="postEntry(entry)" class="text-green-600 hover:text-green-800">ترحيل</button>
                  <button v-if="entry.status === 'posted'" @click="reverseEntry(entry)" class="text-red-600 hover:text-red-800">عكس</button>
                  <button v-if="entry.status === 'draft'" @click="deleteEntry(entry)" class="text-red-600 hover:text-red-800">حذف</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">قيد محاسبي جديد</h2>
        </div>
        <form @submit.prevent="createEntry" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">التاريخ</label>
              <input v-model="form.entry_date" type="date" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
              <select v-model="form.type" required class="input-field">
                <option value="manual">يدوي</option>
                <option value="adjustment">تسوية</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
            <input v-model="form.description" type="text" required class="input-field">
          </div>

          <!-- Lines -->
          <div class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="font-bold">بنود القيد</h3>
              <button type="button" @click="addLine" class="text-blue-600 hover:text-blue-800 text-sm">+ إضافة بند</button>
            </div>
            <table class="min-w-full">
              <thead>
                <tr class="text-xs text-gray-500">
                  <th class="text-right pb-2">الحساب</th>
                  <th class="text-right pb-2">الوصف</th>
                  <th class="text-right pb-2">مدين</th>
                  <th class="text-right pb-2">دائن</th>
                  <th class="pb-2"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(line, index) in form.lines" :key="index" class="border-t">
                  <td class="py-2 pl-2">
                    <select v-model="line.account_id" required class="input-field text-sm">
                      <option value="">اختر الحساب</option>
                      <option v-for="acc in detailAccounts" :key="acc.id" :value="acc.id">
                        {{ acc.account_code }} - {{ acc.account_name_ar || acc.account_name }}
                      </option>
                    </select>
                  </td>
                  <td class="py-2 px-2">
                    <input v-model="line.description" type="text" class="input-field text-sm">
                  </td>
                  <td class="py-2 px-2">
                    <input v-model.number="line.debit" type="number" step="0.01" min="0" class="input-field text-sm" dir="ltr" @input="line.credit = 0">
                  </td>
                  <td class="py-2 px-2">
                    <input v-model.number="line.credit" type="number" step="0.01" min="0" class="input-field text-sm" dir="ltr" @input="line.debit = 0">
                  </td>
                  <td class="py-2">
                    <button v-if="form.lines.length > 2" type="button" @click="removeLine(index)" class="text-red-600 hover:text-red-800">×</button>
                  </td>
                </tr>
              </tbody>
              <tfoot class="border-t font-bold">
                <tr>
                  <td colspan="2" class="py-2 text-left">الإجمالي</td>
                  <td class="py-2 px-2 text-green-600">{{ formatCurrency(totalDebit) }}</td>
                  <td class="py-2 px-2 text-red-600">{{ formatCurrency(totalCredit) }}</td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
            <div v-if="!isBalanced" class="mt-2 text-red-600 text-sm">
              القيد غير متوازن! الفرق: {{ formatCurrency(Math.abs(totalDebit - totalCredit)) }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea v-model="form.notes" rows="2" class="input-field"></textarea>
          </div>

          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving || !isBalanced">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- View Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex justify-between items-center">
          <h2 class="text-xl font-bold">قيد رقم {{ selectedEntry?.entry_number }}</h2>
          <span class="px-3 py-1 rounded-full text-sm" :class="getStatusClass(selectedEntry?.status)">
            {{ getStatusLabel(selectedEntry?.status) }}
          </span>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-2 gap-4 mb-6">
            <div><span class="text-gray-500">التاريخ:</span> {{ formatDate(selectedEntry?.entry_date) }}</div>
            <div><span class="text-gray-500">النوع:</span> {{ selectedEntry?.type === 'manual' ? 'يدوي' : 'تسوية' }}</div>
          </div>
          <div class="mb-6">
            <span class="text-gray-500">الوصف:</span> {{ selectedEntry?.description }}
          </div>
          <table class="min-w-full border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الحساب</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">الوصف</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">مدين</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500">دائن</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="line in selectedEntry?.lines" :key="line.id" class="border-t">
                <td class="px-4 py-2">{{ line.account?.account_code }} - {{ line.account?.account_name_ar || line.account?.account_name }}</td>
                <td class="px-4 py-2 text-gray-500">{{ line.description }}</td>
                <td class="px-4 py-2 text-green-600">{{ line.debit > 0 ? formatCurrency(line.debit) : '' }}</td>
                <td class="px-4 py-2 text-red-600">{{ line.credit > 0 ? formatCurrency(line.credit) : '' }}</td>
              </tr>
            </tbody>
            <tfoot class="bg-gray-50 font-bold">
              <tr>
                <td colspan="2" class="px-4 py-2">الإجمالي</td>
                <td class="px-4 py-2 text-green-600">{{ formatCurrency(selectedEntry?.total_debit) }}</td>
                <td class="px-4 py-2 text-red-600">{{ formatCurrency(selectedEntry?.total_credit) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div class="p-6 border-t flex justify-end">
          <button @click="showViewModal = false" class="btn-secondary">إغلاق</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/utils/axios';

const entries = ref([]);
const accounts = ref([]);
const showCreateModal = ref(false);
const showViewModal = ref(false);
const selectedEntry = ref(null);
const saving = ref(false);

const filters = ref({ status: '', from_date: '', to_date: '' });

const form = ref({
  entry_date: new Date().toISOString().split('T')[0],
  description: '',
  type: 'manual',
  notes: '',
  lines: [
    { account_id: '', description: '', debit: 0, credit: 0 },
    { account_id: '', description: '', debit: 0, credit: 0 },
  ],
});

const detailAccounts = computed(() => accounts.value.filter(a => !a.is_header && a.is_active));
const totalDebit = computed(() => form.value.lines.reduce((sum, l) => sum + (parseFloat(l.debit) || 0), 0));
const totalCredit = computed(() => form.value.lines.reduce((sum, l) => sum + (parseFloat(l.credit) || 0), 0));
const isBalanced = computed(() => Math.abs(totalDebit.value - totalCredit.value) < 0.01 && totalDebit.value > 0);

const loadEntries = async () => {
  try {
    const params = new URLSearchParams();
    if (filters.value.status) params.append('status', filters.value.status);
    if (filters.value.from_date) params.append('from_date', filters.value.from_date);
    if (filters.value.to_date) params.append('to_date', filters.value.to_date);
    
    const response = await axios.get(`/api/accounting/journal-entries?${params}`);
    entries.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading entries:', error);
  }
};

const loadAccounts = async () => {
  try {
    const response = await axios.get('/api/accounting/accounts');
    accounts.value = response.data;
  } catch (error) {
    console.error('Error loading accounts:', error);
  }
};

const addLine = () => {
  form.value.lines.push({ account_id: '', description: '', debit: 0, credit: 0 });
};

const removeLine = (index) => {
  form.value.lines.splice(index, 1);
};

const createEntry = async () => {
  saving.value = true;
  try {
    await axios.post('/api/accounting/journal-entries', form.value);
    showCreateModal.value = false;
    resetForm();
    await loadEntries();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const viewEntry = async (entry) => {
  try {
    const response = await axios.get(`/api/accounting/journal-entries/${entry.id}`);
    selectedEntry.value = response.data;
    showViewModal.value = true;
  } catch (error) {
    console.error('Error loading entry:', error);
  }
};

const postEntry = async (entry) => {
  if (!confirm('هل أنت متأكد من ترحيل هذا القيد؟')) return;
  try {
    await axios.post(`/api/accounting/journal-entries/${entry.id}/post`);
    await loadEntries();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const reverseEntry = async (entry) => {
  const reason = prompt('سبب العكس:');
  if (!reason) return;
  try {
    await axios.post(`/api/accounting/journal-entries/${entry.id}/reverse`, {
      reversal_date: new Date().toISOString().split('T')[0],
      reason,
    });
    await loadEntries();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const deleteEntry = async (entry) => {
  if (!confirm('هل أنت متأكد من حذف هذا القيد؟')) return;
  try {
    await axios.delete(`/api/accounting/journal-entries/${entry.id}`);
    await loadEntries();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const resetForm = () => {
  form.value = {
    entry_date: new Date().toISOString().split('T')[0], description: '', type: 'manual', notes: '',
    lines: [{ account_id: '', description: '', debit: 0, credit: 0 }, { account_id: '', description: '', debit: 0, credit: 0 }],
  };
};

const formatCurrency = (amount) => new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(amount || 0);
const formatDate = (date) => date ? new Date(date).toLocaleDateString('ar-SA') : '';

const getStatusLabel = (status) => {
  const labels = { draft: 'مسودة', posted: 'مرحل', reversed: 'معكوس' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { draft: 'bg-yellow-100 text-yellow-800', posted: 'bg-green-100 text-green-800', reversed: 'bg-red-100 text-red-800' };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadEntries();
  loadAccounts();
});
</script>
