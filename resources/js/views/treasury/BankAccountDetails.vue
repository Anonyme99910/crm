<template>
  <div v-if="account" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">{{ account.account_name }}</h2>
        <p class="text-gray-500">{{ account.bank_name }} - {{ account.account_number }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="account.is_active ? 'badge-success' : 'badge-gray'" class="badge text-base px-4 py-2">
          {{ account.is_active ? 'نشط' : 'غير نشط' }}
        </span>
        <router-link to="/dashboard/treasury/accounts" class="btn btn-secondary">العودة للقائمة</router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل الحساب</h3></div>
          <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div><span class="text-gray-500">اسم الحساب:</span> <span class="mr-2 font-medium">{{ account.account_name }}</span></div>
              <div><span class="text-gray-500">البنك:</span> <span class="mr-2">{{ account.bank_name }}</span></div>
              <div><span class="text-gray-500">رقم الحساب:</span> <span class="mr-2 font-mono" dir="ltr">{{ account.account_number }}</span></div>
              <div><span class="text-gray-500">نوع الحساب:</span> <span class="mr-2">{{ getAccountTypeLabel(account.account_type) }}</span></div>
              <div><span class="text-gray-500">العملة:</span> <span class="mr-2">{{ account.currency }}</span></div>
              <div><span class="text-gray-500">الفرع:</span> <span class="mr-2">{{ account.branch || '-' }}</span></div>
              <div><span class="text-gray-500">رقم IBAN:</span> <span class="mr-2 font-mono" dir="ltr">{{ account.iban || '-' }}</span></div>
              <div><span class="text-gray-500">رمز SWIFT:</span> <span class="mr-2 font-mono" dir="ltr">{{ account.swift_code || '-' }}</span></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header flex justify-between items-center">
            <h3 class="font-semibold">آخر المعاملات</h3>
          </div>
          <div class="card-body">
            <div v-if="transactions.length === 0" class="text-center text-gray-500 py-8">لا توجد معاملات</div>
            <table v-else class="table">
              <thead>
                <tr>
                  <th>التاريخ</th>
                  <th>النوع</th>
                  <th>الوصف</th>
                  <th>المبلغ</th>
                  <th>الرصيد بعد</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tx in transactions" :key="tx.id">
                  <td>{{ formatDate(tx.transaction_date) }}</td>
                  <td>
                    <span :class="getTransactionTypeClass(tx.type)" class="badge">{{ getTransactionTypeLabel(tx.type) }}</span>
                  </td>
                  <td>{{ tx.description }}</td>
                  <td :class="['deposit', 'transfer_in'].includes(tx.type) ? 'text-green-600' : 'text-red-600'">
                    {{ ['deposit', 'transfer_in'].includes(tx.type) ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
                  </td>
                  <td class="font-medium">{{ formatCurrency(tx.balance_after) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">الأرصدة</h3></div>
          <div class="card-body space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg">
              <div class="text-sm text-blue-600">الرصيد الحالي</div>
              <div class="text-2xl font-bold text-blue-800">{{ formatCurrency(account.current_balance) }}</div>
            </div>
            <div class="p-4 bg-gray-50 rounded-lg">
              <div class="text-sm text-gray-600">الرصيد الافتتاحي</div>
              <div class="text-xl font-bold text-gray-800">{{ formatCurrency(account.opening_balance) }}</div>
            </div>
            <div v-if="account.minimum_balance" class="p-4 bg-yellow-50 rounded-lg">
              <div class="text-sm text-yellow-600">الحد الأدنى</div>
              <div class="text-xl font-bold text-yellow-800">{{ formatCurrency(account.minimum_balance) }}</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">ملخص الحركة</h3></div>
          <div class="card-body space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-gray-500">إجمالي الإيداعات:</span>
              <span class="font-medium text-green-600">{{ formatCurrency(summary.total_deposits) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-500">إجمالي السحوبات:</span>
              <span class="font-medium text-red-600">{{ formatCurrency(summary.total_withdrawals) }}</span>
            </div>
            <div class="flex justify-between items-center border-t pt-3">
              <span class="text-gray-500">صافي الحركة:</span>
              <span class="font-bold" :class="summary.net_movement >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ formatCurrency(summary.net_movement) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const account = ref(null);
const transactions = ref([]);
const summary = ref({ total_deposits: 0, total_withdrawals: 0, net_movement: 0 });

const fetchAccount = async () => {
  try {
    const { data } = await axios.get(`/api/treasury/bank-accounts/${route.params.id}`);
    account.value = data.data || data;
  } catch (error) {
    console.error('Error loading account:', error);
  }
};

const fetchTransactions = async () => {
  try {
    const { data } = await axios.get(`/api/treasury/bank-accounts/${route.params.id}/transactions`);
    transactions.value = data.data || data;
    
    let deposits = 0, withdrawals = 0;
    transactions.value.forEach(tx => {
      if (['deposit', 'transfer_in'].includes(tx.type)) deposits += parseFloat(tx.amount);
      else withdrawals += parseFloat(tx.amount);
    });
    summary.value = { total_deposits: deposits, total_withdrawals: withdrawals, net_movement: deposits - withdrawals };
  } catch (error) {
    console.error('Error loading transactions:', error);
  }
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);

const getAccountTypeLabel = (type) => {
  const labels = { checking: 'جاري', savings: 'توفير', cash: 'صندوق نقدي' };
  return labels[type] || type;
};

const getTransactionTypeLabel = (type) => {
  const labels = { deposit: 'إيداع', withdrawal: 'سحب', transfer_in: 'تحويل وارد', transfer_out: 'تحويل صادر', fee: 'رسوم', interest: 'فوائد' };
  return labels[type] || type;
};

const getTransactionTypeClass = (type) => {
  const classes = { deposit: 'badge-success', withdrawal: 'badge-danger', transfer_in: 'badge-info', transfer_out: 'badge-warning', fee: 'badge-gray', interest: 'badge-purple' };
  return classes[type] || 'badge-gray';
};

onMounted(() => {
  fetchAccount();
  fetchTransactions();
});
</script>
