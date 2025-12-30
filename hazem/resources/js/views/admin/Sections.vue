<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Sections</h1>
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
              <div class="flex items-center space-x-3 mb-2">
                <h3 class="text-lg font-bold text-gray-900">{{ section.title }}</h3>
                <span 
                  class="px-2 py-1 text-xs rounded-full"
                  :class="section.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ section.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>
              
              <p class="text-gray-600 mb-2">{{ section.subtitle }}</p>
              <p class="text-gray-500 text-sm line-clamp-2">{{ section.content }}</p>
            </div>
            
            <button 
              @click="editSection(section)"
              class="ml-4 text-bronze hover:text-bronze-dark transition-colors"
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
          <h2 class="text-2xl font-bold text-gray-900">Edit Section</h2>
        </div>
        
        <form @submit.prevent="saveSection" class="p-6 space-y-6">
          <div>
            <label class="block text-gray-700 font-medium mb-2">Title</label>
            <input 
              v-model="editingSection.title" 
              type="text" 
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
            >
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Subtitle</label>
            <input 
              v-model="editingSection.subtitle" 
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
            >
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Content</label>
            <textarea 
              v-model="editingSection.content" 
              rows="5"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"
            ></textarea>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Background Color</label>
            <input 
              v-model="editingSection.background_color" 
              type="text"
              placeholder="#ffffff"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none"
            >
          </div>
          
          <div class="flex items-center">
            <input 
              v-model="editingSection.is_active" 
              type="checkbox"
              class="w-4 h-4 text-bronze border-gray-300 rounded focus:ring-bronze"
            >
            <label class="ml-2 text-gray-700">Active</label>
          </div>
          
          <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
            <button 
              type="button" 
              @click="editingSection = null"
              class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="btn-primary"
              :disabled="saving"
            >
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from '../../utils/axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const sections = ref([]);
const editingSection = ref(null);

const loadSections = async () => {
  try {
    const response = await axios.get('/api/sections');
    sections.value = response.data;
  } catch (error) {
    toast.error('Error loading sections');
  } finally {
    loading.value = false;
  }
};

const editSection = (section) => {
  editingSection.value = { ...section };
};

const saveSection = async () => {
  saving.value = true;
  try {
    await axios.put(`/api/sections/${editingSection.value.id}`, editingSection.value);
    toast.success('Section updated successfully');
    editingSection.value = null;
    await loadSections();
  } catch (error) {
    toast.error('Error updating section');
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  loadSections();
});
</script>
