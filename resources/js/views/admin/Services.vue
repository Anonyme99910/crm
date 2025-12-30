<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Services</h1>
      <button @click="createNew" class="btn-primary">Add Service</button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-if="loading" class="col-span-full p-8 text-center">
        <div class="loading-spinner mx-auto"></div>
      </div>
      
      <div v-for="service in services" :key="service.id" class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
        <div class="flex justify-between items-start mb-4">
          <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ service.title }}</h3>
            <span :class="service.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs rounded-full">
              {{ service.is_active ? 'Active' : 'Inactive' }}
            </span>
          </div>
        </div>
        
        <p class="text-gray-600 text-sm mb-4">{{ service.description }}</p>
        
        <div class="flex justify-end space-x-2">
          <button @click="editService(service)" class="text-bronze hover:text-bronze-dark">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
          </button>
          <button @click="deleteService(service)" class="text-red-600 hover:text-red-900">
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
          <h2 class="text-2xl font-bold text-gray-900">{{ editing.id ? 'Edit' : 'Add' }} Service</h2>
        </div>
        
        <form @submit.prevent="saveService" class="p-6 space-y-6">
          <div>
            <label class="block text-gray-700 font-medium mb-2">Title *</label>
            <input v-model="editing.title" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Description *</label>
            <textarea v-model="editing.description" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"></textarea>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Icon</label>
            <select v-model="editing.icon" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
              <option value="home">Home</option>
              <option value="layout">Layout</option>
              <option value="sofa">Sofa</option>
              <option value="palette">Palette</option>
            </select>
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
const services = ref([]);
const editing = ref(null);

const loadServices = async () => {
  try {
    const response = await axios.get('/api/landing/services');
    services.value = response.data;
  } catch (error) {
    alert('Error loading services');
  } finally {
    loading.value = false;
  }
};

const createNew = () => {
  editing.value = {
    title: '',
    description: '',
    icon: 'home',
    is_active: true,
  };
};

const editService = (service) => {
  editing.value = { ...service };
};

const saveService = async () => {
  saving.value = true;
  try {
    if (editing.value.id) {
      await axios.put(`/api/landing/services/${editing.value.id}`, editing.value);
      alert('Service updated');
    } else {
      await axios.post('/api/landing/services', editing.value);
      alert('Service created');
    }
    editing.value = null;
    await loadServices();
  } catch (error) {
    alert('Error saving service');
  } finally {
    saving.value = false;
  }
};

const deleteService = async (service) => {
  if (!confirm('Are you sure you want to delete this service?')) return;
  
  try {
    await axios.delete(`/api/landing/services/${service.id}`);
    alert('Service deleted');
    await loadServices();
  } catch (error) {
    alert('Error deleting service');
  }
};

onMounted(() => {
  loadServices();
});
</script>
