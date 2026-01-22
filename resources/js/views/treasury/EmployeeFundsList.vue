<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">سلف وعهد الموظفين</h1>
      <button @click="showCreateModal = true" class="btn-primary">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        طلب عهدة جديدة
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">إجمالي العهد النشطة</div>
        <div class="text-2xl font-bold text-blue-600">{{ formatCurrency(summary.total_active_funds) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">طلبات معلقة</div>
        <div class="text-2xl font-bold text-yellow-600">{{ summary.pending_requests }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">مبالغ معلقة</div>
        <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(summary.pending_amount) }}</div>
      </div>
      <div class="bg-white rounded-lg shadow p-4">
        <div class="text-sm text-gray-500">تسويات متأخرة</div>
        <div class="text-2xl font-bold text-red-600">{{ summary.overdue_settlements }}</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
          <select v-model="filters.status" @change="loadFunds" class="input-field">
            <option value="">الكل</option>
            <option value="pending">معلق</option>
            <option value="active">نشط</option>
            <option value="partially_settled">مسوى جزئياً</option>
            <option value="settled">مسوى</option>
            <option value="rejected">مرفوض</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">النوع</label>
          <select v-model="filters.type" @change="loadFunds" class="input-field">
            <option value="">الكل</option>
            <option value="advance">سلفة</option>
            <option value="petty_cash">صندوق مصروفات</option>
            <option value="operation_fund">عهدة تشغيلية</option>
            <option value="travel_allowance">بدل سفر</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">الموظف</label>
          <select v-model="filters.user_id" @change="loadFunds" class="input-field">
            <option value="">الكل</option>
            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Funds List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">رقم العهدة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الموظف</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النوع</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المبلغ المعتمد</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المصروف</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الرصيد</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراءات</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="fund in funds" :key="fund.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap font-mono text-sm">{{ fund.fund_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ fund.user?.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getTypeClass(fund.type)">
                  {{ getTypeLabel(fund.type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">{{ formatCurrency(fund.approved_amount || fund.requested_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-red-600">{{ formatCurrency(fund.spent_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap font-bold" :class="fund.balance > 0 ? 'text-green-600' : 'text-gray-500'">
                {{ formatCurrency(fund.balance) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 py-1 text-xs rounded-full" :class="getStatusClass(fund.status)">
                  {{ getStatusLabel(fund.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex gap-2">
                  <button @click="viewFund(fund)" class="text-blue-600 hover:text-blue-800">عرض</button>
                  <template v-if="fund.status === 'pending'">
                    <button @click="openApproveModal(fund)" class="text-green-600 hover:text-green-800">اعتماد</button>
                    <button @click="rejectFund(fund)" class="text-red-600 hover:text-red-800">رفض</button>
                  </template>
                  <template v-if="['active', 'partially_settled'].includes(fund.status)">
                    <button @click="openExpenseModal(fund)" class="text-purple-600 hover:text-purple-800">إضافة مصروف</button>
                    <button @click="openSettleModal(fund)" class="text-orange-600 hover:text-orange-800">تسوية</button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Fund Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">طلب عهدة جديدة</h2>
        </div>
        <form @submit.prevent="createFund" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الموظف</label>
            <select v-model="form.user_id" required class="input-field">
              <option value="">اختر الموظف</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">نوع العهدة</label>
            <select v-model="form.type" required class="input-field">
              <option value="advance">سلفة</option>
              <option value="petty_cash">صندوق مصروفات</option>
              <option value="operation_fund">عهدة تشغيلية</option>
              <option value="travel_allowance">بدل سفر</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ المطلوب</label>
            <input v-model.number="form.requested_amount" type="number" step="0.01" min="0.01" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الغرض</label>
            <input v-model="form.purpose" type="text" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المشروع (اختياري)</label>
            <select v-model="form.project_id" class="input-field">
              <option value="">بدون مشروع</option>
              <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الطلب</label>
              <input v-model="form.request_date" type="date" required class="input-field">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ التسوية المتوقع</label>
              <input v-model="form.expected_settlement_date" type="date" class="input-field">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">وصف</label>
            <textarea v-model="form.description" rows="2" class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showCreateModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'إرسال الطلب' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Approve Modal -->
    <div v-if="showApproveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">اعتماد العهدة</h2>
        </div>
        <form @submit.prevent="approveFund" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">المبلغ المطلوب: <span class="font-bold">{{ formatCurrency(selectedFund?.requested_amount) }}</span></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ المعتمد</label>
            <input v-model.number="approveForm.approved_amount" type="number" step="0.01" min="0.01" :max="selectedFund?.requested_amount" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الحساب البنكي</label>
            <select v-model="approveForm.bank_account_id" required class="input-field">
              <option value="">اختر الحساب</option>
              <option v-for="account in bankAccounts" :key="account.id" :value="account.id">
                {{ account.account_name }} ({{ formatCurrency(account.current_balance) }})
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea v-model="approveForm.notes" rows="2" class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showApproveModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الاعتماد...' : 'اعتماد' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Expense Modal -->
    <div v-if="showExpenseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">إضافة مصروف</h2>
        </div>
        <form @submit.prevent="addExpense" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <p class="text-sm text-gray-600">الرصيد المتاح: <span class="font-bold text-green-600">{{ formatCurrency(selectedFund?.balance) }}</span></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ</label>
            <input v-model.number="expenseForm.amount" type="number" step="0.01" min="0.01" :max="selectedFund?.balance" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">التصنيف</label>
            <select v-model="expenseForm.category" required class="input-field">
              <option value="materials">مواد</option>
              <option value="transportation">نقل</option>
              <option value="food">طعام</option>
              <option value="supplies">مستلزمات</option>
              <option value="other">أخرى</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
            <input v-model="expenseForm.description" type="text" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ المصروف</label>
            <input v-model="expenseForm.expense_date" type="date" required class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">اسم المورد</label>
            <input v-model="expenseForm.vendor_name" type="text" class="input-field">
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showExpenseModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري الحفظ...' : 'إضافة' }}</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Settle Modal -->
    <div v-if="showSettleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">تسوية العهدة</h2>
        </div>
        <form @submit.prevent="settleFund" class="p-6 space-y-4">
          <div class="bg-gray-50 p-4 rounded-lg space-y-2">
            <p class="text-sm text-gray-600">المبلغ المعتمد: <span class="font-bold">{{ formatCurrency(selectedFund?.approved_amount) }}</span></p>
            <p class="text-sm text-gray-600">المصروف: <span class="font-bold text-red-600">{{ formatCurrency(selectedFund?.spent_amount) }}</span></p>
            <p class="text-sm text-gray-600">الرصيد: <span class="font-bold text-green-600">{{ formatCurrency(selectedFund?.balance) }}</span></p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المبلغ المرتجع</label>
            <input v-model.number="settleForm.returned_amount" type="number" step="0.01" min="0" :max="selectedFund?.balance" required class="input-field" dir="ltr">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">نوع التسوية</label>
            <select v-model="settleForm.settlement_type" required class="input-field">
              <option value="full">تسوية كاملة</option>
              <option value="partial">تسوية جزئية</option>
              <option value="roll_forward">ترحيل للفترة القادمة</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea v-model="settleForm.notes" rows="2" class="input-field"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="showSettleModal = false" class="btn-secondary">إلغاء</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'جاري التسوية...' : 'تسوية' }}</button>
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
const funds = ref([]);
const users = ref([]);
const projects = ref([]);
const bankAccounts = ref([]);
const summary = ref({ total_active_funds: 0, pending_requests: 0, pending_amount: 0, overdue_settlements: 0 });
const selectedFund = ref(null);
const saving = ref(false);

const showCreateModal = ref(false);
const showApproveModal = ref(false);
const showExpenseModal = ref(false);
const showSettleModal = ref(false);

const filters = ref({ status: '', type: '', user_id: '' });

const form = ref({
  user_id: '', type: 'operation_fund', requested_amount: 0, purpose: '', project_id: '',
  request_date: new Date().toISOString().split('T')[0], expected_settlement_date: '', description: '',
});

const approveForm = ref({ approved_amount: 0, bank_account_id: '', notes: '' });
const expenseForm = ref({ amount: 0, category: 'materials', description: '', expense_date: new Date().toISOString().split('T')[0], vendor_name: '' });
const settleForm = ref({ returned_amount: 0, settlement_type: 'full', notes: '' });

const loadFunds = async () => {
  try {
    const params = new URLSearchParams();
    if (filters.value.status) params.append('status', filters.value.status);
    if (filters.value.type) params.append('type', filters.value.type);
    if (filters.value.user_id) params.append('user_id', filters.value.user_id);
    
    const [fundsRes, summaryRes] = await Promise.all([
      axios.get(`/api/funds?${params}`),
      axios.get('/api/funds/summary'),
    ]);
    funds.value = fundsRes.data.data || fundsRes.data;
    summary.value = summaryRes.data;
  } catch (error) {
    console.error('Error loading funds:', error);
  }
};

const loadData = async () => {
  try {
    const [usersRes, projectsRes, accountsRes] = await Promise.all([
      axios.get('/api/users'),
      axios.get('/api/projects'),
      axios.get('/api/treasury/bank-accounts'),
    ]);
    users.value = usersRes.data;
    projects.value = projectsRes.data;
    bankAccounts.value = accountsRes.data;
  } catch (error) {
    console.error('Error loading data:', error);
  }
};

const createFund = async () => {
  saving.value = true;
  try {
    await axios.post('/api/funds', form.value);
    showCreateModal.value = false;
    await loadFunds();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const openApproveModal = (fund) => {
  selectedFund.value = fund;
  approveForm.value = { approved_amount: fund.requested_amount, bank_account_id: '', notes: '' };
  showApproveModal.value = true;
};

const approveFund = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/funds/${selectedFund.value.id}/approve`, approveForm.value);
    showApproveModal.value = false;
    await loadFunds();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const rejectFund = async (fund) => {
  const reason = prompt('سبب الرفض:');
  if (!reason) return;
  try {
    await axios.post(`/api/funds/${fund.id}/reject`, { rejection_reason: reason });
    await loadFunds();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  }
};

const openExpenseModal = (fund) => {
  selectedFund.value = fund;
  expenseForm.value = { amount: 0, category: 'materials', description: '', expense_date: new Date().toISOString().split('T')[0], vendor_name: '' };
  showExpenseModal.value = true;
};

const addExpense = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/funds/${selectedFund.value.id}/expenses`, expenseForm.value);
    showExpenseModal.value = false;
    await loadFunds();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const openSettleModal = (fund) => {
  selectedFund.value = fund;
  settleForm.value = { returned_amount: fund.balance, settlement_type: 'full', notes: '' };
  showSettleModal.value = true;
};

const settleFund = async () => {
  saving.value = true;
  try {
    await axios.post(`/api/funds/${selectedFund.value.id}/settle`, settleForm.value);
    showSettleModal.value = false;
    await loadFunds();
  } catch (error) {
    alert(error.response?.data?.message || 'حدث خطأ');
  } finally {
    saving.value = false;
  }
};

const viewFund = (fund) => router.push(`/dashboard/treasury/funds/${fund.id}`);

const formatCurrency = (amount) => new Intl.NumberFormat('ar-SA', { style: 'currency', currency: 'SAR' }).format(amount || 0);

const getTypeLabel = (type) => {
  const labels = { advance: 'سلفة', petty_cash: 'صندوق مصروفات', operation_fund: 'عهدة تشغيلية', travel_allowance: 'بدل سفر' };
  return labels[type] || type;
};

const getTypeClass = (type) => {
  const classes = { advance: 'bg-blue-100 text-blue-800', petty_cash: 'bg-purple-100 text-purple-800', operation_fund: 'bg-green-100 text-green-800', travel_allowance: 'bg-yellow-100 text-yellow-800' };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
  const labels = { pending: 'معلق', approved: 'معتمد', rejected: 'مرفوض', active: 'نشط', partially_settled: 'مسوى جزئياً', settled: 'مسوى', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { pending: 'bg-yellow-100 text-yellow-800', approved: 'bg-blue-100 text-blue-800', rejected: 'bg-red-100 text-red-800', active: 'bg-green-100 text-green-800', partially_settled: 'bg-orange-100 text-orange-800', settled: 'bg-gray-100 text-gray-800', cancelled: 'bg-gray-100 text-gray-800' };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

onMounted(() => {
  loadFunds();
  loadData();
});
</script>
