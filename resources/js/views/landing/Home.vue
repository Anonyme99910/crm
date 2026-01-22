<template>
  <div class="home" dir="rtl">
    <Navbar :settings="settings" />
    
    <HeroSection v-if="heroSection" :section="heroSection" :settings="settings" />
    
    <AboutSection v-if="aboutSection" :section="aboutSection" />
    
    <PortfolioSection v-if="portfolioSection" :section="portfolioSection" :items="portfolioItems" />
    
    <ServicesSection v-if="servicesSection" :section="servicesSection" :services="services" />
    
    <TestimonialsSection v-if="testimonialsSection" :section="testimonialsSection" :testimonials="testimonials" />
    
    <ContactSection v-if="contactSection" :section="contactSection" :settings="settings" />
    
    <Footer :settings="settings" />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from '@/utils/axios';
import Navbar from '@/components/landing/Navbar.vue';
import Footer from '@/components/landing/Footer.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import AboutSection from '@/components/landing/AboutSection.vue';
import PortfolioSection from '@/components/landing/PortfolioSection.vue';
import ServicesSection from '@/components/landing/ServicesSection.vue';
import TestimonialsSection from '@/components/landing/TestimonialsSection.vue';
import ContactSection from '@/components/landing/ContactSection.vue';

const sections = ref([]);
const portfolioItems = ref([]);
const services = ref([]);
const testimonials = ref([]);
const settings = ref({});

const heroSection = computed(() => sections.value.find(s => s.name === 'hero' && s.is_active));
const aboutSection = computed(() => sections.value.find(s => s.name === 'about' && s.is_active));
const portfolioSection = computed(() => sections.value.find(s => s.name === 'portfolio' && s.is_active));
const servicesSection = computed(() => sections.value.find(s => s.name === 'services' && s.is_active));
const testimonialsSection = computed(() => sections.value.find(s => s.name === 'testimonials' && s.is_active));
const contactSection = computed(() => sections.value.find(s => s.name === 'contact' && s.is_active));

onMounted(async () => {
  const results = await Promise.allSettled([
    axios.get('/api/landing/sections'),
    axios.get('/api/landing/portfolio'),
    axios.get('/api/landing/services'),
    axios.get('/api/landing/testimonials'),
    axios.get('/api/landing/settings'),
  ]);

  const [sectionsRes, portfolioRes, servicesRes, testimonialsRes, settingsRes] = results;

  if (sectionsRes.status === 'fulfilled') {
    sections.value = sectionsRes.value.data;
    console.log('Sections loaded:', sections.value);
    const hero = sections.value.find(s => s.name === 'hero');
    console.log('Hero section:', hero);
    console.log('Hero background_video:', hero?.background_video);
  }

  if (portfolioRes.status === 'fulfilled') {
    portfolioItems.value = portfolioRes.value.data.filter(item => item.is_active);
  }

  if (servicesRes.status === 'fulfilled') {
    services.value = servicesRes.value.data.filter(service => service.is_active);
  }

  if (testimonialsRes.status === 'fulfilled') {
    testimonials.value = testimonialsRes.value.data.filter(testimonial => testimonial.is_active);
  }

  if (settingsRes.status === 'fulfilled') {
    const settingsArray = settingsRes.value.data;
    settings.value = settingsArray.reduce((acc, setting) => {
      acc[setting.key] = setting.value;
      return acc;
    }, {});
  }
});
</script>
