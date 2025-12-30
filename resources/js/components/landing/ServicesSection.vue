<template>
  <section id="services" class="section-padding bg-gradient-to-b from-primary-50/30 to-white relative overflow-hidden">
    <!-- Background decorations -->
    <div class="absolute top-0 left-0 w-72 h-72 bg-bronze/5 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-bronze/5 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
    
    <div class="container-custom relative z-10">
      <div class="text-center mb-16">
        <span class="inline-block px-4 py-2 bg-bronze/10 text-bronze rounded-full text-sm font-semibold mb-4">{{ section.subtitle }}</span>
        <h2 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">{{ section.title }}</h2>
        <div class="w-24 h-1 bg-gradient-bronze mx-auto rounded-full mb-6"></div>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">{{ section.content }}</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div 
          v-for="(service, index) in services" 
          :key="service.id"
          class="group text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border border-gray-100 relative overflow-hidden"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <!-- Hover gradient overlay -->
          <div class="absolute inset-0 bg-gradient-bronze opacity-0 group-hover:opacity-5 transition-opacity duration-500"></div>
          
          <div class="relative z-10">
            <div class="mb-6 inline-flex items-center justify-center w-20 h-20 bg-gradient-bronze rounded-2xl group-hover:scale-110 group-hover:rotate-3 transition-all duration-500 shadow-lg">
              <svg v-if="service.icon === 'home'" class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
              </svg>
              <svg v-else-if="service.icon === 'layout'" class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-3zM14 16a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1v-3z"></path>
              </svg>
              <svg v-else-if="service.icon === 'sofa'" class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
              </svg>
              <svg v-else-if="service.icon === 'palette'" class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
              </svg>
              <svg v-else class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path>
              </svg>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900 mb-4 group-hover:text-bronze transition-colors duration-300">{{ service.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ service.description }}</p>
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
  services: {
    type: Array,
    default: () => [],
  },
});
</script>
