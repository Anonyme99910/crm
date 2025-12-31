<template>
  <div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-value">{{ stats.leads?.total || 0 }}</p>
            <p class="stat-label">إجمالي العملاء</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
          <span class="text-green-600 font-medium">{{ stats.leads?.this_month || 0 }}</span>
          <span class="text-gray-500 mr-1">هذا الشهر</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-value">{{ stats.projects?.in_progress || 0 }}</p>
            <p class="stat-label">مشاريع قيد التنفيذ</p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
          <span class="text-blue-600 font-medium">{{ stats.projects?.total || 0 }}</span>
          <span class="text-gray-500 mr-1">إجمالي المشاريع</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-value">{{ formatCurrency(stats.finance?.this_month_revenue || 0) }}</p>
            <p class="stat-label">إيرادات الشهر</p>
          </div>
          <div class="p-3 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
          <span class="text-gray-500">إجمالي:</span>
          <span class="text-gray-700 font-medium mr-1">{{ formatCurrency(stats.finance?.total_revenue || 0) }}</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="flex items-center justify-between">
          <div>
            <p class="stat-value">{{ stats.quotations?.pending || 0 }}</p>
            <p class="stat-label">عروض أسعار معلقة</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-full">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
          <span class="text-green-600 font-medium">{{ stats.quotations?.accepted || 0 }}</span>
          <span class="text-gray-500 mr-1">مقبولة</span>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-gray-900">إجراءات سريعة</h3>
        </div>
        <div class="card-body space-y-3">
          <router-link :to="{ name: 'leads.create' }" class="btn btn-primary w-full">
            إضافة عميل جديد
          </router-link>
          <router-link :to="{ name: 'site-visits.create' }" class="btn btn-secondary w-full">
            جدولة معاينة
          </router-link>
          <router-link :to="{ name: 'quotations.create' }" class="btn btn-secondary w-full">
            إنشاء عرض سعر
          </router-link>
          <router-link :to="{ name: 'tasks.create' }" class="btn btn-secondary w-full">
            إضافة مهمة
          </router-link>
        </div>
      </div>

      <div class="card lg:col-span-2">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">أحدث العملاء</h3>
          <router-link :to="{ name: 'leads' }" class="text-sm text-primary-600 hover:underline">عرض الكل</router-link>
        </div>
        <div class="card-body">
          <div v-if="recentLeads.length === 0" class="text-center text-gray-500 py-8">
            لا يوجد عملاء حتى الآن
          </div>
          <div v-else class="space-y-4">
            <div v-for="lead in recentLeads" :key="lead.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div>
                <p class="font-medium text-gray-900">{{ lead.name }}</p>
                <p class="text-sm text-gray-500">{{ lead.phone }}</p>
              </div>
              <div class="text-left">
                <span :class="statusClass(lead.status)" class="badge">{{ statusLabel(lead.status) }}</span>
                <p class="text-xs text-gray-400 mt-1">{{ formatDate(lead.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Today's Tasks & Visits -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="card">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">مهام اليوم</h3>
          <router-link :to="{ name: 'tasks' }" class="text-sm text-primary-600 hover:underline">عرض الكل</router-link>
        </div>
        <div class="card-body">
          <div v-if="todayTasks.length === 0" class="text-center text-gray-500 py-8">
            لا توجد مهام لليوم
          </div>
          <div v-else class="space-y-3">
            <div v-for="task in todayTasks" :key="task.id" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
              <input type="checkbox" :checked="task.status === 'completed'" class="rounded text-primary-600" disabled />
              <div class="flex-1">
                <p class="font-medium text-gray-900">{{ task.title }}</p>
                <p class="text-sm text-gray-500">{{ task.project?.name }}</p>
              </div>
              <span :class="priorityClass(task.priority)" class="badge">{{ priorityLabel(task.priority) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header flex items-center justify-between">
          <h3 class="font-semibold text-gray-900">معاينات اليوم</h3>
          <router-link :to="{ name: 'site-visits' }" class="text-sm text-primary-600 hover:underline">عرض الكل</router-link>
        </div>
        <div class="card-body">
          <div v-if="todayVisits.length === 0" class="text-center text-gray-500 py-8">
            لا توجد معاينات لليوم
          </div>
          <div v-else class="space-y-3">
            <div v-for="visit in todayVisits" :key="visit.id" class="p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center justify-between">
                <p class="font-medium text-gray-900">{{ visit.lead?.name }}</p>
                <span class="text-sm text-gray-500">{{ formatTime(visit.scheduled_at) }}</span>
              </div>
              <p class="text-sm text-gray-500 mt-1">{{ visit.address }}</p>
              <p class="text-xs text-primary-600 mt-1">م. {{ visit.engineer?.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const stats = ref({});
const recentLeads = ref([]);
const todayTasks = ref([]);
const todayVisits = ref([]);

const fetchDashboard = async () => {
  try {
    const [dashboardRes, leadsRes, tasksRes, visitsRes] = await Promise.all([
      axios.get('/dashboard'),
      axios.get('/leads', { params: { per_page: 5 } }),
      axios.get('/tasks-today'),
      axios.get('/site-visits-today'),
    ]);
    
    stats.value = dashboardRes.data.data;
    recentLeads.value = leadsRes.data.data;
    todayTasks.value = tasksRes.data.data;
    todayVisits.value = visitsRes.data.data;
  } catch (error) {
    console.error('Error fetching dashboard:', error);
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(value);
};

const formatDate = (date) => dayjs(date).format('YYYY/MM/DD');
const formatTime = (date) => dayjs(date).format('HH:mm');

const statusLabel = (status) => ({ hot: 'ساخن', warm: 'دافئ', cold: 'بارد' }[status] || status);
const statusClass = (status) => ({ hot: 'badge-danger', warm: 'badge-warning', cold: 'badge-info' }[status] || 'badge-gray');

const priorityLabel = (priority) => ({ urgent: 'عاجل', high: 'مرتفع', medium: 'متوسط', low: 'منخفض' }[priority] || priority);
const priorityClass = (priority) => ({ urgent: 'badge-danger', high: 'badge-warning', medium: 'badge-info', low: 'badge-gray' }[priority] || 'badge-gray');

onMounted(fetchDashboard);
</script>
