<template>
  <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" :class="scrolled ? 'bg-white shadow-lg' : 'bg-transparent'">
    <div class="container-custom px-4 md:px-8">
      <div class="flex items-center justify-between h-20">
        <a href="/hazem/" class="flex items-center space-x-3">
          <img :src="logoSrc" alt="Hazem Abdullah" class="h-12 w-auto transition-transform duration-300 hover:scale-110">
        </a>
        
        <div class="hidden md:flex items-center space-x-8">
          <a href="#home" class="nav-link" :class="scrolled ? 'text-gray-800' : 'text-white'">Home</a>
          <a href="#about" class="nav-link" :class="scrolled ? 'text-gray-800' : 'text-white'">About</a>
          <a href="#portfolio" class="nav-link" :class="scrolled ? 'text-gray-800' : 'text-white'">Portfolio</a>
          <a href="#services" class="nav-link" :class="scrolled ? 'text-gray-800' : 'text-white'">Services</a>
          <a href="#testimonials" class="nav-link" :class="scrolled ? 'text-gray-800' : 'text-white'">Testimonials</a>
          <a href="#contact" class="btn-primary">Contact Us</a>
        </div>
        
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden" :class="scrolled ? 'text-gray-800' : 'text-white'">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <div v-if="mobileMenuOpen" class="md:hidden bg-white shadow-lg">
      <div class="px-4 py-6 space-y-4">
        <a href="#home" class="block text-gray-800 hover:text-bronze">Home</a>
        <a href="#about" class="block text-gray-800 hover:text-bronze">About</a>
        <a href="#portfolio" class="block text-gray-800 hover:text-bronze">Portfolio</a>
        <a href="#services" class="block text-gray-800 hover:text-bronze">Services</a>
        <a href="#testimonials" class="block text-gray-800 hover:text-bronze">Testimonials</a>
        <a href="#contact" class="block text-gray-800 hover:text-bronze">Contact</a>
      </div>
    </div>
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
    return `/hazem/storage/${path}`;
  }
  return '/hazem/logo.png';
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
