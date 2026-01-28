<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">دليل الحسابات</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة حساب
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">نوع الحساب</label>
          <select v-model="filters.type" @change="loadAccounts" class="input-field">
            <option value="">الكل</option>
            <option value="asset">أصول</option>
            <option value="liability">التزامات</option>
            <option value="equity">حقوق ملكية</option>
            <option value="revenue">إيرادات</option>
            <option value="expense">مصروفات</option>
          </select>
        </div>
        <div class="flex items-end">
          <label class="flex items-center">
            <input v-model="filters.active_only" type="checkbox" @change="loadAccounts" class="ml-2">
            <span class="text-sm">الحسابات النشطة فقط</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Accounts Tree -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رمز الحساب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم الحساب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الاسم بالعربي</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد الطبيعي</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد الحالي</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="account in accounts" :key="account.id" class="hover:bg-gray-50" :class="{ 'bg-gray-50 font-bold': account.is_header }">
              <td class="px-6 py-4 whitespace-nowrap font-mono" :style="{ paddingRight: (account.level * 20) + 24 + 'px' }">
                {{ account.account_code }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account.account_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account.account_name_ar }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getTypeClass(account.account_type)">
                  {{ getTypeLabel(account.account_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                {{ account.normal_balance === 'debit' ? 'مدين' : 'دائن' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap font-bold" :class="account.current_balance >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ account.is_header ? '-' : formatCurrency(account.current_balance) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div v-if="!account.is_system" class="flex gap-2">
                  <button @click="editAccount(account)" class="text-blue-600 hover:text-blue-800">تعديل</button>
                </div>
                <span v-else class="text-gray-400">حساب نظام</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">{{ editingAccount ? 'تعديل حساب' : 'إضافة حساب جديد' }}</h2>
        </div>
        <form @submit.prevent="saveAccount" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">رمز الحساب</label>
              <input v-model="form.account_code" type="text" required :disabled="editingAccount" class="input-field" dir="ltr">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">نوع الحساب</label>
              <select v-model="form.account_type" required :disabled="editingAccount" class="input-field">
                <option value="asset">أصول</option>
                <option value="liability">التزامات</option>
                <option value="equity">حقوق ملكية</option>
                <option value="revenue">إيرادات</option>
                <option value="expense">مصروفات</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب (إنجليزي)</label>
            <input v-model="form.account_name" type="text" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب (عربي)</label>
            <input v-model="form.account_name_ar" type="text" class="input-field">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الرصيد الطبيعي</label>
              <select v-model="form.normal_balance" required :disabled="editingAccount" class="input-field">
                <option value="debit">مدين</option>
                <option value="credit">دائن</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الحساب الأب</label>
              <select v-model="form.parent_id" class="input-field">
                <option value="">بدون</option>
                <option v-for="acc in headerAccounts" :key="acc.id" :value="acc.id">
                  {{ acc.account_code }} - {{ acc.account_name_ar || acc.account_name }}
                </option>
              </select>
            </div>
          </div>
          <div v-if="!editingAccount">
            <label class="block text-sm font-medium text-gray-700 mb-1">الرصيد الافتتاحي</label>
            <input v-model.number="form.opening_balance" type="number" step="0.01" class="input-field" dir="ltr">
          </div>
          <div class="flex items-center gap-4">
            <label class="flex items-center">
              <input v-model="form.is_header" type="checkbox" class="ml-2">
              <span class="text-sm">حساب رئيسي (Header)</span>
            </label>
            <label class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="ml-2">
              <span class="text-sm">نشط</span>
            </label>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
            <textarea v-model="form.description" rows="2" class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="closeModal" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/utils/axios';

const accounts = ref([]);
const showCreateModal = ref(false);
const editingAccount = ref(null);
const saving = ref(false);

const filters = ref({ type: '', active_only: true });

const form = ref({
  account_code: '', account_name: '', account_name_ar: '', account_type: 'asset',
  normal_balance: 'debit', parent_id: '', is_header: false, is_active: true, opening_balance: 0, description: '',
});

const headerAccounts = computed(() => accounts.value.filter(a => a.is_header));

const loadAccounts = async () => {
  try {
    const params = new URLSearchParams();
    if (filters.value.type) params.append('type', filters.value.type);
    params.append('active_only', filters.value.active_only);
    
    const response = await axios.get(`/api/accounting/accounts?${params}`);
    accounts.value = response.data;
  } catch (error) {
    console.error('Error loading accounts:', error);
  }
};

const saveAccount = async () => {
  saving.value = true;
  try {
    if (editingAccount.value) {
      await axios.put(`/api/accounting/accounts/${editingAccount.value.id}`, form.value);
    } else {
      await axios.post('/api/accounting/accounts', form.value);
    }
    closeModal();
    await loadAccounts();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const editAccount = (account) => {
  editingAccount.value = account;
  form.value = { ...account, parent_id: account.parent_id || '' };
  showCreateModal.value = true;
};

const closeModal = () => {
  showCreateModal.value = false;
  editingAccount.value = null;
  form.value = {
    account_code: '', account_name: '', account_name_ar: '', account_type: 'asset',
    normal_balance: 'debit', parent_id: '', is_header: false, is_active: true, opening_balance: 0, description: '',
  };
};

const formatCurrency = (amount) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(amount || 0);

const getTypeLabel = (type) => {
  const labels = { asset: 'أصول', liability: 'التزامات', equity: 'حقوق ملكية', revenue: 'إيرادات', expense: 'مصروفات' };
  return labels[type] || type;
};

const getTypeClass = (type) => {
  const classes = {
    asset: 'bg-blue-100 text-blue-800', liability: 'bg-red-100 text-red-800', equity: 'bg-purple-100 text-purple-800',
    revenue: 'bg-green-100 text-green-800', expense: 'bg-orange-100 text-orange-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

onMounted(loadAccounts);
</script>
