<template>
  <section id="portfolio" class="section-padding bg-gray-50">
    <div class="container-custom">
      <div class="text-center mb-16 animate-fade-in">
        <h3 class="text-bronze text-lg font-semibold mb-2">{{ section.subtitle }}</h3>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900">{{ section.title }}</h2>
        <p class="text-gray-600 mt-4 max-w-2xl mx-auto">{{ section.content }}</p>
      </div>
      
      <div class="flex justify-center mb-12 flex-wrap gap-4">
        <button 
          @click="selectedCategory = 'all'" 
          :class="selectedCategory === 'all' ? 'btn-primary' : 'btn-secondary'"
          class="px-6 py-2 transform hover:scale-105 transition-all duration-300"
        >
          All
        </button>
        <button 
          v-for="category in categories" 
          :key="category"
          @click="selectedCategory = category" 
          :class="selectedCategory === category ? 'btn-primary' : 'btn-secondary'"
          class="px-6 py-2 transform hover:scale-105 transition-all duration-300"
        >
          {{ category }}
        </button>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div 
          v-for="(item, index) in filteredItems" 
          :key="item.id"
          class="group cursor-pointer hover-lift bg-white rounded-lg overflow-hidden shadow-lg"
          :style="{ animationDelay: `${index * 0.1}s` }"
          @click="openPortfolio(item)"
          @mouseenter="addHoverEffect"
          @mouseleave="removeHoverEffect"
        >
          <div class="image-overlay aspect-[4/3] relative overflow-hidden">
            <img 
              v-if="item.media && item.media.length > 0"
              :src="getMediaUrl(item.media[0].path)" 
              :alt="item.title"
              class="w-full h-full object-cover transform transition-transform duration-700 group-hover:scale-110"
              loading="lazy"
              @error="handleImageError"
            >
            <div v-else class="w-full h-full bg-gradient-bronze flex items-center justify-center">
              <svg class="w-16 h-16 text-white/30" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
              </svg>
            </div>
            
            <!-- Enhanced overlay with more details -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-500 flex items-end p-6">
              <div class="text-white transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                <h3 class="text-xl font-bold mb-2">{{ item.title }}</h3>
                <p class="text-sm text-gray-200 mb-2">{{ item.category }}</p>
                <p class="text-xs text-gray-300 line-clamp-2">{{ item.description }}</p>
                <div class="mt-3 flex items-center text-white text-sm font-semibold">
                  View Project
                  <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
              </div>
            </div>
            
            <!-- Category badge -->
            <div class="absolute top-4 right-4 bg-bronze text-white px-3 py-1 rounded-full text-xs font-semibold opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              {{ item.category }}
            </div>
          </div>
          
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-bronze transition-colors duration-300">{{ item.title }}</h3>
            <p class="text-gray-600 text-sm line-clamp-2">{{ item.description }}</p>
            <div class="mt-4 flex items-center justify-between">
              <div class="flex items-center text-bronze text-sm font-semibold group-hover:text-bronze-dark transition-colors duration-300">
                View Details
                <svg class="w-4 h-4 ml-2 group-hover:translate-x-2 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </div>
              <div v-if="item.is_featured" class="bg-bronze/10 text-bronze px-2 py-1 rounded text-xs font-semibold">
                Featured
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-if="filteredItems.length === 0" class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
          </svg>
        </div>
        <p class="text-gray-500 text-lg">No portfolio items found in this category.</p>
        <p class="text-gray-400 text-sm mt-2">Try selecting a different category or check back later.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const props = defineProps({
  section: {
    type: Object,
    required: true,
  },
  items: {
    type: Array,
    default: () => [],
  },
});

const router = useRouter();
const selectedCategory = ref('all');

const categories = computed(() => {
  const cats = new Set();
  props.items.forEach(item => {
    if (item.category) {
      cats.add(item.category);
    }
  });
  return Array.from(cats);
});

const filteredItems = computed(() => {
  if (selectedCategory.value === 'all') {
    return props.items;
  }
  return props.items.filter(item => item.category === selectedCategory.value);
});

const openPortfolio = (item) => {
  router.push({ name: 'PortfolioDetail', params: { id: item.id } });
};

const getMediaUrl = (path) => {
  if (!path) return '';
  // Handle both old paths and new storage paths
  if (path.startsWith('http')) return path;
  return `/hazem/storage/${path}`;
};

const handleImageError = (event) => {
  // Hide broken image and show placeholder
  event.target.style.display = 'none';
  event.target.parentElement.classList.add('show-placeholder');
};

const addHoverEffect = (event) => {
  const card = event.currentTarget;
  card.style.transform = 'translateY(-5px)';
};

const removeHoverEffect = (event) => {
  const card = event.currentTarget;
  card.style.transform = 'translateY(0)';
};

onMounted(() => {
  // Add staggered animation to portfolio items
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry, index) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('animate-fade-in');
        }, index * 100);
      }
    });
  }, { threshold: 0.1 });

  // Observe portfolio items when they come into view
  setTimeout(() => {
    const portfolioItems = document.querySelectorAll('#portfolio .grid > div');
    portfolioItems.forEach(item => observer.observe(item));
  }, 100);
});
</script>
