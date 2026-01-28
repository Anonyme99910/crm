<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <select v-model="filters.status" class="form-input w-40" @change="fetchContracts">
        <option value="">كل الحالات</option>
        <option value="draft">مسودة</option>
        <option value="pending_signature">بانتظار التوقيع</option>
        <option value="active">نشط</option>
        <option value="completed">مكتمل</option>
      </select>
      <router-link to="/dashboard/contracts/create" class="btn btn-primary">إنشاء عقد</router-link>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>رقم العقد</th><th>العنوان</th><th>المشروع</th><th>القيمة</th><th>الحالة</th><th>إجراءات</th></tr>
          </thead>
          <tbody>
            <tr v-for="c in contracts" :key="c.id">
              <td class="font-medium">{{ c.contract_number }}</td>
              <td>{{ c.title }}</td>
              <td>{{ c.project?.name }}</td>
              <td>{{ formatCurrency(c.total_value) }}</td>
              <td><span :class="statusClass(c.status)" class="badge">{{ statusLabel(c.status) }}</span></td>
              <td><router-link :to="`/dashboard/contracts/${c.id}`" class="text-primary-600">عرض</router-link></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const contracts = ref([]);
const filters = reactive({ status: '' });

const fetchContracts = async () => {
  const { data } = await axios.get('/contracts', { params: filters });
  contracts.value = data.data;
};

const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);
const statusLabel = (s) => ({ draft: 'مسودة', pending_signature: 'بانتظار التوقيع', active: 'نشط', completed: 'مكتمل', terminated: 'منتهي' }[s] || s);
const statusClass = (s) => ({ draft: 'badge-gray', pending_signature: 'badge-warning', active: 'badge-success', completed: 'badge-info' }[s] || 'badge-gray');

onMounted(fetchContracts);
</script>
