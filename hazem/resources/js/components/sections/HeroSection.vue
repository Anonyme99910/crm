<template>
  <section id="home" class="relative min-h-screen flex items-center justify-center overflow-hidden" :style="{ backgroundColor: section.background_color || '#f9ede0' }">
    <div v-if="section.background_image" class="absolute inset-0 parallax-bg" :style="{ backgroundImage: `url(${section.background_image})` }"></div>
    
    <div class="absolute inset-0 bg-gradient-to-br from-bronze/20 to-transparent"></div>
    
    <!-- Animated background elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-bronze/10 rounded-full blur-3xl animate-float"></div>
    <div class="absolute bottom-20 right-10 w-96 h-96 bg-bronze-light/10 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
    <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-bronze/5 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>
    
    <div class="container-custom px-4 md:px-8 relative z-10">
      <div class="text-center animate-slide-up">
        <div class="mb-8">
          <img :src="logoSrc" alt="Hazem Abdullah" 
               class="h-32 md:h-40 w-auto mx-auto animate-scale-in drop-shadow-2xl hover:scale-105 transition-transform duration-500 cursor-pointer"
               @click="animateLogo">
        </div>
        
        <h1 class="text-5xl md:text-7xl lg:text-8xl font-bold text-gradient mb-4 animate-fade-in" 
            style="animation-delay: 0.2s;"
            @mouseenter="addGlow"
            @mouseleave="removeGlow">
          <span :class="{ 'text-glow': isGlowing }">{{ section.title }}</span>
        </h1>
        
        <p class="text-2xl md:text-3xl text-bronze-dark mb-8 font-serif animate-fade-in" 
           style="animation-delay: 0.4s;">
          <span class="inline-block hover:scale-105 transition-transform duration-300">{{ section.subtitle }}</span>
        </p>
        
        <p class="text-lg md:text-xl text-gray-700 max-w-2xl mx-auto mb-12 animate-fade-in" 
           style="animation-delay: 0.6s;">
          {{ section.content }}
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center animate-fade-in" 
             style="animation-delay: 0.8s;">
          <a href="#portfolio" 
             class="btn-primary transform hover:scale-105 hover:shadow-xl transition-all duration-300 group">
            <span class="group-hover:animate-pulse">View Portfolio</span>
          </a>
          <a href="#contact" 
             class="btn-secondary transform hover:scale-105 hover:shadow-xl transition-all duration-300 group">
            <span class="group-hover:animate-pulse">Get In Touch</span>
          </a>
        </div>
      </div>
    </div>
    
    <!-- Animated scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
      <a href="#about" class="text-bronze hover:text-bronze-dark transition-colors duration-300">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
        </svg>
      </a>
    </div>
  </section>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  section: {
    type: Object,
    required: true,
  },
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const logoSrc = computed(() => {
  const path = props.settings?.site_logo;
  if (path) {
    if (String(path).startsWith('http')) return path;
    return `/hazem/storage/${path}`;
  }
  return '/hazem/logo.png';
});

const isGlowing = ref(false);

const addGlow = () => {
  isGlowing.value = true;
};

const removeGlow = () => {
  isGlowing.value = false;
};

const animateLogo = () => {
  const logo = event.target;
  logo.classList.remove('animate-scale-in');
  void logo.offsetWidth; // Trigger reflow
  logo.classList.add('animate-scale-in');
};
</script>

<style scoped>
.text-glow {
  filter: drop-shadow(0 0 20px rgba(184, 130, 74, 0.5));
  transition: filter 0.3s ease;
}

.parallax-bg {
  background-attachment: fixed;
  background-position: center;
  background-repeat: no-repeat;
  background-size: cover;
}
</style>
