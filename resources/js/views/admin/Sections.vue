<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">أقسام الصفحة الرئيسية</h1>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="loading-spinner mx-auto"></div>
      </div>
      
      <div v-else class="divide-y divide-gray-200">
        <div 
          v-for="section in sections" 
          :key="section.id"
          class="p-6 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="text-lg font-bold text-gray-900">{{ section.title }}</h3>
                <span 
                  class="px-2 py-1 text-xs rounded-full"
                  :class="section.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ section.is_active ? 'مفعّل' : 'معطّل' }}
                </span>
              </div>
              
              <p class="text-gray-600 mb-2">{{ section.subtitle }}</p>
              <p class="text-gray-500 text-sm line-clamp-2">{{ section.content }}</p>
            </div>
            
            <button 
              @click="editSection(section)"
              class="mr-4 text-bronze hover:text-bronze-dark transition-colors"
              title="تعديل"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="editingSection" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900">تعديل القسم</h2>
        </div>
        
        <form @submit.prevent="saveSection" class="p-6 space-y-6">
          <div>
            <label class="block text-gray-700 font-medium mb-2">العنوان</label>
            <input 
              v-model="editingSection.title" 
              type="text" 
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
            >
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">العنوان الفرعي</label>
            <input 
              v-model="editingSection.subtitle" 
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
            >
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">المحتوى</label>
            <textarea 
              v-model="editingSection.content" 
              rows="5"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"
            ></textarea>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">لون الخلفية</label>
            <input 
              v-model="editingSection.background_color" 
              type="text"
              dir="ltr"
              placeholder="#ffffff"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
            >
          </div>
          
          <!-- Video Background (Hero section only) -->
          <div v-if="editingSection.name === 'hero'">
            <label class="block text-gray-700 font-medium mb-2">فيديو الخلفية (MP4)</label>
            <div class="space-y-3">
              <input 
                type="file" 
                accept="video/mp4"
                @change="handleVideoUpload"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
              >
              <div v-if="editingSection.background_video" class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <svg class="w-8 h-8 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <span class="flex-1 text-sm text-gray-600">{{ editingSection.background_video }}</span>
                <button 
                  type="button"
                  @click="removeVideo"
                  class="text-red-500 hover:text-red-700"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
              <p class="text-xs text-gray-500">يُفضل فيديو بدقة 1920x1080 وحجم أقل من 10MB للأداء الأمثل</p>
            </div>
          </div>
          
          <div class="flex items-center">
            <input 
              v-model="editingSection.is_active" 
              type="checkbox"
              class="w-4 h-4 text-bronze border-gray-300 rounded focus:ring-bronze"
            >
            <label class="mr-2 text-gray-700">مفعّل</label>
          </div>
          
          <div class="flex justify-end gap-4 pt-4 border-t border-gray-200">
            <button 
              type="button" 
              @click="editingSection = null"
              class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              إلغاء
            </button>
            <button 
              type="submit"
              class="btn-primary"
              :disabled="saving"
            >
              {{ saving ? 'جاري الحفظ...' : 'حفظ التغييرات' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/utils/axios';

const loading = ref(true);
const saving = ref(false);
const sections = ref([]);
const editingSection = ref(null);
const videoFile = ref(null);

const loadSections = async () => {
  try {
    const response = await axios.get('/api/landing/sections');
    sections.value = response.data;
  } catch (error) {
    alert('خطأ في تحميل الأقسام');
  } finally {
    loading.value = false;
  }
};

const editSection = (section) => {
  editingSection.value = { ...section };
  videoFile.value = null;
};

const handleVideoUpload = (event) => {
  const file = event.target.files[0];
  if (file) {
    if (file.size > 50 * 1024 * 1024) {
      alert('حجم الفيديو كبير جداً. الحد الأقصى 50MB');
      return;
    }
    videoFile.value = file;
  }
};

const removeVideo = () => {
  editingSection.value.background_video = null;
  videoFile.value = null;
};

const saveSection = async () => {
  saving.value = true;
  try {
    // If there's a video file, upload it first
    if (videoFile.value) {
      const formData = new FormData();
      formData.append('video', videoFile.value);
      formData.append('section_id', editingSection.value.id);
      
      const uploadResponse = await axios.post('/api/landing/sections/upload-video', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      
      editingSection.value.background_video = uploadResponse.data.path;
    }
    
    // Create a clean section object with only the needed properties
    const sectionToUpdate = {
      id: editingSection.value.id,
      name: editingSection.value.name,
      title: editingSection.value.title,
      subtitle: editingSection.value.subtitle,
      content: editingSection.value.content,
      background_image: editingSection.value.background_image,
      background_video: editingSection.value.background_video,
      background_color: editingSection.value.background_color,
      order: editingSection.value.order,
      is_active: editingSection.value.is_active
    };
    
    await axios.put(`/api/landing/sections/${editingSection.value.id}`, sectionToUpdate);
    alert('تم تحديث القسم بنجاح');
    editingSection.value = null;
    videoFile.value = null;
    await loadSections();
  } catch (error) {
    console.error('Error updating section:', error);
    alert('خطأ في تحديث القسم');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSections();
});
</script>
