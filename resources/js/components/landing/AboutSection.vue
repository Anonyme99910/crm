<template>
  <section id="about" class="section-padding bg-gradient-to-b from-white to-primary-50/30">
    <div class="container-custom">
      <div class="text-center mb-16">
        <span class="inline-block px-4 py-2 bg-bronze/10 text-bronze rounded-full text-sm font-semibold mb-4">{{ section.subtitle }}</span>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ section.title }}</h2>
        <div class="w-24 h-1 bg-gradient-bronze mx-auto rounded-full"></div>
      </div>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <!-- Image Section -->
        <div class="relative group order-2 lg:order-1">
          <div class="absolute -top-4 -right-4 w-full h-full border-4 border-bronze/20 rounded-2xl transition-all duration-500 group-hover:border-bronze/40"></div>
          <div class="absolute -bottom-4 -left-4 w-full h-full bg-gradient-bronze/10 rounded-2xl"></div>
          <div v-if="section.background_image" class="relative overflow-hidden rounded-2xl shadow-2xl">
            <img :src="section.background_image" alt="من نحن" class="w-full h-auto transition-transform duration-700 group-hover:scale-105">
          </div>
          <div v-else class="relative bg-gradient-bronze rounded-2xl shadow-2xl h-96 flex items-center justify-center overflow-hidden">
            <div class="absolute inset-0 bg-black/10"></div>
            <svg class="w-32 h-32 text-white/40 relative z-10" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
            </svg>
          </div>
        </div>
        
        <!-- Content Section -->
        <div class="order-1 lg:order-2">
          <div class="prose prose-lg max-w-none">
            <p class="text-gray-700 leading-relaxed whitespace-pre-line text-lg">{{ section.content }}</p>
          </div>
          
          <!-- Stats Grid -->
          <div class="grid grid-cols-2 gap-6 mt-10">
            <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
              <div class="text-5xl font-bold text-bronze mb-2">+10</div>
              <div class="text-gray-600 font-medium">سنوات خبرة</div>
            </div>
            <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
              <div class="text-5xl font-bold text-bronze mb-2">+200</div>
              <div class="text-gray-600 font-medium">مشروع منجز</div>
            </div>
            <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
              <div class="text-5xl font-bold text-bronze mb-2">+150</div>
              <div class="text-gray-600 font-medium">عميل سعيد</div>
            </div>
            <div class="text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-gray-100">
              <div class="text-5xl font-bold text-bronze mb-2">+50</div>
              <div class="text-gray-600 font-medium">جائزة تميز</div>
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
