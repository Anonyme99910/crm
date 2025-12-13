<template>
  <div class="space-y-4 lg:space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-4">
        <input v-model="filters.search" type="text" placeholder="بحث..." class="form-input w-full sm:w-48 lg:w-64" @input="debouncedFetch" />
        <div class="flex gap-2">
          <select v-model="filters.status" class="form-input flex-1 sm:w-32 lg:w-40" @change="fetchLeads">
            <option value="">كل الحالات</option>
            <option value="hot">ساخن</option>
            <option value="warm">دافئ</option>
            <option value="cold">بارد</option>
          </select>
          <select v-model="filters.source" class="form-input flex-1 sm:w-32 lg:w-40" @change="fetchLeads">
            <option value="">كل المصادر</option>
            <option value="whatsapp">واتساب</option>
            <option value="ads">إعلانات</option>
            <option value="call">مكالمات</option>
            <option value="website">موقع</option>
            <option value="referral">إحالة</option>
          </select>
        </div>
      </div>
      <router-link to="/leads/create" class="btn btn-primary w-full sm:w-auto">
        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        إضافة عميل
      </router-link>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>الاسم</th>
              <th>الهاتف</th>
              <th>المصدر</th>
              <th>الحالة</th>
              <th>المرحلة</th>
              <th>المسؤول</th>
              <th>التاريخ</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="lead in leads" :key="lead.id">
              <td class="font-medium">{{ lead.name }}</td>
              <td dir="ltr">{{ lead.phone }}</td>
              <td>{{ sourceLabel(lead.source) }}</td>
              <td><span :class="statusClass(lead.status)" class="badge">{{ statusLabel(lead.status) }}</span></td>
              <td><span class="badge badge-gray">{{ stageLabel(lead.stage) }}</span></td>
              <td>{{ lead.assigned_user?.name || '-' }}</td>
              <td>{{ formatDate(lead.created_at) }}</td>
              <td>
                <div class="flex items-center gap-2">
                  <router-link :to="`/leads/${lead.id}`" class="text-primary-600 hover:text-primary-800">عرض</router-link>
                  <router-link :to="`/leads/${lead.id}/edit`" class="text-gray-600 hover:text-gray-800">تعديل</router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div v-if="leads.length === 0" class="text-center py-12 text-gray-500">
        لا يوجد عملاء
      </div>

      <div v-if="meta.last_page > 1" class="p-4 border-t flex items-center justify-between">
        <p class="text-sm text-gray-500">عرض {{ leads.length }} من {{ meta.total }}</p>
        <div class="flex gap-2">
          <button @click="changePage(meta.current_page - 1)" :disabled="meta.current_page === 1" class="btn btn-sm btn-secondary">السابق</button>
          <button @click="changePage(meta.current_page + 1)" :disabled="meta.current_page === meta.last_page" class="btn btn-sm btn-secondary">التالي</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const leads = ref([]);
const meta = ref({});
const filters = reactive({ search: '', status: '', source: '' });

let debounceTimer;
const debouncedFetch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(fetchLeads, 300);
};

const fetchLeads = async (page = 1) => {
  try {
    const { data } = await axios.get('/leads', { params: { ...filters, page } });
    leads.value = data.data;
    meta.value = data.meta;
  } catch (error) {
    console.error(error);
  }
};

const changePage = (page) => fetchLeads(page);
const formatDate = (date) => dayjs(date).format('YYYY/MM/DD');
const sourceLabel = (s) => ({ whatsapp: 'واتساب', ads: 'إعلانات', call: 'مكالمات', website: 'موقع', referral: 'إحالة', other: 'أخرى' }[s] || s);
const statusLabel = (s) => ({ hot: 'ساخن', warm: 'دافئ', cold: 'بارد' }[s] || s);
const statusClass = (s) => ({ hot: 'badge-danger', warm: 'badge-warning', cold: 'badge-info' }[s] || 'badge-gray');
const stageLabel = (s) => ({ new: 'جديد', contacted: 'تم التواصل', qualified: 'مؤهل', proposal: 'عرض سعر', negotiation: 'تفاوض', won: 'مكسب', lost: 'خسارة' }[s] || s);

onMounted(fetchLeads);
</script>
