<template>
  <section id="contact" class="section-padding bg-gradient-to-b from-white to-primary-50/30 relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-20 right-0 w-96 h-96 bg-bronze/5 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 bg-bronze/5 rounded-full blur-3xl"></div>
    
    <div class="container-custom relative z-10">
      <div class="text-center mb-16">
        <span class="inline-block px-4 py-2 bg-bronze/10 text-bronze rounded-full text-sm font-semibold mb-4">{{ section.subtitle }}</span>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ section.title }}</h2>
        <div class="w-24 h-1 bg-gradient-bronze mx-auto rounded-full mb-6"></div>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">{{ section.content }}</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Contact Info -->
        <div class="order-2 lg:order-1">
          <div class="space-y-6">
            <div class="flex items-start gap-4 p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
              <div class="flex-shrink-0 w-14 h-14 bg-gradient-bronze rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 mb-1 text-lg">البريد الإلكتروني</h3>
                <p class="text-gray-600" dir="ltr">{{ settings.contact_email || 'info@hazemabdullah.com' }}</p>
              </div>
            </div>
            
            <div class="flex items-start gap-4 p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
              <div class="flex-shrink-0 w-14 h-14 bg-gradient-bronze rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 mb-1 text-lg">رقم الهاتف</h3>
                <p class="text-gray-600" dir="ltr">{{ settings.contact_phone || '+966 50 123 4567' }}</p>
              </div>
            </div>
            
            <div class="flex items-start gap-4 p-6 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 border border-gray-100">
              <div class="flex-shrink-0 w-14 h-14 bg-gradient-bronze rounded-xl flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 mb-1 text-lg">العنوان</h3>
                <p class="text-gray-600">{{ settings.contact_address || 'الرياض، المملكة العربية السعودية' }}</p>
              </div>
            </div>
          </div>
          
          <div class="mt-8 p-8 bg-white rounded-2xl shadow-lg border border-gray-100">
            <h3 class="font-bold text-gray-900 mb-6 text-xl flex items-center gap-2">
              <svg class="w-6 h-6 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              ساعات العمل
            </h3>
            <div class="space-y-3 text-gray-600">
              <div class="flex justify-between items-center py-2 border-b border-gray-100">
                <span>السبت - الخميس</span>
                <span class="font-medium text-gray-900">9:00 ص - 6:00 م</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span>الجمعة</span>
                <span class="font-medium text-red-500">مغلق</span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Contact Form -->
        <div class="order-1 lg:order-2 bg-white p-8 rounded-2xl shadow-xl border border-gray-100">
          <h3 class="text-2xl font-bold text-gray-900 mb-6">أرسل لنا رسالة</h3>
          <form @submit.prevent="submitForm" class="space-y-5">
            <div>
              <label class="block text-gray-700 font-medium mb-2">الاسم الكامل</label>
              <input 
                v-model="form.name" 
                type="text" 
                required
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition bg-gray-50 hover:bg-white"
                placeholder="أدخل اسمك الكامل"
              >
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-gray-700 font-medium mb-2">البريد الإلكتروني</label>
                <input 
                  v-model="form.email" 
                  type="email" 
                  required
                  dir="ltr"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition bg-gray-50 hover:bg-white"
                  placeholder="email@example.com"
                >
              </div>
              
              <div>
                <label class="block text-gray-700 font-medium mb-2">رقم الهاتف</label>
                <input 
                  v-model="form.phone" 
                  type="tel"
                  dir="ltr"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition bg-gray-50 hover:bg-white"
                  placeholder="+966 50 123 4567"
                >
              </div>
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">الموضوع</label>
              <input 
                v-model="form.subject" 
                type="text"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition bg-gray-50 hover:bg-white"
                placeholder="موضوع الرسالة"
              >
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">الرسالة</label>
              <textarea 
                v-model="form.message" 
                required
                rows="5"
                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition resize-none bg-gray-50 hover:bg-white"
                placeholder="اكتب رسالتك هنا..."
              ></textarea>
            </div>
            
            <button type="submit" class="btn-primary w-full flex items-center justify-center gap-2" :disabled="submitting">
              <svg v-if="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
              </svg>
              <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'جاري الإرسال...' : 'إرسال الرسالة' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import axios from '@/utils/axios';

defineProps({
  section: {
    type: Object,
    required: true,
  },
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const submitting = ref(false);
const form = ref({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
});

const submitForm = async () => {
  submitting.value = true;
  
  try {
    await axios.post('api/landing/contact', form.value);
    alert('شكراً لتواصلك معنا! سنرد عليك في أقرب وقت.');
    form.value = {
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: '',
    };
  } catch (error) {
    console.error('Error submitting form:', error);
    alert('فشل في إرسال الرسالة. يرجى المحاولة مرة أخرى.');
  } finally {
    submitting.value = false;
  }
};
</script>
