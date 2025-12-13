<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <input v-model="filters.search" type="text" placeholder="بحث..." class="form-input w-64" @input="debouncedFetch" />
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="filters.low_stock" @change="fetchMaterials" class="rounded" />
          <span class="text-sm">نقص المخزون فقط</span>
        </label>
      </div>
      <router-link to="/inventory/create" class="btn btn-primary">إضافة خامة</router-link>
    </div>

    <div class="card">
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>الكود</th>
              <th>الاسم</th>
              <th>التصنيف</th>
              <th>الوحدة</th>
              <th>السعر</th>
              <th>المخزون</th>
              <th>الحالة</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="m in materials" :key="m.id">
              <td class="font-mono text-sm">{{ m.code }}</td>
              <td class="font-medium">{{ m.name }}</td>
              <td>{{ m.category?.name || '-' }}</td>
              <td>{{ m.unit }}</td>
              <td>{{ formatCurrency(m.unit_price) }}</td>
              <td>{{ m.current_stock }}</td>
              <td>
                <span v-if="m.current_stock <= m.minimum_stock" class="badge badge-danger">نقص</span>
                <span v-else class="badge badge-success">متوفر</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="materials.length === 0" class="text-center py-12 text-gray-500">لا توجد خامات</div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const materials = ref([]);
const filters = reactive({ search: '', low_stock: false });
let debounceTimer;

const debouncedFetch = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(fetchMaterials, 300); };

const fetchMaterials = async () => {
  const { data } = await axios.get('/materials', { params: filters });
  materials.value = data.data;
};

const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP' }).format(v);

onMounted(fetchMaterials);
</script>
