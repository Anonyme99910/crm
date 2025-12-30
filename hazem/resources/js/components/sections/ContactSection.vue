<template>
  <section id="contact" class="section-padding bg-white">
    <div class="container-custom">
      <div class="text-center mb-16">
        <h3 class="text-bronze text-lg font-semibold mb-2">{{ section.subtitle }}</h3>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900">{{ section.title }}</h2>
        <p class="text-gray-600 mt-4 max-w-2xl mx-auto">{{ section.content }}</p>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <div>
          <div class="space-y-6">
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-gradient-bronze rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 mb-1">Email</h3>
                <p class="text-gray-600">{{ settings.contact_email || 'info@hazemabdullah.com' }}</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-gradient-bronze rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 mb-1">Phone</h3>
                <p class="text-gray-600">{{ settings.contact_phone || '+1 234 567 8900' }}</p>
              </div>
            </div>
            
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0 w-12 h-12 bg-gradient-bronze rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 mb-1">Address</h3>
                <p class="text-gray-600">{{ settings.contact_address || '123 Design Street, City, Country' }}</p>
              </div>
            </div>
          </div>
          
          <div class="mt-8 p-6 bg-primary-50 rounded-lg">
            <h3 class="font-bold text-gray-900 mb-4">Business Hours</h3>
            <div class="space-y-2 text-gray-600">
              <p>Monday - Friday: 9:00 AM - 6:00 PM</p>
              <p>Saturday: 10:00 AM - 4:00 PM</p>
              <p>Sunday: Closed</p>
            </div>
          </div>
        </div>
        
        <div class="bg-gray-50 p-8 rounded-lg">
          <form @submit.prevent="submitForm" class="space-y-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">Name</label>
              <input 
                v-model="form.name" 
                type="text" 
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition"
                placeholder="Your Name"
              >
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Email</label>
              <input 
                v-model="form.email" 
                type="email" 
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition"
                placeholder="your@email.com"
              >
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Phone</label>
              <input 
                v-model="form.phone" 
                type="tel"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition"
                placeholder="+1 234 567 8900"
              >
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Subject</label>
              <input 
                v-model="form.subject" 
                type="text"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition"
                placeholder="Project inquiry"
              >
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Message</label>
              <textarea 
                v-model="form.message" 
                required
                rows="5"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none transition resize-none"
                placeholder="Tell us about your project..."
              ></textarea>
            </div>
            
            <button type="submit" class="btn-primary w-full" :disabled="submitting">
              {{ submitting ? 'Sending...' : 'Send Message' }}
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
    await axios.post('api/contact', form.value);
    alert('Thank you for contacting us! We will get back to you soon.');
    form.value = {
      name: '',
      email: '',
      phone: '',
      subject: '',
      message: '',
    };
  } catch (error) {
    console.error('Error submitting form:', error);
    alert('Failed to send message. Please try again.');
  } finally {
    submitting.value = false;
  }
};
</script>
