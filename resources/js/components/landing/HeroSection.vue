<template>
  <section id="home" class="relative min-h-screen flex items-center justify-center overflow-hidden" :style="{ backgroundColor: section.background_color || '#f9ede0' }">
    <!-- Video Background -->
    <div v-if="videoSrc" class="absolute inset-0 w-full h-full overflow-hidden">
      <!-- Loading placeholder while video loads -->
      <div v-if="!videoLoaded" class="absolute inset-0 bg-gradient-to-br from-bronze/30 to-bronze-dark/20 flex items-center justify-center">
        <div class="text-center">
          <div class="w-16 h-16 border-4 border-bronze border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
          <p class="text-bronze-dark text-lg">جاري تحميل الفيديو...</p>
        </div>
      </div>
      <video 
        ref="heroVideo"
        class="absolute min-w-full min-h-full w-auto h-auto object-cover transition-opacity duration-500"
        :class="{ 'opacity-0': !videoLoaded, 'opacity-100': videoLoaded }"
        style="top: 50%; left: 50%; transform: translate(-50%, -50%);"
        autoplay 
        muted 
        loop 
        playsinline
        preload="auto"
        @loadeddata="onVideoLoaded"
        @canplay="onVideoCanPlay"
      >
        <source :src="videoSrc" type="video/mp4">
      </video>
      <div class="absolute inset-0 bg-black/40"></div>
    </div>
    
    <!-- Image Background (fallback) -->
    <div v-else-if="section.background_image" class="absolute inset-0 parallax-bg" :style="{ backgroundImage: `url(${section.background_image})` }"></div>
    
    <div class="absolute inset-0 bg-gradient-to-br from-bronze/20 to-transparent"></div>
    
    <!-- Animated background elements (only show when no video) -->
    <div v-if="!videoSrc" class="absolute top-20 left-10 w-72 h-72 bg-bronze/10 rounded-full blur-3xl animate-float"></div>
    <div v-if="!videoSrc" class="absolute bottom-20 right-10 w-96 h-96 bg-bronze-light/10 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
    <div v-if="!videoSrc" class="absolute top-1/3 right-1/4 w-64 h-64 bg-bronze/5 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>
    
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
             class="btn-primary transform hover:scale-105 hover:shadow-xl transition-all duration-300 group flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="group-hover:animate-pulse">عرض الأعمال</span>
          </a>
          <a href="#contact" 
             class="btn-secondary transform hover:scale-105 hover:shadow-xl transition-all duration-300 group flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="group-hover:animate-pulse">تواصل معنا</span>
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
import { ref, computed, onMounted } from 'vue';

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

const heroVideo = ref(null);
const videoLoaded = ref(false);

const logoSrc = computed(() => {
  const path = props.settings?.site_logo;
  if (path) {
    if (String(path).startsWith('http')) return path;
    return `/storage/${path}`;
  }
  return '/logo.png';
});

const videoSrc = computed(() => {
  const path = props.section?.background_video;
  console.log('Video path from section:', path);
  if (path) {
    if (String(path).startsWith('http')) return path;
    const fullPath = path.startsWith('/') ? path : `/storage/${path}`;
    console.log('Full video URL:', fullPath);
    return fullPath;
  }
  return null;
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

const onVideoLoaded = () => {
  console.log('Video data loaded');
  if (heroVideo.value) {
    heroVideo.value.play();
  }
};

const onVideoCanPlay = () => {
  console.log('Video can play');
  videoLoaded.value = true;
  if (heroVideo.value) {
    heroVideo.value.play();
  }
};

onMounted(() => {
  // Ensure video starts from beginning on page load
  if (heroVideo.value) {
    heroVideo.value.currentTime = 0;
    heroVideo.value.play();
  }
});
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
