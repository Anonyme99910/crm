<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <input v-model="filters.search" type="text" placeholder="بحث..." class="form-input w-64" @input="debouncedFetch" />
        <select v-model="filters.status" class="form-input w-40" @change="fetchQuotations">
          <option value="">كل الحالات</option>
          <option value="draft">مسودة</option>
          <option value="sent">مرسل</option>
          <option value="viewed">تم العرض</option>
          <option value="accepted">مقبول</option>
          <option value="rejected">مرفوض</option>
        </select>
      </div>
      <router-link to="/quotations/create" class="btn btn-primary">إنشاء عرض سعر</router-link>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>رقم العرض</th>
              <th>العنوان</th>
              <th>العميل</th>
              <th>الإجمالي</th>
              <th>الحالة</th>
              <th>صالح حتى</th>
              <th>إجراءات</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="q in quotations" :key="q.id">
              <td class="font-medium">{{ q.quotation_number }}</td>
              <td>{{ q.title }}</td>
              <td>{{ q.lead?.name }}</td>
              <td>{{ formatCurrency(q.total) }}</td>
              <td><span :class="statusClass(q.status)" class="badge">{{ statusLabel(q.status) }}</span></td>
              <td>{{ formatDate(q.valid_until) }}</td>
              <td>
                <div class="flex items-center gap-2">
                  <router-link :to="`/quotations/${q.id}`" class="text-primary-600 hover:text-primary-800">عرض</router-link>
                  <router-link :to="`/quotations/${q.id}/edit`" class="text-gray-600 hover:text-gray-800">تعديل</router-link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="quotations.length === 0" class="text-center py-12 text-gray-500">لا توجد عروض أسعار</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import dayjs from 'dayjs';

const quotations = ref([]);
const filters = reactive({ search: '', status: '' });
let debounceTimer;

const debouncedFetch = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(fetchQuotations, 300); };

const fetchQuotations = async () => {
  const { data } = await axios.get('/quotations', { params: filters });
  quotations.value = data.data;
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ draft: 'مسودة', sent: 'مرسل', viewed: 'تم العرض', accepted: 'مقبول', rejected: 'مرفوض', expired: 'منتهي' }[s] || s);
const statusClass = (s) => ({ draft: 'badge-gray', sent: 'badge-info', viewed: 'badge-warning', accepted: 'badge-success', rejected: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchQuotations);
</script>
