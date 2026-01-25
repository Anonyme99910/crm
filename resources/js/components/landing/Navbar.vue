<template>
  <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-500" :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-2xl' : 'bg-transparent'">
    <div class="container-custom px-4 md:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <a href="/" class="flex items-center gap-3 group">
          <img :src="logoSrc" alt="حازم عبدالله" class="h-14 w-auto transition-all duration-500 group-hover:scale-110 group-hover:drop-shadow-lg">
          <span class="hidden sm:block text-xl font-bold transition-colors duration-300" :class="scrolled ? 'text-bronze' : 'text-white'">
            {{ settings.site_title || 'حازم عبدالله' }}
          </span>
        </a>
        
        <!-- Desktop Menu -->
        <div class="hidden md:flex items-center gap-8">
          <a href="#home" class="nav-link relative group" :class="scrolled ? 'text-gray-800' : 'text-white'">
            الرئيسية
            <span class="absolute -bottom-1 right-0 w-0 h-0.5 bg-bronze transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#about" class="nav-link relative group" :class="scrolled ? 'text-gray-800' : 'text-white'">
            من نحن
            <span class="absolute -bottom-1 right-0 w-0 h-0.5 bg-bronze transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#portfolio" class="nav-link relative group" :class="scrolled ? 'text-gray-800' : 'text-white'">
            أعمالنا
            <span class="absolute -bottom-1 right-0 w-0 h-0.5 bg-bronze transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#services" class="nav-link relative group" :class="scrolled ? 'text-gray-800' : 'text-white'">
            خدماتنا
            <span class="absolute -bottom-1 right-0 w-0 h-0.5 bg-bronze transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#testimonials" class="nav-link relative group" :class="scrolled ? 'text-gray-800' : 'text-white'">
            آراء العملاء
            <span class="absolute -bottom-1 right-0 w-0 h-0.5 bg-bronze transition-all duration-300 group-hover:w-full"></span>
          </a>
          <a href="#contact" class="btn-primary flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            تواصل معنا
          </a>
        </div>
        
        <!-- Mobile Menu Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg transition-colors" :class="scrolled ? 'text-gray-800 hover:bg-gray-100' : 'text-white hover:bg-white/10'">
          <svg class="w-6 h-6 transition-transform duration-300" :class="mobileMenuOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <!-- Mobile Menu -->
    <transition name="slide-down">
      <div v-if="mobileMenuOpen" class="md:hidden bg-white/95 backdrop-blur-md shadow-2xl border-t border-gray-100">
        <div class="px-6 py-8 space-y-4">
          <a href="#home" @click="mobileMenuOpen = false" class="block text-lg font-medium text-gray-800 hover:text-bronze transition-colors py-2 border-b border-gray-100">الرئيسية</a>
          <a href="#about" @click="mobileMenuOpen = false" class="block text-lg font-medium text-gray-800 hover:text-bronze transition-colors py-2 border-b border-gray-100">من نحن</a>
          <a href="#portfolio" @click="mobileMenuOpen = false" class="block text-lg font-medium text-gray-800 hover:text-bronze transition-colors py-2 border-b border-gray-100">أعمالنا</a>
          <a href="#services" @click="mobileMenuOpen = false" class="block text-lg font-medium text-gray-800 hover:text-bronze transition-colors py-2 border-b border-gray-100">خدماتنا</a>
          <a href="#testimonials" @click="mobileMenuOpen = false" class="block text-lg font-medium text-gray-800 hover:text-bronze transition-colors py-2 border-b border-gray-100">آراء العملاء</a>
          <a href="#contact" @click="mobileMenuOpen = false" class="block btn-primary text-center mt-4">تواصل معنا</a>
        </div>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
  settings: {
    type: Object,
    default: () => ({}),
  },
});

const scrolled = ref(false);
const mobileMenuOpen = ref(false);

const logoSrc = computed(() => {
  const path = props.settings?.site_logo;
  if (path) {
    if (String(path).startsWith('http')) return path;
    return `/storage/${path}`;
  }
  return '/logo.png';
});

const handleScroll = () => {
  scrolled.value = window.scrollY > 50;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.nav-link {
  @apply font-medium transition-colors duration-300 hover:text-bronze;
}
</style>
