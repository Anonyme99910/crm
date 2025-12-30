<template>
  <div class="home">
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
import axios from '../utils/axios';
import Navbar from '../components/Navbar.vue';
import Footer from '../components/Footer.vue';
import HeroSection from '../components/sections/HeroSection.vue';
import AboutSection from '../components/sections/AboutSection.vue';
import PortfolioSection from '../components/sections/PortfolioSection.vue';
import ServicesSection from '../components/sections/ServicesSection.vue';
import TestimonialsSection from '../components/sections/TestimonialsSection.vue';
import ContactSection from '../components/sections/ContactSection.vue';

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
    axios.get('api/sections'),
    axios.get('api/portfolio'),
    axios.get('api/services'),
    axios.get('api/testimonials'),
    axios.get('api/settings'),
  ]);

  const [sectionsRes, portfolioRes, servicesRes, testimonialsRes, settingsRes] = results;

  if (sectionsRes.status === 'fulfilled') {
    sections.value = sectionsRes.value.data;
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
