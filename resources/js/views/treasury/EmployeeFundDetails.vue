<template>
  <div v-if="fund" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">{{ fund.fund_number }}</h2>
        <p class="text-gray-500">{{ fund.purpose }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="getStatusClass(fund.status)" class="badge text-base px-4 py-2">{{ getStatusLabel(fund.status) }}</span>
        <router-link to="/dashboard/treasury/funds" class="btn btn-secondary">العودة للقائمة</router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل العهدة</h3></div>
          <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div><span class="text-gray-500">الموظف:</span> <span class="mr-2 font-medium">{{ fund.user?.name }}</span></div>
              <div><span class="text-gray-500">النوع:</span> <span class="mr-2">{{ getTypeLabel(fund.type) }}</span></div>
              <div><span class="text-gray-500">المبلغ المطلوب:</span> <span class="mr-2">{{ formatCurrency(fund.requested_amount) }}</span></div>
              <div><span class="text-gray-500">المبلغ المعتمد:</span> <span class="mr-2 font-bold text-primary-600">{{ formatCurrency(fund.approved_amount) }}</span></div>
              <div><span class="text-gray-500">تاريخ الطلب:</span> <span class="mr-2">{{ formatDate(fund.request_date) }}</span></div>
              <div><span class="text-gray-500">تاريخ التسوية المتوقع:</span> <span class="mr-2">{{ formatDate(fund.expected_settlement_date) }}</span></div>
              <div><span class="text-gray-500">المشروع:</span> <span class="mr-2">{{ fund.project?.name || '-' }}</span></div>
            </div>
            <div v-if="fund.description" class="mt-4 p-3 bg-gray-50 rounded-lg">
              <p class="text-sm text-gray-600">{{ fund.description }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">المصروفات</h3></div>
          <div class="card-body">
            <div v-if="!fund.expenses?.length" class="text-center text-gray-500 py-8">لا توجد مصروفات مسجلة</div>
            <table v-else class="table">
              <thead>
                <tr>
                  <th>التاريخ</th>
                  <th>الفئة</th>
                  <th>الوصف</th>
                  <th>المورد</th>
                  <th>المبلغ</th>
                  <th>الحالة</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="expense in fund.expenses" :key="expense.id">
                  <td>{{ formatDate(expense.expense_date) }}</td>
                  <td>{{ getCategoryLabel(expense.category) }}</td>
                  <td>{{ expense.description }}</td>
                  <td>{{ expense.vendor_name || '-' }}</td>
                  <td class="text-red-600">{{ formatCurrency(expense.amount) }}</td>
                  <td><span :class="expense.status === 'approved' ? 'badge-success' : 'badge-warning'" class="badge">{{ expense.status === 'approved' ? 'معتمد' : 'معلق' }}</span></td>
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
              <div class="text-sm text-blue-600">المبلغ المعتمد</div>
              <div class="text-2xl font-bold text-blue-800">{{ formatCurrency(fund.approved_amount) }}</div>
            </div>
            <div class="p-4 bg-red-50 rounded-lg">
              <div class="text-sm text-red-600">المصروف</div>
              <div class="text-xl font-bold text-red-800">{{ formatCurrency(fund.spent_amount) }}</div>
            </div>
            <div class="p-4 rounded-lg" :class="fund.balance > 0 ? 'bg-green-50' : 'bg-gray-50'">
              <div class="text-sm" :class="fund.balance > 0 ? 'text-green-600' : 'text-gray-600'">الرصيد المتبقي</div>
              <div class="text-xl font-bold" :class="fund.balance > 0 ? 'text-green-800' : 'text-gray-800'">{{ formatCurrency(fund.balance) }}</div>
            </div>
          </div>
        </div>

        <div v-if="fund.approved_by" class="card">
          <div class="card-header"><h3 class="font-semibold">معلومات الاعتماد</h3></div>
          <div class="card-body space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">معتمد بواسطة:</span><span>{{ fund.approver?.name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">تاريخ الاعتماد:</span><span>{{ formatDate(fund.approved_at) }}</span></div>
            <div v-if="fund.approval_notes" class="mt-2 p-2 bg-gray-50 rounded text-gray-600">{{ fund.approval_notes }}</div>
          </div>
        </div>

        <div v-if="fund.status === 'rejected'" class="card border-red-200">
          <div class="card-header bg-red-50"><h3 class="font-semibold text-red-800">سبب الرفض</h3></div>
          <div class="card-body">
            <p class="text-red-600">{{ fund.rejection_reason }}</p>
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
const fund = ref(null);

const fetchFund = async () => {
  try {
    const { data } = await axios.get(`/api/funds/${route.params.id}`);
    fund.value = data.data || data;
  } catch (error) {
    console.error('Error loading fund:', error);
  }
};

const formatDate = (d) => d ? dayjs(d).format('YYYY/MM/DD') : '-';
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);

const getTypeLabel = (type) => {
  const labels = { advance: 'سلفة', petty_cash: 'صندوق مصروفات', operation_fund: 'عهدة تشغيلية', travel_allowance: 'بدل سفر' };
  return labels[type] || type;
};

const getStatusLabel = (status) => {
  const labels = { pending: 'معلق', approved: 'معتمد', rejected: 'مرفوض', active: 'نشط', partially_settled: 'مسوى جزئياً', settled: 'مسوى', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { pending: 'badge-warning', approved: 'badge-info', rejected: 'badge-danger', active: 'badge-success', partially_settled: 'badge-warning', settled: 'badge-gray', cancelled: 'badge-gray' };
  return classes[status] || 'badge-gray';
};

const getCategoryLabel = (cat) => {
  const labels = { materials: 'مواد', labor: 'عمالة', transport: 'نقل', food: 'طعام', accommodation: 'إقامة', other: 'أخرى' };
  return labels[cat] || cat;
};

onMounted(fetchFund);
</script>
