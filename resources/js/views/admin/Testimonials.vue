<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Testimonials</h1>
      <button @click="createNew" class="btn-primary">Add Testimonial</button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-if="loading" class="col-span-full p-8 text-center">
        <div class="loading-spinner mx-auto"></div>
      </div>
      
      <div v-for="testimonial in testimonials" :key="testimonial.id" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-gradient-bronze flex items-center justify-center text-white font-bold mr-3">
              {{ testimonial.client_name.charAt(0) }}
            </div>
            <div>
              <h3 class="font-bold text-gray-900">{{ testimonial.client_name }}</h3>
              <p class="text-sm text-gray-600">{{ testimonial.client_position }}</p>
            </div>
          </div>
          
          <span :class="testimonial.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs rounded-full">
            {{ testimonial.is_active ? 'Active' : 'Inactive' }}
          </span>
        </div>
        
        <div class="flex mb-3">
          <svg v-for="i in testimonial.rating" :key="i" class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
          </svg>
        </div>
        
        <p class="text-gray-700 text-sm mb-4 italic">"{{ testimonial.content }}"</p>
        
        <div class="flex justify-end space-x-2">
          <button @click="editTestimonial(testimonial)" class="text-bronze hover:text-bronze-dark">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button @click="deleteTestimonial(testimonial)" class="text-red-600 hover:text-red-900">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
    
    <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900">{{ editing.id ? 'Edit' : 'Add' }} Testimonial</h2>
        </div>
        
        <form @submit.prevent="saveTestimonial" class="p-6 space-y-6">
          <div>
            <label class="block text-gray-700 font-medium mb-2">Client Name *</label>
            <input v-model="editing.client_name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">Position</label>
              <input v-model="editing.client_position" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Company</label>
              <input v-model="editing.client_company" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Testimonial *</label>
            <textarea v-model="editing.content" rows="5" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"></textarea>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Rating</label>
            <select v-model.number="editing.rating" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
              <option :value="5">5 Stars</option>
              <option :value="4">4 Stars</option>
              <option :value="3">3 Stars</option>
              <option :value="2">2 Stars</option>
              <option :value="1">1 Star</option>
            </select>
          </div>
          
          <!-- Media Section Header -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
              <svg class="w-5 h-5 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              Client Media
            </h3>
          </div>
          
          <!-- Client Image Upload -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="block text-gray-700 font-medium mb-3">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Client Photo
              </span>
            </label>
            <div class="flex items-center gap-4">
              <div v-if="editing.client_image" class="relative">
                <img :src="`/crm/storage/${editing.client_image}`" class="w-24 h-24 rounded-full object-cover border-4 border-bronze shadow-lg">
                <button @click="removeImage" type="button" class="absolute -top-2 -right-2 bg-red-600 text-white p-1.5 rounded-full shadow-lg hover:bg-red-700 transition-colors">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
              <div v-else class="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center border-4 border-dashed border-gray-300">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
              <div class="flex-1">
                <label class="block w-full cursor-pointer">
                  <span class="block px-4 py-3 bg-white border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-bronze transition-colors">
                    <svg class="w-6 h-6 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span class="text-sm text-gray-600">Click to upload photo</span>
                  </span>
                  <input @change="uploadImage" type="file" accept="image/*" class="hidden">
                </label>
              </div>
            </div>
          </div>
          
          <!-- Client Video Upload -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <label class="block text-gray-700 font-medium mb-3">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-bronze" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                </svg>
                Video Testimonial (Optional)
              </span>
            </label>
            <div v-if="editing.client_video" class="flex items-center gap-4 p-4 bg-white rounded-lg border border-gray-200 mb-3">
              <div class="w-16 h-16 bg-gray-900 rounded-lg flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">Video uploaded</p>
                <p class="text-xs text-gray-500 truncate">{{ editing.client_video }}</p>
              </div>
              <button @click="removeVideo" type="button" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
            <label class="block w-full cursor-pointer">
              <span class="block px-4 py-4 bg-white border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-bronze transition-colors">
                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <span class="text-sm text-gray-600">Click to upload video testimonial</span>
                <span class="block text-xs text-gray-400 mt-1">MP4, MOV, AVI (Max 50MB)</span>
              </span>
              <input @change="uploadVideo" type="file" accept="video/*" class="hidden">
            </label>
          </div>
          
          <div class="flex items-center">
            <input v-model="editing.is_active" type="checkbox" class="w-4 h-4 text-bronze border-gray-300 rounded focus:ring-bronze">
            <label class="ml-2 text-gray-700">Active</label>
          </div>
          
          <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
            <button type="button" @click="editing = null" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
            <button type="submit" class="btn-primary" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
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
const testimonials = ref([]);
const editing = ref(null);

const loadTestimonials = async () => {
  try {
    const response = await axios.get('api/landing/testimonials');
    testimonials.value = response.data;
  } catch (error) {
    alert('Error loading testimonials');
  } finally {
    loading.value = false;
  }
};

const createNew = () => {
  editing.value = {
    client_name: '',
    client_position: '',
    client_company: '',
    content: '',
    rating: 5,
    is_active: true,
  };
};

const editTestimonial = (testimonial) => {
  editing.value = { ...testimonial };
};

const saveTestimonial = async () => {
  saving.value = true;
  try {
    if (editing.value.id) {
      await axios.put(`api/landing/testimonials/${editing.value.id}`, editing.value);
      alert('Testimonial updated');
    } else {
      await axios.post('api/landing/testimonials', editing.value);
      alert('Testimonial created');
    }
    editing.value = null;
    await loadTestimonials();
  } catch (error) {
    alert('Error saving testimonial');
  } finally {
    saving.value = false;
  }
};

const deleteTestimonial = async (testimonial) => {
  if (!confirm('Are you sure you want to delete this testimonial?')) return;
  
  try {
    await axios.delete(`api/landing/testimonials/${testimonial.id}`);
    alert('Testimonial deleted');
    await loadTestimonials();
  } catch (error) {
    alert('Error deleting testimonial');
  }
};

const uploadImage = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', 'testimonial_image');
  
  try {
    const response = await axios.post('api/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    editing.value.client_image = response.data.path;
    alert('Image uploaded');
  } catch (error) {
    alert('Error uploading image');
  }
};

const uploadVideo = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', 'testimonial_video');
  
  try {
    const response = await axios.post('api/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    editing.value.client_video = response.data.path;
    alert('Video uploaded');
  } catch (error) {
    alert('Error uploading video');
  }
};

const removeImage = () => {
  editing.value.client_image = null;
};

const removeVideo = () => {
  editing.value.client_video = null;
};

onMounted(() => {
  loadTestimonials();
});
</script>
