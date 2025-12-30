<template>
  <div class="p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">رسائل التواصل</h1>
      <div class="text-sm text-gray-600">
        <span class="font-semibold">{{ unreadCount }}</span> رسالة غير مقروءة
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-bronze"></div>
    </div>

    <div v-else-if="submissions.length === 0" class="text-center py-12 bg-white rounded-lg shadow">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
      </svg>
      <p class="mt-4 text-gray-500">لا توجد رسائل حتى الآن</p>
    </div>

    <div v-else class="space-y-4">
      <div v-for="submission in submissions" :key="submission.id" 
           class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow duration-300 overflow-hidden"
           :class="{ 'border-r-4 border-bronze': !submission.is_read }">
        <div class="p-6">
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-bold text-gray-900">{{ submission.name }}</h3>
                <span v-if="!submission.is_read" class="px-2 py-1 bg-bronze text-white text-xs rounded-full">جديد</span>
              </div>
              <div class="text-sm text-gray-600 space-y-1">
                <p><strong>البريد:</strong> <a :href="`mailto:${submission.email}`" class="text-bronze hover:underline" dir="ltr">{{ submission.email }}</a></p>
                <p v-if="submission.phone"><strong>الهاتف:</strong> <span dir="ltr">{{ submission.phone }}</span></p>
                <p v-if="submission.subject"><strong>الموضوع:</strong> {{ submission.subject }}</p>
                <p class="text-xs text-gray-400">{{ formatDate(submission.created_at) }}</p>
              </div>
            </div>
            <div class="flex gap-2">
              <button v-if="!submission.is_read" @click="markAsRead(submission.id)" 
                      class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                تم القراءة
              </button>
              <button @click="deleteSubmission(submission.id)" 
                      class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                حذف
              </button>
            </div>
          </div>
          <div class="mt-4 p-4 bg-gray-50 rounded">
            <p class="text-gray-700 whitespace-pre-wrap">{{ submission.message }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '@/utils/axios';

const submissions = ref([]);
const loading = ref(true);

const unreadCount = computed(() => {
  return submissions.value.filter(s => !s.is_read).length;
});

const fetchSubmissions = async () => {
  try {
    loading.value = true;
    const response = await axios.get('api/landing/contact-submissions');
    submissions.value = response.data;
  } catch (error) {
    console.error('Error fetching submissions:', error);
    alert('فشل في تحميل الرسائل');
  } finally {
    loading.value = false;
  }
};

const markAsRead = async (id) => {
  try {
    await axios.put(`api/landing/contact-submissions/${id}/read`);
    const submission = submissions.value.find(s => s.id === id);
    if (submission) {
      submission.is_read = true;
    }
  } catch (error) {
    console.error('Error marking as read:', error);
    alert('فشل في تحديث الحالة');
  }
};

const deleteSubmission = async (id) => {
  if (!confirm('هل أنت متأكد من حذف هذه الرسالة؟')) return;
  
  try {
    await axios.delete(`api/landing/contact-submissions/${id}`);
    submissions.value = submissions.value.filter(s => s.id !== id);
  } catch (error) {
    console.error('Error deleting submission:', error);
    alert('فشل في حذف الرسالة');
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};

onMounted(() => {
  fetchSubmissions();
});
</script>
