<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-900 mb-8">إعدادات الموقع</h1>
    
    <div class="bg-white rounded-lg shadow">
      <form @submit.prevent="saveSettings" class="p-6 space-y-8">
        <div>
          <h2 class="text-xl font-bold text-gray-900 mb-4">معلومات الموقع</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">عنوان الموقع</label>
              <input v-model="settings.site_title" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">العنوان الفرعي</label>
              <input v-model="settings.site_subtitle" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
          </div>
          
          <div class="mt-6">
            <label class="block text-gray-700 font-medium mb-2">وصف الموقع</label>
            <textarea v-model="settings.site_description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"></textarea>
          </div>

          <div class="mt-6">
            <label class="block text-gray-700 font-medium mb-2">شعار الموقع</label>
            <div class="flex items-center gap-4">
              <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                <img v-if="settings.site_logo" :src="getPublicUrl(settings.site_logo)" alt="شعار الموقع" class="w-full h-full object-contain" />
                <img v-else src="/crm/logo.png" alt="شعار الموقع" class="w-full h-full object-contain" />
              </div>

              <div class="flex-1">
                <label class="block w-full cursor-pointer">
                  <span class="block px-4 py-3 bg-white border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-bronze transition-colors">
                    <span class="text-sm text-gray-700">{{ uploadingLogo ? 'جاري الرفع...' : 'انقر لرفع شعار جديد' }}</span>
                    <span class="block text-xs text-gray-400 mt-1">PNG/JPG/WebP (الحد الأقصى 50MB)</span>
                  </span>
                  <input :disabled="uploadingLogo" @change="uploadLogo" type="file" accept="image/*" class="hidden" />
                </label>

                <div v-if="settings.site_logo" class="mt-2">
                  <button type="button" @click="removeLogo" class="text-sm text-red-600 hover:text-red-800">إزالة الشعار</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="border-t border-gray-200 pt-8">
          <h2 class="text-xl font-bold text-gray-900 mb-4">معلومات التواصل</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">البريد الإلكتروني</label>
              <input v-model="settings.contact_email" type="email" dir="ltr" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">رقم الهاتف</label>
              <input v-model="settings.contact_phone" type="text" dir="ltr" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
          </div>
          
          <div class="mt-6">
            <label class="block text-gray-700 font-medium mb-2">العنوان</label>
            <input v-model="settings.contact_address" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
          </div>
        </div>
        
        <div class="border-t border-gray-200 pt-8">
          <h2 class="text-xl font-bold text-gray-900 mb-4">وسائل التواصل الاجتماعي</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">رابط فيسبوك</label>
              <input v-model="settings.social_facebook" type="url" dir="ltr" placeholder="https://facebook.com/..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">رابط انستغرام</label>
              <input v-model="settings.social_instagram" type="url" dir="ltr" placeholder="https://instagram.com/..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">رابط تويتر</label>
              <input v-model="settings.social_twitter" type="url" dir="ltr" placeholder="https://twitter.com/..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">رابط لينكد إن</label>
              <input v-model="settings.social_linkedin" type="url" dir="ltr" placeholder="https://linkedin.com/..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
          </div>
        </div>
        
        <div class="flex justify-end pt-6 border-t border-gray-200">
          <button type="submit" class="btn-primary" :disabled="saving">
            {{ saving ? 'جاري الحفظ...' : 'حفظ الإعدادات' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '@/utils/axios';



const saving = ref(false);
const uploadingLogo = ref(false);
const settings = ref({
  site_title: '',
  site_subtitle: '',
  site_description: '',
  site_logo: '',
  contact_email: '',
  contact_phone: '',
  contact_address: '',
  social_facebook: '',
  social_instagram: '',
  social_twitter: '',
  social_linkedin: '',
});

const getPublicUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `/crm/storage/${path}`;
};

const loadSettings = async () => {
  try {
    const response = await axios.get('api/landing/settings');
    const settingsArray = response.data;
    settingsArray.forEach(setting => {
      settings.value[setting.key] = setting.value;
    });
  } catch (error) {
    alert('خطأ في تحميل الإعدادات');
  }
};

const saveSettings = async () => {
  saving.value = true;
  try {
    await axios.put('api/landing/settings/bulk', settings.value);
    alert('تم حفظ الإعدادات بنجاح');
  } catch (error) {
    alert('خطأ في حفظ الإعدادات');
  } finally {
    saving.value = false;
  }
};

const uploadLogo = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  uploadingLogo.value = true;
  try {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'site_logo');

    const response = await axios.post('api/landing/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    settings.value.site_logo = response.data.path;
    alert('تم رفع الشعار بنجاح');
  } catch (error) {
    alert('خطأ في رفع الشعار');
  } finally {
    uploadingLogo.value = false;
    event.target.value = '';
  }
};

const removeLogo = () => {
  settings.value.site_logo = '';
};

onMounted(() => {
  loadSettings();
});
</script>
