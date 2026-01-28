<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <select v-model="filters.status" class="form-input w-40" @change="fetchVisits">
          <option value="">كل الحالات</option>
          <option value="scheduled">مجدولة</option>
          <option value="completed">مكتملة</option>
          <option value="cancelled">ملغاة</option>
        </select>
        <input v-model="filters.date_from" type="date" class="form-input" @change="fetchVisits" />
      </div>
      <router-link to="/dashboard/site-visits/create" class="btn btn-primary">جدولة معاينة</router-link>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>العميل</th>
              <th>العنوان</th>
              <th>المهندس</th>
              <th>الموعد</th>
              <th>الحالة</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="visit in visits" :key="visit.id">
              <td class="font-medium">{{ visit.lead?.name }}</td>
              <td>{{ visit.address?.substring(0, 50) }}...</td>
              <td>{{ visit.engineer?.name }}</td>
              <td>{{ formatDateTime(visit.scheduled_at) }}</td>
              <td><span :class="statusClass(visit.status)" class="badge">{{ statusLabel(visit.status) }}</span></td>
              <td>
                <router-link :to="`/dashboard/site-visits/${visit.id}`" class="text-primary-600 hover:text-primary-800">عرض</router-link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="visits.length === 0" class="text-center py-12 text-gray-500">لا توجد معاينات</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const visits = ref([]);
const filters = reactive({ status: '', date_from: '' });

const fetchVisits = async () => {
  const { data } = await axios.get('/site-visits', { params: filters });
  visits.value = data.data;
};

const formatDateTime = (d) => dayjs(d).format('YYYY/MM/DD HH:mm');
const statusLabel = (s) => ({ scheduled: 'مجدولة', in_progress: 'جارية', completed: 'مكتملة', cancelled: 'ملغاة', rescheduled: 'معاد جدولتها' }[s] || s);
const statusClass = (s) => ({ scheduled: 'badge-info', completed: 'badge-success', cancelled: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchVisits);
</script>
