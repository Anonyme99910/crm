<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <input v-model="filters.search" type="text" placeholder="بحث..." class="form-input w-64" @input="debouncedFetch" />
        <select v-model="filters.status" class="form-input w-40" @change="fetchProjects">
          <option value="">كل الحالات</option>
          <option value="pending">قيد الانتظار</option>
          <option value="in_progress">جاري التنفيذ</option>
          <option value="on_hold">متوقف</option>
          <option value="completed">مكتمل</option>
        </select>
      </div>
      <router-link :to="{ name: 'projects.create' }" class="btn btn-primary">إنشاء مشروع</router-link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <router-link v-for="project in projects" :key="project.id" :to="{ name: 'projects.show', params: { id: project.id } }" class="card hover:shadow-lg transition-shadow">
        <div class="card-body">
          <div class="flex items-start justify-between mb-4">
            <div>
              <p class="text-xs text-gray-500">{{ project.project_number }}</p>
              <h3 class="font-semibold text-gray-900">{{ project.name }}</h3>
              <p class="text-sm text-gray-500">{{ project.lead?.name }}</p>
            </div>
            <span :class="statusClass(project.status)" class="badge">{{ statusLabel(project.status) }}</span>
          </div>
          <div class="mb-4">
            <div class="flex items-center justify-between text-sm mb-1">
              <span class="text-gray-500">نسبة الإنجاز</span>
              <span class="font-medium">{{ project.progress_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-primary-600 h-2 rounded-full" :style="{ width: project.progress_percentage + '%' }"></div>
            </div>
          </div>
          <div class="flex items-center justify-between text-sm text-gray-500">
            <span>{{ formatCurrency(project.contract_value) }}</span>
            <span>{{ project.manager?.name }}</span>
          </div>
        </div>
      </router-link>
    </div>

    <div v-if="projects.length === 0" class="text-center py-12 text-gray-500">لا توجد مشاريع</div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const projects = ref([]);
const filters = reactive({ search: '', status: '' });
let debounceTimer;

const debouncedFetch = () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(fetchProjects, 300); };

const fetchProjects = async () => {
  const { data } = await axios.get('/projects', { params: filters });
  projects.value = data.data;
};

const formatCurrency = (v) => new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(v);
const statusLabel = (s) => ({ pending: 'قيد الانتظار', in_progress: 'جاري التنفيذ', on_hold: 'متوقف', completed: 'مكتمل', cancelled: 'ملغي' }[s] || s);
const statusClass = (s) => ({ pending: 'badge-gray', in_progress: 'badge-info', on_hold: 'badge-warning', completed: 'badge-success', cancelled: 'badge-danger' }[s] || 'badge-gray');

onMounted(fetchProjects);
</script>
