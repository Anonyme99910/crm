<template>
  <div>
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Portfolio</h1>
      <button @click="createNew" class="btn-primary">Add Portfolio Item</button>
    </div>
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="loading-spinner mx-auto"></div>
      </div>
      
      <table v-else class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Media</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ item.title }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-500">{{ item.category || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                {{ item.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ item.media?.length || 0 }} files
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editItem(item)" class="text-bronze hover:text-bronze-dark mr-4">Edit</button>
              <button @click="deleteItem(item)" class="text-red-600 hover:text-red-900">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900">{{ editing.id ? 'Edit' : 'Add' }} Portfolio Item</h2>
        </div>
        
        <form @submit.prevent="saveItem" class="p-6 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">Title *</label>
              <input v-model="editing.title" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Category</label>
              <input v-model="editing.category" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Description</label>
            <textarea v-model="editing.description" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"></textarea>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-gray-700 font-medium mb-2">Client</label>
              <input v-model="editing.client" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Project Date</label>
              <input v-model="editing.project_date" type="date" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
            
            <div>
              <label class="block text-gray-700 font-medium mb-2">Location</label>
              <input v-model="editing.location" type="text" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none">
            </div>
          </div>
          
          <div>
            <label class="block text-gray-700 font-medium mb-2">Project Details</label>
            <textarea v-model="editing.details" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bronze focus:border-transparent outline-none resize-none"></textarea>
          </div>
          
          <div class="flex items-center space-x-6">
            <label class="flex items-center">
              <input v-model="editing.is_featured" type="checkbox" class="w-4 h-4 text-bronze border-gray-300 rounded focus:ring-bronze">
              <span class="ml-2 text-gray-700">Featured</span>
            </label>
            
            <label class="flex items-center">
              <input v-model="editing.is_active" type="checkbox" class="w-4 h-4 text-bronze border-gray-300 rounded focus:ring-bronze">
              <span class="ml-2 text-gray-700">Active</span>
            </label>
          </div>
          
          <!-- Media Files Section -->
          <div>
            <label class="block text-gray-700 font-medium mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-5 h-5 text-bronze" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Media Files (Images & Videos)
              </span>
            </label>
            
            <!-- Show existing media if editing -->
            <div v-if="editing.id && editing.media && editing.media.length > 0" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
              <div v-for="media in editing.media" :key="media.id" class="relative group">
                <img v-if="media.type === 'image'" :src="`/hazem/storage/${media.path}`" class="w-full h-32 object-cover rounded-lg">
                <div v-else class="w-full h-32 bg-gray-900 rounded-lg flex items-center justify-center">
                  <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                  </svg>
                </div>
                <button @click="deleteMedia(media)" type="button" class="absolute top-2 right-2 bg-red-600 text-white p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
            
            <!-- Upload input - always visible -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-bronze transition-colors">
              <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
              </svg>
              <p class="text-gray-600 mb-2">Drag & drop files here or click to browse</p>
              <p class="text-sm text-gray-400">Supports: JPG, PNG, GIF, MP4, MOV (Max 50MB)</p>
              <input 
                v-if="editing.id"
                @change="uploadMedia" 
                type="file" 
                accept="image/*,video/*" 
                multiple 
                class="w-full mt-4 px-4 py-3 border border-gray-300 rounded-lg cursor-pointer"
              >
              <p v-else class="text-sm text-amber-600 mt-3 font-medium">
                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Save the portfolio item first, then you can upload media files
              </p>
            </div>
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
import axios from '../../utils/axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const loading = ref(true);
const saving = ref(false);
const items = ref([]);
const editing = ref(null);

const loadItems = async () => {
  try {
    const response = await axios.get('api/portfolio');
    items.value = response.data;
  } catch (error) {
    toast.error('Error loading portfolio items');
  } finally {
    loading.value = false;
  }
};

const createNew = () => {
  editing.value = {
    title: '',
    description: '',
    category: '',
    client: '',
    project_date: '',
    location: '',
    details: '',
    is_featured: false,
    is_active: true,
    media: [],
  };
};

const editItem = (item) => {
  editing.value = { ...item };
};

const saveItem = async () => {
  saving.value = true;
  try {
    if (editing.value.id) {
      await axios.put(`api/portfolio/${editing.value.id}`, editing.value);
      toast.success('Portfolio item updated');
    } else {
      await axios.post('api/portfolio', editing.value);
      toast.success('Portfolio item created');
    }
    editing.value = null;
    await loadItems();
  } catch (error) {
    toast.error('Error saving portfolio item');
  } finally {
    saving.value = false;
  }
};

const deleteItem = async (item) => {
  if (!confirm('Are you sure you want to delete this item?')) return;
  
  try {
    await axios.delete(`api/portfolio/${item.id}`);
    toast.success('Portfolio item deleted');
    await loadItems();
  } catch (error) {
    toast.error('Error deleting portfolio item');
  }
};

const uploadMedia = async (event) => {
  const files = event.target.files;
  if (!files.length) return;
  
  for (const file of files) {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('mediable_type', 'App\\Models\\PortfolioItem');
    formData.append('mediable_id', editing.value.id);
    
    try {
      await axios.post('api/media', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
    } catch (error) {
      toast.error(`Error uploading ${file.name}`);
    }
  }
  
  toast.success('Media uploaded successfully');
  const response = await axios.get(`api/portfolio/${editing.value.id}`);
  editing.value = response.data;
};

const deleteMedia = async (media) => {
  if (!confirm('Delete this media file?')) return;
  
  try {
    await axios.delete(`api/media/${media.id}`);
    editing.value.media = editing.value.media.filter(m => m.id !== media.id);
    toast.success('Media deleted');
  } catch (error) {
    toast.error('Error deleting media');
  }
};

onMounted(() => {
  loadItems();
});
</script>
