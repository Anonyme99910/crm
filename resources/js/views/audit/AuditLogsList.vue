<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-900">سجلات المراجعة والأمان</h1>
      <div class="flex gap-2">
        <button @click="activeTab = 'logs'" :class="[activeTab === 'logs' ? 'btn-primary' : 'btn-secondary']">
          سجل النشاط
        </button>
        <button @click="activeTab = 'logins'" :class="[activeTab === 'logins' ? 'btn-primary' : 'btn-secondary']">
          محاولات الدخول
        </button>
        <button @click="activeTab = 'dashboard'" :class="[activeTab === 'dashboard' ? 'btn-primary' : 'btn-secondary']">
          لوحة الأمان
        </button>
      </div>
    </div>

    <!-- Security Dashboard Tab -->
    <div v-if="activeTab === 'dashboard'" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-500">تسجيلات دخول ناجحة اليوم</div>
          <div class="text-2xl font-bold text-green-600">{{ dashboard.login_stats?.successful_today || 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-500">محاولات فاشلة اليوم</div>
          <div class="text-2xl font-bold text-red-600">{{ dashboard.login_stats?.failed_today || 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-500">إجمالي الأنشطة اليوم</div>
          <div class="text-2xl font-bold text-blue-600">{{ dashboard.activity_stats?.total_actions_today || 0 }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
          <div class="text-sm text-gray-500">عمليات الحذف اليوم</div>
          <div class="text-2xl font-bold text-orange-600">{{ dashboard.activity_stats?.deletes_today || 0 }}</div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold mb-4">أكثر المستخدمين نشاطاً اليوم</h3>
          <div class="space-y-2">
            <div v-for="user in dashboard.top_users_today" :key="user.user_id" class="flex justify-between items-center py-2 border-b">
              <span>{{ user.user_name || 'غير معروف' }}</span>
              <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">{{ user.actions_count }} نشاط</span>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="font-bold mb-4">آخر تسجيلات الدخول</h3>
          <div class="space-y-2">
            <div v-for="login in dashboard.recent_logins" :key="login.id" class="flex justify-between items-center py-2 border-b">
              <div>
                <span class="font-medium">{{ login.user?.name || login.email }}</span>
                <span class="text-xs text-gray-500 block">{{ login.ip_address }}</span>
              </div>
              <span class="text-xs text-gray-500">{{ formatDateTime(login.created_at) }}</span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="dashboard.suspicious_activity?.multiple_failed_logins?.length" class="bg-red-50 rounded-lg shadow p-6">
        <h3 class="font-bold text-red-800 mb-4">⚠️ نشاط مشبوه</h3>
        <div class="space-y-2">
          <div v-for="item in dashboard.suspicious_activity.multiple_failed_logins" :key="item.email + item.ip_address" class="flex justify-between items-center py-2 border-b border-red-200">
            <div>
              <span class="font-medium">{{ item.email }}</span>
              <span class="text-xs text-gray-500 block">IP: {{ item.ip_address }}</span>
            </div>
            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">{{ item.attempts }} محاولة فاشلة</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Audit Logs Tab -->
    <div v-if="activeTab === 'logs'">
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">المستخدم</label>
            <select v-model="filters.user_id" @change="loadLogs" class="input-field">
              <option value="">الكل</option>
              <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الإجراء</label>
            <select v-model="filters.action" @change="loadLogs" class="input-field">
              <option value="">الكل</option>
              <option v-for="action in actions" :key="action" :value="action">{{ getActionLabel(action) }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
            <input v-model="filters.from_date" type="date" @change="loadLogs" class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
            <input v-model="filters.to_date" type="date" @change="loadLogs" class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">بحث</label>
            <input v-model="filters.search" type="text" @input="debounceSearch" placeholder="بحث..." class="input-field">
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المستخدم</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الإجراء</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">النموذج</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الوصف</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">IP</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التفاصيل</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(log.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ log.user_name || 'نظام' }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs rounded-full" :class="getActionClass(log.action)">
                    {{ getActionLabel(log.action) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span v-if="log.model_type">{{ getModelLabel(log.model_type) }} #{{ log.model_id }}</span>
                  <span v-else class="text-gray-400">-</span>
                </td>
                <td class="px-6 py-4 text-sm">{{ log.description || log.model_name || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">{{ log.ip_address }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button v-if="log.old_values || log.new_values" @click="viewDetails(log)" class="text-blue-600 hover:text-blue-800">
                    عرض التغييرات
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Login Attempts Tab -->
    <div v-if="activeTab === 'logins'">
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
            <input v-model="loginFilters.email" type="text" @input="debounceLoginSearch" class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الحالة</label>
            <select v-model="loginFilters.successful" @change="loadLoginAttempts" class="input-field">
              <option value="">الكل</option>
              <option value="true">ناجح</option>
              <option value="false">فاشل</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">من تاريخ</label>
            <input v-model="loginFilters.from_date" type="date" @change="loadLoginAttempts" class="input-field">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">إلى تاريخ</label>
            <input v-model="loginFilters.to_date" type="date" @change="loadLoginAttempts" class="input-field">
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">التاريخ</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">البريد الإلكتروني</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">الحالة</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">سبب الفشل</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">IP</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">المتصفح</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="attempt in loginAttempts" :key="attempt.id" class="hover:bg-gray-50" :class="{ 'bg-red-50': !attempt.successful }">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateTime(attempt.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ attempt.email }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs rounded-full" :class="attempt.successful ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                    {{ attempt.successful ? 'ناجح' : 'فاشل' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-red-600">{{ attempt.failure_reason || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">{{ attempt.ip_address }}</td>
                <td class="px-6 py-4 text-sm text-gray-500 truncate max-w-xs">{{ attempt.user_agent }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <div v-if="showDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b">
          <h2 class="text-xl font-bold">تفاصيل التغييرات</h2>
        </div>
        <div class="p-6 space-y-4">
          <div v-if="selectedLog?.changed_fields?.length" class="mb-4">
            <h3 class="font-bold text-sm text-gray-600 mb-2">الحقول المتغيرة:</h3>
            <div class="flex flex-wrap gap-2">
              <span v-for="field in selectedLog.changed_fields" :key="field" class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                {{ field }}
              </span>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div v-if="selectedLog?.old_values">
              <h3 class="font-bold text-sm text-gray-600 mb-2">القيم القديمة:</h3>
              <pre class="bg-red-50 p-4 rounded text-sm overflow-auto max-h-64">{{ JSON.stringify(selectedLog.old_values, null, 2) }}</pre>
            </div>
            <div v-if="selectedLog?.new_values">
              <h3 class="font-bold text-sm text-gray-600 mb-2">القيم الجديدة:</h3>
              <pre class="bg-green-50 p-4 rounded text-sm overflow-auto max-h-64">{{ JSON.stringify(selectedLog.new_values, null, 2) }}</pre>
            </div>
          </div>
        </div>
        <div class="p-6 border-t flex justify-end">
          <button @click="showDetailsModal = false" class="btn-secondary">إغلاق</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import axios from '@/utils/axios';

const activeTab = ref('dashboard');
const logs = ref([]);
const loginAttempts = ref([]);
const users = ref([]);
const actions = ref([]);
const dashboard = ref({});
const selectedLog = ref(null);
const showDetailsModal = ref(false);

const filters = ref({ user_id: '', action: '', from_date: '', to_date: '', search: '' });
const loginFilters = ref({ email: '', successful: '', from_date: '', to_date: '' });

let searchTimeout = null;

const loadLogs = async () => {
  try {
    const params = new URLSearchParams();
    Object.entries(filters.value).forEach(([key, value]) => { if (value) params.append(key, value); });
    const response = await axios.get(`/api/audit/logs?${params}`);
    logs.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading logs:', error);
  }
};

const loadLoginAttempts = async () => {
  try {
    const params = new URLSearchParams();
    Object.entries(loginFilters.value).forEach(([key, value]) => { if (value) params.append(key, value); });
    const response = await axios.get(`/api/audit/login-attempts?${params}`);
    loginAttempts.value = response.data.data || response.data;
  } catch (error) {
    console.error('Error loading login attempts:', error);
  }
};

const loadDashboard = async () => {
  try {
    const response = await axios.get('/api/audit/security-dashboard');
    dashboard.value = response.data;
  } catch (error) {
    console.error('Error loading dashboard:', error);
  }
};

const loadActions = async () => {
  try {
    const response = await axios.get('/api/audit/actions');
    actions.value = response.data;
  } catch (error) {
    console.error('Error loading actions:', error);
  }
};

const loadUsers = async () => {
  try {
    const response = await axios.get('/api/users');
    users.value = response.data;
  } catch (error) {
    console.error('Error loading users:', error);
  }
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(loadLogs, 300);
};

const debounceLoginSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(loadLoginAttempts, 300);
};

const viewDetails = (log) => {
  selectedLog.value = log;
  showDetailsModal.value = true;
};

const formatDateTime = (date) => date ? new Date(date).toLocaleString('ar-EG') : '';

const getActionLabel = (action) => {
  const labels = { create: 'إنشاء', update: 'تعديل', delete: 'حذف', login: 'تسجيل دخول', logout: 'تسجيل خروج', login_failed: 'فشل دخول', export: 'تصدير', approve: 'اعتماد', reject: 'رفض' };
  return labels[action] || action;
};

const getActionClass = (action) => {
  const classes = { create: 'bg-green-100 text-green-800', update: 'bg-blue-100 text-blue-800', delete: 'bg-red-100 text-red-800', login: 'bg-purple-100 text-purple-800', logout: 'bg-gray-100 text-gray-800', login_failed: 'bg-red-100 text-red-800', export: 'bg-yellow-100 text-yellow-800', approve: 'bg-green-100 text-green-800', reject: 'bg-red-100 text-red-800' };
  return classes[action] || 'bg-gray-100 text-gray-800';
};

const getModelLabel = (modelType) => {
  const parts = modelType.split('\\');
  const name = parts[parts.length - 1];
  const labels = { Lead: 'عميل محتمل', Project: 'مشروع', Invoice: 'فاتورة', Quotation: 'عرض سعر', Contract: 'عقد', User: 'مستخدم', BankAccount: 'حساب بنكي', EmployeeFund: 'عهدة', SupplierBill: 'فاتورة مورد', CustomerInvoice: 'فاتورة عميل' };
  return labels[name] || name;
};

watch(activeTab, (tab) => {
  if (tab === 'logs') loadLogs();
  else if (tab === 'logins') loadLoginAttempts();
  else if (tab === 'dashboard') loadDashboard();
});

onMounted(() => {
  loadDashboard();
  loadUsers();
  loadActions();
});
</script>
