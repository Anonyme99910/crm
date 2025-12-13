<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="stat-card">
        <p class="stat-value text-blue-600">{{ formatCurrency(financial.income) }}</p>
        <p class="stat-label">إجمالي الإيرادات</p>
      </div>
      <div class="stat-card">
        <p class="stat-value text-red-600">{{ formatCurrency(financial.expenses) }}</p>
        <p class="stat-label">إجمالي المصروفات</p>
      </div>
      <div class="stat-card">
        <p class="stat-value" :class="financial.profit >= 0 ? 'text-green-600' : 'text-red-600'">{{ formatCurrency(financial.profit) }}</p>
        <p class="stat-label">صافي الربح</p>
      </div>
      <div class="stat-card">
        <p class="stat-value text-yellow-600">{{ leadsReport.conversion_rate }}%</p>
        <p class="stat-label">معدل التحويل</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <div class="card-header"><h3 class="font-semibold">العملاء حسب المصدر</h3></div>
        <div class="card-body">
          <div v-for="(count, source) in leadsReport.by_source" :key="source" class="flex items-center justify-between py-2 border-b last:border-0">
            <span>{{ sourceLabel(source) }}</span>
            <span class="font-bold">{{ count }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3 class="font-semibold">المشاريع حسب الحالة</h3></div>
        <div class="card-body">
          <div v-for="(count, status) in projectsReport.by_status" :key="status" class="flex items-center justify-between py-2 border-b last:border-0">
            <span>{{ projectStatusLabel(status) }}</span>
            <span class="font-bold">{{ count }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3 class="font-semibold">المصروفات حسب التصنيف</h3></div>
        <div class="card-body">
          <div v-for="(amount, category) in financial.expenses_by_category" :key="category" class="flex items-center justify-between py-2 border-b last:border-0">
            <span>{{ categoryLabel(category) }}</span>
            <span class="font-bold text-red-600">{{ formatCurrency(amount) }}</span>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><h3 class="font-semibold">ملخص المشاريع</h3></div>
        <div class="card-body space-y-3">
          <div class="flex items-center justify-between">
            <span class="text-gray-500">إجمالي قيمة المشاريع</span>
            <span class="font-bold">{{ formatCurrency(projectsReport.total_value) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-500">متوسط قيمة المشروع</span>
            <span class="font-bold">{{ formatCurrency(projectsReport.average_value) }}</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-500">متوسط نسبة الإنجاز</span>
            <span class="font-bold">{{ Math.round(projectsReport.average_progress || 0) }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span class="text-gray-500">مشاريع متأخرة</span>
            <span class="font-bold text-red-600">{{ projectsReport.delayed }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const financial = ref({ income: 0, expenses: 0, profit: 0, expenses_by_category: {} });
const leadsReport = ref({ by_source: {}, conversion_rate: 0 });
const projectsReport = ref({ by_status: {}, total_value: 0, average_value: 0, average_progress: 0, delayed: 0 });

const fetchReports = async () => {
  const [finRes, leadsRes, projRes] = await Promise.all([
    axios.get('/reports/financial'),
    axios.get('/reports/leads'),
    axios.get('/reports/projects')
  ]);
  financial.value = finRes.data.data;
  leadsReport.value = leadsRes.data.data;
  projectsReport.value = projRes.data.data;
};

const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(v || 0);
const sourceLabel = (s) => ({ whatsapp: 'واتساب', ads: 'إعلانات', call: 'مكالمات', website: 'موقع', referral: 'إحالة', other: 'أخرى' }[s] || s);
const projectStatusLabel = (s) => ({ pending: 'قيد الانتظار', in_progress: 'جاري التنفيذ', on_hold: 'متوقف', completed: 'مكتمل', cancelled: 'ملغي' }[s] || s);
const categoryLabel = (c) => ({ materials: 'خامات', labor: 'عمالة', transport: 'نقل', equipment: 'معدات', other: 'أخرى' }[c] || c);

onMounted(fetchReports);
</script>
