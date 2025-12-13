<template>
  <div v-if="task" class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-2xl font-bold">{{ task.title }}</h2>
        <p class="text-gray-500">{{ task.project?.name }}</p>
      </div>
      <div class="flex items-center gap-3">
        <span :class="priorityClass(task.priority)" class="badge text-base px-4 py-2">{{ priorityLabel(task.priority) }}</span>
        <span :class="statusClass(task.status)" class="badge text-base px-4 py-2">{{ statusLabel(task.status) }}</span>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">التفاصيل</h3></div>
          <div class="card-body">
            <p v-if="task.description" class="text-gray-600 mb-4">{{ task.description }}</p>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div><span class="text-gray-500">المسؤول:</span> <span class="mr-2">{{ task.assignee?.name || '-' }}</span></div>
              <div><span class="text-gray-500">تاريخ الاستحقاق:</span> <span class="mr-2">{{ task.due_date ? formatDate(task.due_date) : '-' }}</span></div>
              <div><span class="text-gray-500">أنشأها:</span> <span class="mr-2">{{ task.creator?.name }}</span></div>
              <div><span class="text-gray-500">تاريخ الإنشاء:</span> <span class="mr-2">{{ formatDateTime(task.created_at) }}</span></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">قائمة المهام</h3>
            <button @click="showChecklistModal = true" class="btn btn-sm btn-primary">إضافة</button>
          </div>
          <div class="card-body">
            <div v-if="task.checklists?.length === 0" class="text-center text-gray-500 py-4">لا توجد عناصر</div>
            <div v-else class="space-y-2">
              <div v-for="item in task.checklists" :key="item.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded">
                <input type="checkbox" :checked="item.is_completed" @change="toggleChecklist(item)" class="rounded text-primary-600" />
                <span :class="{ 'line-through text-gray-400': item.is_completed }">{{ item.item }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header flex items-center justify-between">
            <h3 class="font-semibold">التعليقات</h3>
          </div>
          <div class="card-body space-y-4">
            <div v-for="comment in task.comments" :key="comment.id" class="p-3 bg-gray-50 rounded-lg">
              <p class="text-sm">{{ comment.comment }}</p>
              <p class="text-xs text-gray-500 mt-2">{{ comment.user?.name }} - {{ formatDateTime(comment.created_at) }}</p>
            </div>
            <form @submit.prevent="addComment" class="flex gap-3">
              <input v-model="newComment" type="text" class="form-input flex-1" placeholder="أضف تعليق..." />
              <button type="submit" class="btn btn-primary">إرسال</button>
            </form>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card">
          <div class="card-header"><h3 class="font-semibold">تغيير الحالة</h3></div>
          <div class="card-body space-y-3">
            <button v-if="task.status === 'pending'" @click="updateStatus('in_progress')" class="btn btn-primary w-full">بدء العمل</button>
            <button v-if="task.status === 'in_progress'" @click="updateStatus('completed')" class="btn btn-success w-full">إكمال المهمة</button>
            <button v-if="task.status !== 'cancelled'" @click="updateStatus('cancelled')" class="btn btn-danger w-full">إلغاء</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Checklist Modal -->
    <div v-if="showChecklistModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">إضافة عنصر</h3>
        <form @submit.prevent="addChecklist" class="space-y-4">
          <input v-model="checklistItem" class="form-input" placeholder="العنصر" required />
          <div class="flex gap-3">
            <button type="submit" class="btn btn-primary">حفظ</button>
            <button type="button" @click="showChecklistModal = false" class="btn btn-secondary">إلغاء</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import dayjs from 'dayjs';

const route = useRoute();
const task = ref(null);
const showChecklistModal = ref(false);
const checklistItem = ref('');
const newComment = ref('');

const fetchTask = async () => {
  const { data } = await axios.get(`/tasks/${route.params.id}`);
  task.value = data.data;
};

const updateStatus = async (status) => {
  await axios.put(`/tasks/${task.value.id}`, { status });
  fetchTask();
};

const toggleChecklist = async (item) => {
  await axios.put(`/task-checklists/${item.id}/toggle`);
  fetchTask();
};

const addChecklist = async () => {
  await axios.post(`/tasks/${task.value.id}/checklists`, { item: checklistItem.value });
  showChecklistModal.value = false;
  checklistItem.value = '';
  fetchTask();
};

const addComment = async () => {
  if (!newComment.value.trim()) return;
  await axios.post(`/tasks/${task.value.id}/comments`, { comment: newComment.value });
  newComment.value = '';
  fetchTask();
};

const formatDate = (d) => dayjs(d).format('YYYY/MM/DD');
const formatDateTime = (d) => dayjs(d).format('YYYY/MM/DD HH:mm');
const statusLabel = (s) => ({ pending: 'قيد الانتظار', in_progress: 'جاري', completed: 'مكتمل', cancelled: 'ملغي' }[s] || s);
const statusClass = (s) => ({ pending: 'badge-gray', in_progress: 'badge-info', completed: 'badge-success', cancelled: 'badge-danger' }[s] || 'badge-gray');
const priorityLabel = (p) => ({ urgent: 'عاجل', high: 'مرتفع', medium: 'متوسط', low: 'منخفض' }[p] || p);
const priorityClass = (p) => ({ urgent: 'badge-danger', high: 'badge-warning', medium: 'badge-info', low: 'badge-gray' }[p] || 'badge-gray');

onMounted(fetchTask);
</script>
