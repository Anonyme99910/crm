<template>
  <div v-if="contract" class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm text-gray-500">{{ contract.contract_number }}</p>
        <h2 class="text-2xl font-bold">{{ contract.title }}</h2>
      </div>
      <div class="flex items-center gap-3">
        <span :class="statusClass(contract.status)" class="badge text-base px-4 py-2">{{ statusLabel(contract.status) }}</span>
        <button @click="generatePdf" class="btn btn-primary">تحميل PDF</button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تفاصيل العقد</h3></div>
          <div class="card-body">
            <div class="grid grid-cols-2 gap-4 text-sm mb-6">
              <div><span class="text-gray-500">المشروع:</span> <span class="mr-2 font-medium">{{ contract.project?.name }}</span></div>
              <div><span class="text-gray-500">العميل:</span> <span class="mr-2">{{ contract.project?.lead?.name }}</span></div>
              <div><span class="text-gray-500">قيمة العقد:</span> <span class="mr-2 font-bold text-primary-600">{{ formatCurrency(contract.total_value) }}</span></div>
              <div><span class="text-gray-500">تاريخ البدء:</span> <span class="mr-2">{{ formatDate(contract.start_date) }}</span></div>
              <div><span class="text-gray-500">تاريخ الانتهاء:</span> <span class="mr-2">{{ formatDate(contract.end_date) }}</span></div>
            </div>
            <div v-if="contract.scope_of_work" class="mb-4">
              <h4 class="font-medium mb-2">نطاق العمل</h4>
              <p class="text-sm text-gray-600 whitespace-pre-line">{{ contract.scope_of_work }}</p>
            </div>
            <div v-if="contract.terms_conditions">
              <h4 class="font-medium mb-2">الشروط والأحكام</h4>
              <p class="text-sm text-gray-600 whitespace-pre-line">{{ contract.terms_conditions }}</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">شروط الدفع</h3></div>
          <div class="card-body">
            <div v-if="contract.payment_terms?.length === 0" class="text-center text-gray-500 py-4">لا توجد شروط دفع</div>
            <div v-else class="space-y-3">
              <div v-for="term in contract.payment_terms" :key="term.id" class="p-3 bg-gray-50 rounded-lg flex items-center justify-between">
                <div>
                  <p class="font-medium">{{ term.description }}</p>
                  <p class="text-sm text-gray-500">{{ term.milestone }}</p>
                </div>
                <div class="text-left">
                  <p class="font-bold">{{ formatCurrency(term.amount) }}</p>
                  <p class="text-xs text-gray-500">{{ term.percentage }}%</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">حالة التوقيع</h3></div>
          <div class="card-body space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span>توقيع الشركة</span>
              <span v-if="contract.company_signed_at" class="badge badge-success">تم</span>
              <span v-else class="badge badge-gray">لم يتم</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <span>توقيع العميل</span>
              <span v-if="contract.client_signed_at" class="badge badge-success">تم</span>
              <span v-else class="badge badge-gray">لم يتم</span>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="font-semibold">إجراءات</h3></div>
          <div class="card-body space-y-3">
            <button v-if="contract.status === 'draft'" @click="updateStatus('pending_signature')" class="btn btn-primary w-full">إرسال للتوقيع</button>
            <router-link :to="`/dashboard/invoices/create?contract_id=${contract.id}&project_id=${contract.project_id}`" class="btn btn-secondary w-full block text-center">إنشاء فاتورة</router-link>
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
const contract = ref(null);

const fetchContract = async () => {
  const { data } = await axios.get(`/contracts/${route.params.id}`);
  contract.value = data.data;
};

const updateStatus = async (status) => {
  await axios.put(`/contracts/${contract.value.id}`, { status });
  fetchContract();
};

const generatePdf = async () => {
  const { data } = await axios.post(`/contracts/${contract.value.id}/pdf`);
  window.open(data.data.pdf_url, '_blank');
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ draft: 'مسودة', pending_signature: 'بانتظار التوقيع', active: 'نشط', completed: 'مكتمل' }[s] || s);
const statusClass = (s) => ({ draft: 'badge-gray', pending_signature: 'badge-warning', active: 'badge-success', completed: 'badge-info' }[s] || 'badge-gray');

onMounted(fetchContract);
</script>
