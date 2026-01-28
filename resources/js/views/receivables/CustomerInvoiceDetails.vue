<template>
  <div v-if="invoice" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">{{ invoice.invoice_number }}</h2>
        <p class="text-gray-500">{{ getInvoiceTypeLabel(invoice.invoice_type) }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="getStatusClass(invoice.status)" class="badge text-base px-4 py-2">{{ getStatusLabel(invoice.status) }}</span>
        <button @click="printInvoice" class="btn btn-primary">طباعة</button>
        <router-link to="/dashboard/receivables/invoices" class="btn btn-secondary">العودة للقائمة</router-link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل الفاتورة</h3></div>
          <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
              <div><span class="text-gray-500">العميل:</span> <span class="mr-2 font-medium">{{ invoice.client?.name }}</span></div>
              <div><span class="text-gray-500">المشروع:</span> <span class="mr-2">{{ invoice.project?.name }}</span></div>
              <div><span class="text-gray-500">تاريخ الفاتورة:</span> <span class="mr-2">{{ formatDate(invoice.invoice_date) }}</span></div>
              <div><span class="text-gray-500">تاريخ الاستحقاق:</span> <span class="mr-2" :class="isOverdue ? 'text-red-600 font-bold' : ''">{{ formatDate(invoice.due_date) }}</span></div>
              <div><span class="text-gray-500">نسبة الإنجاز:</span> <span class="mr-2">{{ invoice.completion_percentage }}%</span></div>
              <div><span class="text-gray-500">العقد:</span> <span class="mr-2">{{ invoice.contract?.contract_number || '-' }}</span></div>
            </div>
          </div>
        </div>

        <div class="card" id="invoice-content">
          <div class="card-header"><h3 class="font-semibold">البنود</h3></div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>الوصف</th>
                  <th>الوحدة</th>
                  <th>الكمية</th>
                  <th>سعر الوحدة</th>
                  <th>الضريبة</th>
                  <th>الإجمالي</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in invoice.items" :key="item.id">
                  <td>{{ item.description }}</td>
                  <td>{{ item.unit || '-' }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ item.tax_rate }}%</td>
                  <td>{{ formatCurrency(item.total_amount) }}</td>
                </tr>
              </tbody>
            </table>

            <div class="flex justify-end mt-6">
              <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span>الإجمالي الفرعي:</span><span>{{ formatCurrency(invoice.subtotal) }}</span></div>
                <div v-if="invoice.discount_amount > 0" class="flex justify-between text-green-600"><span>الخصم:</span><span>-{{ formatCurrency(invoice.discount_amount) }}</span></div>
                <div class="flex justify-between"><span>الضريبة:</span><span>{{ formatCurrency(invoice.tax_amount) }}</span></div>
                <div v-if="invoice.retention_amount > 0" class="flex justify-between text-orange-600"><span>محتجزات:</span><span>-{{ formatCurrency(invoice.retention_amount) }}</span></div>
                <div class="flex justify-between font-bold text-lg border-t pt-2"><span>الإجمالي:</span><span>{{ formatCurrency(invoice.total_amount) }}</span></div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="invoice.payments?.length" class="card">
          <div class="card-header"><h3 class="font-semibold">المدفوعات</h3></div>
          <div class="card-body">
            <table class="table">
              <thead>
                <tr>
                  <th>رقم الدفعة</th>
                  <th>التاريخ</th>
                  <th>طريقة الدفع</th>
                  <th>المبلغ</th>
                  <th>الحالة</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="payment in invoice.payments" :key="payment.id">
                  <td class="font-mono">{{ payment.payment_number }}</td>
                  <td>{{ formatDate(payment.payment_date) }}</td>
                  <td>{{ getPaymentMethodLabel(payment.payment_method) }}</td>
                  <td class="text-green-600 font-medium">{{ formatCurrency(payment.amount) }}</td>
                  <td><span :class="payment.status === 'confirmed' ? 'badge-success' : 'badge-warning'" class="badge">{{ payment.status === 'confirmed' ? 'مؤكد' : 'معلق' }}</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">ملخص الدفع</h3></div>
          <div class="card-body space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg">
              <div class="text-sm text-blue-600">إجمالي الفاتورة</div>
              <div class="text-2xl font-bold text-blue-800">{{ formatCurrency(invoice.total_amount) }}</div>
            </div>
            <div class="p-4 bg-green-50 rounded-lg">
              <div class="text-sm text-green-600">المدفوع</div>
              <div class="text-xl font-bold text-green-800">{{ formatCurrency(invoice.paid_amount) }}</div>
            </div>
            <div class="p-4 rounded-lg" :class="invoice.balance > 0 ? 'bg-red-50' : 'bg-gray-50'">
              <div class="text-sm" :class="invoice.balance > 0 ? 'text-red-600' : 'text-gray-600'">المتبقي</div>
              <div class="text-xl font-bold" :class="invoice.balance > 0 ? 'text-red-800' : 'text-gray-800'">{{ formatCurrency(invoice.balance) }}</div>
            </div>
          </div>
        </div>

        <div v-if="invoice.approved_by" class="card">
          <div class="card-header"><h3 class="font-semibold">معلومات الاعتماد</h3></div>
          <div class="card-body space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">معتمد بواسطة:</span><span>{{ invoice.approver?.name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">تاريخ الاعتماد:</span><span>{{ formatDate(invoice.approved_at) }}</span></div>
          </div>
        </div>

        <div v-if="invoice.notes" class="card">
          <div class="card-header"><h3 class="font-semibold">ملاحظات</h3></div>
          <div class="card-body">
            <p class="text-gray-600 whitespace-pre-line">{{ invoice.notes }}</p>
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
const invoice = ref(null);

const isOverdue = computed(() => invoice.value && !['paid', 'cancelled'].includes(invoice.value.status) && dayjs(invoice.value.due_date).isBefore(dayjs()));

const fetchInvoice = async () => {
  try {
    const { data } = await axios.get(`/api/receivables/invoices/${route.params.id}`);
    invoice.value = data.data || data;
  } catch (error) {
    console.error('Error loading invoice:', error);
  }
};

const printInvoice = () => {
  const content = document.getElementById('invoice-content').innerHTML;
  const printWindow = window.open('', '_blank');
  printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
      <head>
        <meta charset="UTF-8">
        <title>فاتورة عميل - ${invoice.value.invoice_number}</title>
        <style>
          @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap');
          * { box-sizing: border-box; margin: 0; padding: 0; }
          body { font-family: 'Cairo', Arial, sans-serif; padding: 40px; direction: rtl; line-height: 1.6; }
          .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #d4af37; padding-bottom: 20px; }
          .header h1 { font-size: 28px; margin-bottom: 10px; color: #1f2937; }
          .header .company { font-size: 18px; color: #d4af37; font-weight: 600; }
          .info { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
          .info-box { background: #f9fafb; padding: 15px; border-radius: 8px; }
          .info-box h3 { font-size: 14px; color: #6b7280; margin-bottom: 5px; }
          .info-box p { font-size: 16px; font-weight: 600; }
          table { width: 100%; border-collapse: collapse; margin: 20px 0; }
          th, td { border: 1px solid #e5e7eb; padding: 12px; text-align: right; }
          th { background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); font-weight: 600; }
          tr:nth-child(even) { background: #f9fafb; }
          .totals { margin-top: 20px; display: flex; justify-content: flex-end; }
          .totals-box { width: 280px; }
          .totals-row { display: flex; justify-content: space-between; padding: 8px 0; }
          .totals-row.total { font-weight: bold; font-size: 18px; border-top: 2px solid #333; padding-top: 12px; margin-top: 8px; }
          .footer { margin-top: 40px; text-align: center; color: #9ca3af; font-size: 12px; border-top: 1px solid #e5e7eb; padding-top: 20px; }
        </style>
      </head>
      <body>
        <div class="header">
          <div class="company">حازم عبدالله للتشطيبات</div>
          <h1>فاتورة عميل</h1>
        </div>
        <div class="info">
          <div class="info-box">
            <h3>رقم الفاتورة</h3>
            <p>${invoice.value.invoice_number}</p>
          </div>
          <div class="info-box">
            <h3>العميل</h3>
            <p>${invoice.value.client?.name || '-'}</p>
          </div>
          <div class="info-box">
            <h3>تاريخ الفاتورة</h3>
            <p>${dayjs(invoice.value.invoice_date).format('YYYY/MM/DD')}</p>
          </div>
          <div class="info-box">
            <h3>تاريخ الاستحقاق</h3>
            <p>${dayjs(invoice.value.due_date).format('YYYY/MM/DD')}</p>
          </div>
        </div>
        ${content}
        <div class="footer">
          تم إنشاء هذه الفاتورة بتاريخ ${dayjs().format('YYYY/MM/DD')}
        </div>
      </body>
    </html>
  `);
  printWindow.document.close();
  setTimeout(() => printWindow.print(), 500);
};

const formatDate = (d) => d ? dayjs(d).format('YYYY/MM/DD') : '-';
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);

const getInvoiceTypeLabel = (type) => {
  const labels = { progress: 'مستخلص', final: 'نهائي', retention: 'محتجزات', variation: 'أعمال إضافية', advance: 'دفعة مقدمة' };
  return labels[type] || type;
};

const getStatusLabel = (status) => {
  const labels = { draft: 'مسودة', pending_approval: 'بانتظار الاعتماد', approved: 'معتمد', sent: 'مرسل', partially_paid: 'مدفوع جزئياً', paid: 'مدفوع', overdue: 'متأخر', cancelled: 'ملغي' };
  return labels[status] || status;
};

const getStatusClass = (status) => {
  const classes = { draft: 'badge-gray', pending_approval: 'badge-warning', approved: 'badge-info', sent: 'badge-info', partially_paid: 'badge-warning', paid: 'badge-success', overdue: 'badge-danger', cancelled: 'badge-gray' };
  return classes[status] || 'badge-gray';
};

const getPaymentMethodLabel = (method) => {
  const labels = { bank_transfer: 'تحويل بنكي', check: 'شيك', cash: 'نقدي', credit_card: 'بطاقة ائتمان' };
  return labels[method] || method;
};

onMounted(fetchInvoice);
</script>
