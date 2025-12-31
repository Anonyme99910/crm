<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <select v-model="filters.status" class="form-input w-40" @change="fetchInvoices">
          <option value="">كل الحالات</option>
          <option value="draft">مسودة</option>
          <option value="sent">مرسلة</option>
          <option value="paid">مدفوعة</option>
          <option value="partial">جزئية</option>
          <option value="overdue">متأخرة</option>
        </select>
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="filters.overdue" @change="fetchInvoices" class="rounded" />
          <span class="text-sm">المتأخرة فقط</span>
        </label>
      </div>
      <router-link :to="{ name: 'invoices.create' }" class="btn btn-primary">إنشاء فاتورة</router-link>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>رقم الفاتورة</th><th>العنوان</th><th>المشروع</th><th>الإجمالي</th><th>المدفوع</th><th>الحالة</th><th>الاستحقاق</th></tr>
          </thead>
          <tbody>
            <tr v-for="inv in invoices" :key="inv.id">
              <td class="font-medium">{{ inv.invoice_number }}</td>
              <td>{{ inv.title }}</td>
              <td>{{ inv.project?.name }}</td>
              <td>{{ formatCurrency(inv.total) }}</td>
              <td>{{ formatCurrency(inv.paid_amount) }}</td>
              <td><span :class="statusClass(inv.status)" class="badge">{{ statusLabel(inv.status) }}</span></td>
              <td :class="{ 'text-red-600': isOverdue(inv) }">{{ formatDate(inv.due_date) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const invoices = ref([]);
const filters = reactive({ status: '', overdue: false });

const fetchInvoices = async () => {
  const { data } = await axios.get('/invoices', { params: filters });
  invoices.value = data.data;
};

const isOverdue = (inv) => inv.status !== 'paid' && dayjs(inv.due_date).isBefore(dayjs());
const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ draft: 'مسودة', sent: 'مرسلة', paid: 'مدفوعة', partial: 'جزئية', overdue: 'متأخرة' }[s] || s);
const statusClass = (s) => ({ draft: 'badge-gray', sent: 'badge-info', paid: 'badge-success', partial: 'badge-warning', overdue: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchInvoices);
</script>
