<template>
  <div v-if="quotation" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">{{ quotation.quotation_number }}</h2>
        <p class="text-gray-500">{{ quotation.title }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="statusClass(quotation.status)" class="badge text-base px-4 py-2">{{ statusLabel(quotation.status) }}</span>
        <router-link :to="`/quotations/${quotation.id}/edit`" class="btn btn-secondary">تعديل</router-link>
        <button @click="generatePdf" class="btn btn-primary">تحميل PDF</button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل العرض</h3></div>
          <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
              <div><span class="text-gray-500">العميل:</span> <span class="mr-2 font-medium">{{ quotation.lead?.name }}</span></div>
              <div><span class="text-gray-500">الهاتف:</span> <span class="mr-2" dir="ltr">{{ quotation.lead?.phone }}</span></div>
              <div><span class="text-gray-500">تاريخ الإنشاء:</span> <span class="mr-2">{{ formatDate(quotation.created_at) }}</span></div>
              <div><span class="text-gray-500">صالح حتى:</span> <span class="mr-2">{{ formatDate(quotation.valid_until) }}</span></div>
            </div>

            <table class="table">
              <thead>
                <tr>
                  <th>البند</th>
                  <th>الوحدة</th>
                  <th>الكمية</th>
                  <th>سعر الوحدة</th>
                  <th>الإجمالي</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in quotation.items" :key="item.id">
                  <td>{{ item.name }}</td>
                  <td>{{ item.unit }}</td>
                  <td>{{ item.quantity }}</td>
                  <td>{{ formatCurrency(item.unit_price) }}</td>
                  <td>{{ formatCurrency(item.total) }}</td>
                </tr>
              </tbody>
            </table>

            <div class="flex justify-end mt-6">
              <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between"><span>الإجمالي الفرعي:</span><span>{{ formatCurrency(quotation.subtotal) }}</span></div>
                <div v-if="quotation.discount_amount > 0" class="flex justify-between text-red-600"><span>الخصم:</span><span>-{{ formatCurrency(quotation.discount_amount) }}</span></div>
                <div v-if="quotation.tax_amount > 0" class="flex justify-between"><span>الضريبة:</span><span>{{ formatCurrency(quotation.tax_amount) }}</span></div>
                <div class="flex justify-between font-bold text-lg border-t pt-2"><span>الإجمالي:</span><span>{{ formatCurrency(quotation.total) }}</span></div>
              </div>
            </div>

            <div v-if="quotation.terms_conditions" class="mt-6 p-4 bg-gray-50 rounded-lg">
              <h4 class="font-semibold mb-2">الشروط والأحكام</h4>
              <p class="text-sm text-gray-600 whitespace-pre-line">{{ quotation.terms_conditions }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">إجراءات</h3></div>
          <div class="card-body space-y-3">
            <button v-if="quotation.status === 'draft'" @click="sendQuotation" class="btn btn-primary w-full">إرسال للعميل</button>
            <button v-if="quotation.status === 'sent' || quotation.status === 'viewed'" @click="updateStatus('accepted')" class="btn btn-success w-full">تحديد كمقبول</button>
            <button v-if="quotation.status === 'sent' || quotation.status === 'viewed'" @click="updateStatus('rejected')" class="btn btn-danger w-full">تحديد كمرفوض</button>
            <button @click="duplicateQuotation" class="btn btn-secondary w-full">نسخ العرض</button>
            <router-link v-if="quotation.status === 'accepted'" :to="`/projects/create?quotation_id=${quotation.id}&lead_id=${quotation.lead_id}`" class="btn btn-primary w-full block text-center">إنشاء مشروع</router-link>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">معلومات الربح</h3></div>
          <div class="card-body space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">التكلفة:</span><span>{{ formatCurrency(quotation.cost_price) }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">هامش الربح:</span><span class="text-green-600 font-medium">{{ formatCurrency(quotation.profit_margin) }}</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const router = useRouter();
const quotation = ref(null);

const fetchQuotation = async () => {
  const { data } = await axios.get(`/quotations/${route.params.id}`);
  quotation.value = data.data;
};

const sendQuotation = async () => {
  await axios.post(`/quotations/${quotation.value.id}/send`);
  fetchQuotation();
};

const updateStatus = async (status) => {
  await axios.put(`/quotations/${quotation.value.id}`, { status });
  fetchQuotation();
};

const generatePdf = async () => {
  const { data } = await axios.post(`/quotations/${quotation.value.id}/pdf`);
  window.open(data.data.pdf_url, '_blank');
};

const duplicateQuotation = async () => {
  const { data } = await axios.post(`/quotations/${quotation.value.id}/duplicate`);
  router.push(`/quotations/${data.data.id}/edit`);
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v || 0);
const statusLabel = (s) => ({ draft: 'مسودة', sent: 'مرسل', viewed: 'تم العرض', accepted: 'مقبول', rejected: 'مرفوض' }[s] || s);
const statusClass = (s) => ({ draft: 'badge-gray', sent: 'badge-info', viewed: 'badge-warning', accepted: 'badge-success', rejected: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchQuotation);
</script>
