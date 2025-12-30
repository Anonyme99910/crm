<template>
  <section id="about" class="section-padding bg-white">
    <div class="container-custom">
      <div class="text-center mb-16">
        <h3 class="text-bronze text-lg font-semibold mb-2">{{ section.subtitle }}</h3>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900">{{ section.title }}</h2>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
        <div v-if="section.background_image" class="relative">
          <div class="absolute -top-6 -left-6 w-full h-full border-4 border-bronze/20 rounded-lg"></div>
          <img :src="section.background_image" alt="About" class="relative rounded-lg shadow-2xl w-full h-auto">
        </div>
        <div v-else class="relative">
          <div class="absolute -top-6 -left-6 w-full h-full border-4 border-bronze/20 rounded-lg"></div>
          <div class="relative bg-gradient-bronze rounded-lg shadow-2xl h-96 flex items-center justify-center">
            <svg class="w-32 h-32 text-white/30" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
            </svg>
          </div>
        </div>
        
        <div>
          <div class="prose prose-lg max-w-none">
            <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ section.content }}</p>
          </div>
          
          <div class="grid grid-cols-2 gap-6 mt-8">
            <div class="text-center p-6 bg-primary-50 rounded-lg">
              <div class="text-4xl font-bold text-bronze mb-2">10+</div>
              <div class="text-gray-600">Years Experience</div>
            </div>
            <div class="text-center p-6 bg-primary-50 rounded-lg">
              <div class="text-4xl font-bold text-bronze mb-2">200+</div>
              <div class="text-gray-600">Projects Completed</div>
            </div>
          </div>
        </div>
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
});
</script>
