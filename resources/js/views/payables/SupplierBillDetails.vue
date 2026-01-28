<template>
  <div v-if="bill" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">{{ bill.bill_number }}</h2>
        <p class="text-gray-500">فاتورة المورد: {{ bill.supplier_invoice_number }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="getStatusClass(bill.status)" class="badge text-base px-4 py-2">{{ getStatusLabel(bill.status) }}</span>
        <button @click="printBill" class="btn btn-primary">طباعة</button>
        <router-link to="/dashboard/payables/bills" class="btn btn-secondary">العودة للقائمة</router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل الفاتورة</h3></div>
          <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
              <div><span class="text-gray-500">المورد:</span> <span class="mr-2 font-medium">{{ bill.supplier?.name }}</span></div>
              <div><span class="text-gray-500">المشروع:</span> <span class="mr-2">{{ bill.project?.name || '-' }}</span></div>
              <div><span class="text-gray-500">تاريخ الفاتورة:</span> <span class="mr-2">{{ formatDate(bill.bill_date) }}</span></div>
              <div><span class="text-gray-500">تاريخ الاستحقاق:</span> <span class="mr-2" :class="isOverdue ? 'text-red-600 font-bold' : ''">{{ formatDate(bill.due_date) }}</span></div>
              <div><span class="text-gray-500">شروط الدفع:</span> <span class="mr-2">{{ bill.payment_terms || '-' }}</span></div>
              <div><span class="text-gray-500">حالة الاستلام:</span> <span class="mr-2">{{ bill.goods_received ? 'تم الاستلام' : 'لم يتم الاستلام' }}</span></div>
            </div>
          </div>
        </div>

        <div class="card" id="bill-content">
          <div class="card-header"><h3 class="font-semibold">البنود</h3></div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>الوصف</th>
                  <th>الكمية</th>
                  <th>سعر الوحدة</th>
                  <th>الضريبة</th>
                  <th>الإجمالي</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in bill.items" :key="item.id">
                  <td>{{ item.description }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ item.tax_rate }}%</td>
                  <td>{{ formatCurrency(item.total_amount) }}</td>
                </tr>
              </tbody>
            </table>

            <div class="flex justify-end mt-6">
              <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span>الإجمالي الفرعي:</span><span>{{ formatCurrency(bill.subtotal) }}</span></div>
                <div v-if="bill.discount_amount > 0" class="flex justify-between text-green-600"><span>الخصم:</span><span>-{{ formatCurrency(bill.discount_amount) }}</span></div>
                <div class="flex justify-between"><span>الضريبة:</span><span>{{ formatCurrency(bill.tax_amount) }}</span></div>
                <div class="flex justify-between font-bold text-lg border-t pt-2"><span>الإجمالي:</span><span>{{ formatCurrency(bill.total_amount) }}</span></div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="bill.notes" class="card">
          <div class="card-header"><h3 class="font-semibold">ملاحظات</h3></div>
          <div class="card-body">
            <p class="text-gray-600 whitespace-pre-line">{{ bill.notes }}</p>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">ملخص الدفع</h3></div>
          <div class="card-body space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg">
              <div class="text-sm text-blue-600">إجمالي الفاتورة</div>
              <div class="text-2xl font-bold text-blue-800">{{ formatCurrency(bill.total_amount) }}</div>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
              <div class="text-sm text-green-600">المدفوع</div>
              <div class="text-xl font-bold text-green-800">{{ formatCurrency(bill.paid_amount) }}</div>
            </div>
            <div class="p-4 rounded-lg" :class="bill.balance > 0 ? 'bg-red-50' : 'bg-gray-50'">
              <div class="text-sm" :class="bill.balance > 0 ? 'text-red-600' : 'text-gray-600'">المتبقي</div>
              <div class="text-xl font-bold" :class="bill.balance > 0 ? 'text-red-800' : 'text-gray-800'">{{ formatCurrency(bill.balance) }}</div>
            </div>
          </div>
        </div>

        <div v-if="bill.approved_by" class="card">
          <div class="card-header"><h3 class="font-semibold">معلومات الاعتماد</h3></div>
          <div class="card-body space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">معتمد بواسطة:</span><span>{{ bill.approver?.name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">تاريخ الاعتماد:</span><span>{{ formatDate(bill.approved_at) }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const bill = ref(null);

const isOverdue = computed(() => bill.value && bill.value.status !== 'paid' && dayjs(bill.value.due_date).isBefore(dayjs()));

const fetchBill = async () => {
  try {
    const { data } = await axios.get(`/api/payables/bills/${route.params.id}`);
    bill.value = data.data || data;
  } catch (error) {
    console.error('Error loading bill:', error);
  }
};

const printBill = () => {
  const content = document.getElementById('bill-content').innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>فاتورة مورد - ${bill.value.bill_number}</title>
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
          * { box-sizing: border-box; margin: 0; padding: 0; }
          body { font-family: 'Cairo', Arial, sans-serif; padding: 40px; direction: rtl; }
          .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #d4af37; padding-bottom: 20px; }
          .header h1 { font-size: 24px; margin-bottom: 10px; }
          table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          th, td { border: 1px solid #ddd; padding: 10px; text-align: right; }
          th { background: #f5f5f5; }
          .totals { margin-top: 20px; text-align: left; }
          .totals div { padding: 5px 0; }
          .total-row { font-weight: bold; font-size: 18px; border-top: 2px solid #333; padding-top: 10px; }
        </style>
      </head>
      <body>
        <div class="header">
          <h1>فاتورة مورد</h1>
          <p>رقم الفاتورة: ${bill.value.bill_number}</p>
          <p>المورد: ${bill.value.supplier?.name}</p>
          <p>التاريخ: ${dayjs(bill.value.bill_date).format('YYYY/MM/DD')}</p>
        </div>
        ${content}
      </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => printWindow.print(), 500);
};

const formatDate = (d) => d ? dayjs(d).format('YYYY/MM/DD') : '-';
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);

const getStatusLabel = (status) => {
  const labels = { draft: 'مسودة', pending_approval: 'بانتظار الاعتماد', approved: 'معتمد', partially_paid: 'مدفوع جزئياً', paid: 'مدفوع', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { draft: 'badge-gray', pending_approval: 'badge-warning', approved: 'badge-info', partially_paid: 'badge-warning', paid: 'badge-success', cancelled: 'badge-danger' };
  return classes[status] || 'badge-gray';
};

onMounted(fetchBill);
</script>
