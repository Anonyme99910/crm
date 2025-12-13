<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <select v-model="filters.status" class="form-input w-40" @change="fetchTasks">
          <option value="">كل الحالات</option>
          <option value="pending">قيد الانتظار</option>
          <option value="in_progress">جاري</option>
          <option value="completed">مكتمل</option>
        </select>
        <select v-model="filters.priority" class="form-input w-40" @change="fetchTasks">
          <option value="">كل الأولويات</option>
          <option value="urgent">عاجل</option>
          <option value="high">مرتفع</option>
          <option value="medium">متوسط</option>
          <option value="low">منخفض</option>
        </select>
      </div>
      <router-link to="/tasks/create" class="btn btn-primary">إضافة مهمة</router-link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="task in tasks" :key="task.id" class="card hover:shadow-md transition-shadow cursor-pointer" @click="$router.push(`/tasks/${task.id}`)">
        <div class="card-body">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-medium text-gray-900">{{ task.title }}</h3>
              <p class="text-sm text-gray-500">{{ task.project?.name }}</p>
            </div>
            <span :class="priorityClass(task.priority)" class="badge">{{ priorityLabel(task.priority) }}</span>
          </div>
          <div class="flex items-center justify-between text-sm">
            <span :class="statusClass(task.status)" class="badge">{{ statusLabel(task.status) }}</span>
            <span class="text-gray-500">{{ task.due_date ? formatDate(task.due_date) : '-' }}</span>
          </div>
          <div v-if="task.assignee" class="mt-3 pt-3 border-t text-sm text-gray-500">
            {{ task.assignee.name }}
          </div>
        </div>
      </div>
    </div>

    <div v-if="tasks.length === 0" class="text-center py-12 text-gray-500">لا توجد مهام</div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const tasks = ref([]);
const filters = reactive({ status: '', priority: '', project_id: route.query.project_id || '' });

const fetchTasks = async () => {
  const { data } = await axios.get('/tasks', { params: filters });
  tasks.value = data.data;
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const statusLabel = (s) => ({ pending: 'قيد الانتظار', in_progress: 'جاري', completed: 'مكتمل', cancelled: 'ملغي' }[s] || s);
const statusClass = (s) => ({ pending: 'badge-gray', in_progress: 'badge-info', completed: 'badge-success', cancelled: 'badge-danger' }[s] || 'badge-gray');
const priorityLabel = (p) => ({ urgent: 'عاجل', high: 'مرتفع', medium: 'متوسط', low: 'منخفض' }[p] || p);
const priorityClass = (p) => ({ urgent: 'badge-danger', high: 'badge-warning', medium: 'badge-info', low: 'badge-gray' }[p] || 'badge-gray');

onMounted(fetchTasks);
</script>
