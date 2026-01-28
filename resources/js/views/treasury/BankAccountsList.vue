<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">إدارة الخزينة</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة حساب بنكي
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">إجمالي الأرصدة</div>
        <div class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.total_balance) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">عدد الحسابات</div>
        <div class="text-2xl font-bold text-blue-600">{{ summary.accounts_count }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">حسابات منخفضة الرصيد</div>
        <div class="text-2xl font-bold text-red-600">{{ summary.low_balance_accounts }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">التحويلات المعلقة</div>
        <div class="text-2xl font-bold text-yellow-600">{{ pendingTransfers }}</div>
      </div>
    </div>

    <!-- Accounts List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">اسم الحساب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البنك</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم الحساب</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد الحالي</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="account in accounts" :key="account.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="font-medium text-gray-900">{{ account.account_name }}</div>
                <div v-if="account.is_default" class="text-xs text-green-600">الحساب الافتراضي</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ account.bank_name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-gray-500 font-mono">{{ account.account_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getTypeClass(account.account_type)">
                  {{ getTypeLabel(account.account_type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="account.current_balance < account.minimum_balance ? 'text-red-600' : 'text-green-600'" class="font-bold">
                  {{ formatCurrency(account.current_balance) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="account.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs rounded-full">
                  {{ account.is_active ? 'نشط' : 'غير نشط' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <button @click="viewAccount(account)" class="text-blue-600 hover:text-blue-800">عرض</button>
                  <button @click="openDeposit(account)" class="text-green-600 hover:text-green-800">إيداع</button>
                  <button @click="openWithdraw(account)" class="text-red-600 hover:text-red-800">سحب</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Account Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">إضافة حساب بنكي جديد</h2>
        </div>
        <form @submit.prevent="createAccount" class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">اسم الحساب</label>
              <input v-model="form.account_name" type="text" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">اسم البنك</label>
              <input v-model="form.bank_name" type="text" required class="input-field">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">رقم الحساب</label>
              <input v-model="form.account_number" type="text" required class="input-field" dir="ltr">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">نوع الحساب</label>
              <select v-model="form.account_type" required class="input-field">
                <option value="checking">جاري</option>
                <option value="savings">توفير</option>
                <option value="cash">نقدي</option>
                <option value="petty_cash">صندوق مصروفات</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الرصيد الافتتاحي</label>
              <input v-model.number="form.opening_balance" type="number" step="0.01" required class="input-field" dir="ltr">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الحد الأدنى للرصيد</label>
              <input v-model.number="form.minimum_balance" type="number" step="0.01" class="input-field" dir="ltr">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">العملة</label>
              <select v-model="form.currency" required class="input-field">
                <option value="EGP">جنيه مصري</option>
                <option value="USD">دولار أمريكي</option>
                <option value="EUR">يورو</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">الفرع</label>
              <input v-model="form.branch" type="text" class="input-field">
            </div>
          </div>
          <div class="flex items-center gap-4">
            <label class="flex items-center">
              <input v-model="form.is_active" type="checkbox" class="ml-2">
              <span class="text-sm">نشط</span>
            </label>
            <label class="flex items-center">
              <input v-model="form.is_default" type="checkbox" class="ml-2">
              <span class="text-sm">الحساب الافتراضي</span>
            </label>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'حفظ' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Deposit/Withdraw Modal -->
    <div v-if="showTransactionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">{{ transactionType === 'deposit' ? 'إيداع' : 'سحب' }} - {{ selectedAccount?.account_name }}</h2>
        </div>
        <form @submit.prevent="submitTransaction" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ</label>
            <input v-model.number="transactionForm.amount" type="number" step="0.01" min="0.01" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
            <input v-model="transactionForm.description" type="text" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">التاريخ</label>
            <input v-model="transactionForm.transaction_date" type="date" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">رقم المرجع</label>
            <input v-model="transactionForm.reference_number" type="text" class="input-field" dir="ltr">
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showTransactionModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري التنفيذ...' : 'تنفيذ' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/utils/axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const accounts = ref([]);
const summary = ref({ total_balance: 0, accounts_count: 0, low_balance_accounts: 0 });
const pendingTransfers = ref(0);
const showCreateModal = ref(false);
const showTransactionModal = ref(false);
const transactionType = ref('deposit');
const selectedAccount = ref(null);
const saving = ref(false);

const form = ref({
  account_name: '',
  bank_name: '',
  account_number: '',
  account_type: 'checking',
  currency: 'EGP',
  opening_balance: 0,
  minimum_balance: 0,
  branch: '',
  is_active: true,
  is_default: false,
});

const transactionForm = ref({
  amount: 0,
  description: '',
  transaction_date: new Date().toISOString().split('T')[0],
  reference_number: '',
});

const loadAccounts = async () => {
  try {
    const [accountsRes, summaryRes] = await Promise.all([
      axios.get('/api/treasury/bank-accounts'),
      axios.get('/api/treasury/bank-accounts/summary'),
    ]);
    accounts.value = accountsRes.data;
    summary.value = summaryRes.data;
  } catch (error) {
    console.error('Error loading accounts:', error);
  }
};

const createAccount = async () => {
  saving.value = true;
  try {
    await axios.post('/api/treasury/bank-accounts', form.value);
    showCreateModal.value = false;
    resetForm();
    await loadAccounts();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const openDeposit = (account) => {
  selectedAccount.value = account;
  transactionType.value = 'deposit';
  transactionForm.value = { amount: 0, description: '', transaction_date: new Date().toISOString().split('T')[0], reference_number: '' };
  showTransactionModal.value = true;
};

const openWithdraw = (account) => {
  selectedAccount.value = account;
  transactionType.value = 'withdraw';
  transactionForm.value = { amount: 0, description: '', transaction_date: new Date().toISOString().split('T')[0], reference_number: '' };
  showTransactionModal.value = true;
};

const submitTransaction = async () => {
  saving.value = true;
  try {
    const endpoint = transactionType.value === 'deposit' ? 'deposit' : 'withdraw';
    await axios.post(`/api/treasury/bank-accounts/${selectedAccount.value.id}/${endpoint}`, transactionForm.value);
    showTransactionModal.value = false;
    await loadAccounts();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const viewAccount = (account) => {
  router.push(`/dashboard/treasury/accounts/${account.id}`);
};

const resetForm = () => {
  form.value = {
    account_name: '', bank_name: '', account_number: '', account_type: 'checking',
    currency: 'EGP', opening_balance: 0, minimum_balance: 0, branch: '', is_active: true, is_default: false,
  };
};

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(amount || 0);
};

const getTypeLabel = (type) => {
  const labels = { checking: 'جاري', savings: 'توفير', cash: 'نقدي', petty_cash: 'صندوق مصروفات' };
  return labels[type] || type;
};

const getTypeClass = (type) => {
  const classes = {
    checking: 'bg-blue-100 text-blue-800',
    savings: 'bg-green-100 text-green-800',
    cash: 'bg-yellow-100 text-yellow-800',
    petty_cash: 'bg-purple-100 text-purple-800',
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

onMounted(loadAccounts);
</script>
