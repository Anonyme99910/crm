<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <input v-model="filters.search" type="text" placeholder="بحث..." class="form-input w-64" @input="debouncedFetch" />
        <select v-model="filters.type" class="form-input w-40" @change="fetchSuppliers">
          <option value="">الكل</option>
          <option value="supplier">موردين</option>
          <option value="contractor">مقاولين</option>
        </select>
      </div>
      <div class="flex gap-3">
        <router-link to="/dashboard/purchase-orders" class="btn btn-secondary">أوامر الشراء</router-link>
        <router-link to="/dashboard/suppliers/create" class="btn btn-primary">إضافة مورد</router-link>
      </div>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr><th>الكود</th><th>الاسم</th><th>النوع</th><th>الهاتف</th><th>التقييم</th><th>إجراءات</th></tr>
          </thead>
          <tbody>
            <tr v-for="s in suppliers" :key="s.id">
              <td class="font-mono text-sm">{{ s.code }}</td>
              <td class="font-medium">{{ s.name }}</td>
              <td>{{ typeLabel(s.type) }}</td>
              <td dir="ltr">{{ s.phone }}</td>
              <td>
                <div class="flex items-center gap-1">
                  <span class="text-yellow-500">★</span>
                  <span>{{ s.rating || 0 }}</span>
                </div>
              </td>
              <td><router-link :to="`/dashboard/suppliers/${s.id}`" class="text-primary-600">عرض</router-link></td>
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

const suppliers = ref([]);
const filters = reactive({ search: '', type: '' });
let debounceTimer;

const debouncedFetch = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(fetchSuppliers, 300); };

const fetchSuppliers = async () => {
  const { data } = await axios.get('/suppliers', { params: filters });
  suppliers.value = data.data;
};

const typeLabel = (t) => ({ supplier: 'مورد', contractor: 'مقاول', both: 'مورد ومقاول' }[t] || t);

onMounted(fetchSuppliers);
</script>
