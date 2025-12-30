<template>
  <div class="portfolio-detail">
    <Navbar />
    
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="loading-spinner"></div>
    </div>
    
    <div v-else-if="portfolio" class="pt-20">
      <div class="section-padding bg-gray-50">
        <div class="container-custom">
          <button @click="$router.push('/')" class="flex items-center text-bronze hover:text-bronze-dark mb-8 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Home
          </button>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 mb-12">
            <div>
              <div v-if="portfolio.media && portfolio.media.length > 0" class="space-y-4">
                <div class="aspect-[4/3] rounded-lg overflow-hidden shadow-2xl">
                  <img 
                    v-if="currentMedia.type === 'image'"
                    :src="`/hazem/storage/${currentMedia.path}`" 
                    :alt="portfolio.title"
                    class="w-full h-full object-cover"
                  >
                  <video 
                    v-else
                    :src="`/hazem/storage/${currentMedia.path}`"
                    controls
                    class="w-full h-full object-cover"
                  ></video>
                </div>
                
                <div v-if="portfolio.media.length > 1" class="grid grid-cols-4 gap-4">
                  <div 
                    v-for="(media, index) in portfolio.media" 
                    :key="media.id"
                    @click="currentMediaIndex = index"
                    class="aspect-square rounded-lg overflow-hidden cursor-pointer border-2 transition-all"
                    :class="currentMediaIndex === index ? 'border-bronze' : 'border-transparent hover:border-bronze/50'"
                  >
                    <img 
                      v-if="media.type === 'image'"
                      :src="`/hazem/storage/${media.path}`" 
                      :alt="portfolio.title"
                      class="w-full h-full object-cover"
                    >
                    <div v-else class="w-full h-full bg-gray-900 flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="aspect-[4/3] rounded-lg bg-gradient-bronze flex items-center justify-center">
                <svg class="w-24 h-24 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                </svg>
              </div>
            </div>
            
            <div>
              <div class="inline-block px-4 py-2 bg-bronze/10 text-bronze rounded-full text-sm font-semibold mb-4">
                {{ portfolio.category }}
              </div>
              
              <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">{{ portfolio.title }}</h1>
              
              <div class="prose prose-lg max-w-none mb-8">
                <p class="text-gray-700">{{ portfolio.description }}</p>
              </div>
              
              <div class="grid grid-cols-2 gap-6 mb-8">
                <div v-if="portfolio.client">
                  <h3 class="text-sm font-semibold text-gray-500 mb-2">Client</h3>
                  <p class="text-gray-900">{{ portfolio.client }}</p>
                </div>
                
                <div v-if="portfolio.project_date">
                  <h3 class="text-sm font-semibold text-gray-500 mb-2">Date</h3>
                  <p class="text-gray-900">{{ formatDate(portfolio.project_date) }}</p>
                </div>
                
                <div v-if="portfolio.location">
                  <h3 class="text-sm font-semibold text-gray-500 mb-2">Location</h3>
                  <p class="text-gray-900">{{ portfolio.location }}</p>
                </div>
              </div>
              
              <div v-if="portfolio.details" class="bg-gray-50 p-6 rounded-lg">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Project Details</h3>
                <p class="text-gray-700 whitespace-pre-line">{{ portfolio.details }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <Footer :settings="{}" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from '../utils/axios';
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';

const route = useRoute();
const loading = ref(true);
const portfolio = ref(null);
const currentMediaIndex = ref(0);

const currentMedia = computed(() => {
  if (portfolio.value?.media && portfolio.value.media.length > 0) {
    return portfolio.value.media[currentMediaIndex.value];
  }
  return null;
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long' });
};

onMounted(async () => {
  try {
    const response = await axios.get(`/api/portfolio/${route.params.id}`);
    portfolio.value = response.data;
  } catch (error) {
    console.error('Error loading portfolio:', error);
  } finally {
    loading.value = false;
  }
});
</script>
