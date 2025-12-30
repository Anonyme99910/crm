<template>
  <section id="testimonials" class="section-padding bg-gray-50">
    <div class="container-custom">
      <div class="text-center mb-16">
        <h3 class="text-bronze text-lg font-semibold mb-2">{{ section.subtitle }}</h3>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900">{{ section.title }}</h2>
        <p class="text-gray-600 mt-4 max-w-2xl mx-auto">{{ section.content }}</p>
      </div>
      
      <div v-if="testimonials.length > 0" class="relative">
        <div class="overflow-hidden">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div 
              v-for="testimonial in testimonials" 
              :key="testimonial.id"
              class="bg-white p-8 rounded-lg shadow-lg hover-lift"
            >
              <!-- Video testimonial -->
              <div v-if="testimonial.client_video" class="mb-4 rounded-lg overflow-hidden">
                <video 
                  :src="getMediaUrl(testimonial.client_video)" 
                  controls 
                  class="w-full h-48 object-cover bg-black"
                  preload="metadata"
                ></video>
              </div>
              
              <div class="flex items-center mb-4">
                <div v-for="i in 5" :key="i" class="text-yellow-400">
                  <svg v-if="i <= testimonial.rating" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                  </svg>
                  <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                  </svg>
                </div>
              </div>
              
              <p class="text-gray-700 mb-6 italic">"{{ testimonial.content }}"</p>
              
              <div class="flex items-center">
                <div v-if="testimonial.client_image" class="w-12 h-12 rounded-full overflow-hidden mr-4">
                  <img 
                    :src="getMediaUrl(testimonial.client_image)" 
                    :alt="testimonial.client_name" 
                    class="w-full h-full object-cover"
                    @error="handleImageError"
                  >
                </div>
                <div v-else class="w-12 h-12 rounded-full bg-gradient-bronze flex items-center justify-center text-white font-bold mr-4">
                  {{ testimonial.client_name.charAt(0) }}
                </div>
                
                <div>
                  <h4 class="font-bold text-gray-900">{{ testimonial.client_name }}</h4>
                  <p class="text-sm text-gray-600">
                    <span v-if="testimonial.client_position">{{ testimonial.client_position }}</span>
                    <span v-if="testimonial.client_position && testimonial.client_company"> at </span>
                    <span v-if="testimonial.client_company">{{ testimonial.client_company }}</span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-12">
        <p class="text-gray-500 text-lg">No testimonials available yet.</p>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  section: {
    type: Object,
    required: true,
  },
  testimonials: {
    type: Array,
    default: () => [],
  },
});

const getMediaUrl = (path) => {
  if (!path) return '';
  if (path.startsWith('http')) return path;
  return `/hazem/storage/${path}`;
};

const handleImageError = (event) => {
  event.target.style.display = 'none';
};
</script>
