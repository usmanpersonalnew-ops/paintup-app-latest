<script setup>
import { ref, computed } from 'vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineOptions({
  layout: CustomerLayout,
});

const props = defineProps({
  customer: Object,
  project: Object,
  photos: Array,
});

const stages = [
  { value: 'before', label: 'Before', icon: '📷', color: 'bg-orange-500', desc: 'Initial site condition' },
  { value: 'in-progress', label: 'In Progress', icon: '🔨', desc: 'Work in progress' },
  { value: 'after', label: 'After', icon: '✨', desc: 'Final result' },
];

const activeTab = ref('before');

const photosByStage = computed(() => {
  const grouped = {
    before: [],
    'in-progress': [],
    after: [],
  };
  
  props.photos.forEach(photo => {
    if (grouped[photo.stage]) {
      grouped[photo.stage].push(photo);
    } else {
      grouped.before.push(photo);
    }
  });
  
  return grouped;
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  });
};

const getStageInfo = (stage) => {
  return stages.find(s => s.value === stage) || stages[0];
};

const getStageColor = (stage) => {
  const colors = {
    'before': 'from-orange-400 to-orange-600',
    'in-progress': 'from-blue-400 to-blue-600',
    'after': 'from-green-400 to-green-600',
  };
  return colors[stage] || 'from-gray-400 to-gray-600';
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
      <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold">Your Home Progress</h1>
        <p class="text-blue-100 mt-1">{{ project.client_name }} - {{ project.location }}</p>
      </div>
    </div>

    <!-- Progress Timeline -->
    <div class="max-w-7xl mx-auto px-4 py-6">
      <!-- Stage Tabs -->
      <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="grid grid-cols-3 divide-x divide-gray-100">
          <button
            v-for="stage in stages"
            :key="stage.value"
            @click="activeTab = stage.value"
            :class="[
              'py-4 px-4 text-center transition-all',
              activeTab === stage.value 
                ? 'bg-blue-50' 
                : 'hover:bg-gray-50'
            ]"
          >
            <span :class="['text-2xl mb-2 block', activeTab === stage.value ? 'scale-110' : '']">
              {{ stage.icon }}
            </span>
            <span :class="['font-medium text-sm', activeTab === stage.value ? 'text-blue-600' : 'text-gray-600']">
              {{ stage.label }}
            </span>
            <span class="block text-xs text-gray-400 mt-1">
              {{ photosByStage[stage.value]?.length || 0 }} photos
            </span>
          </button>
        </div>
      </div>

      <!-- Photos Section -->
      <div v-if="photosByStage[activeTab]?.length === 0" class="bg-white rounded-xl shadow-sm p-12 text-center">
        <span class="text-5xl mb-4 block">{{ getStageInfo(activeTab).icon }}</span>
        <h3 class="text-lg font-medium text-gray-900">No {{ getStageInfo(activeTab).label }} photos yet</h3>
        <p class="text-gray-500 mt-2">{{ getStageInfo(activeTab).desc }}</p>
        <p class="text-sm text-gray-400 mt-4">Check back soon for updates</p>
      </div>
      
      <div v-else class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800">
              <span class="mr-2">{{ getStageInfo(activeTab).icon }}</span>
              {{ getStageInfo(activeTab).label }}
            </h2>
            <p class="text-sm text-gray-500">{{ getStageInfo(activeTab).desc }}</p>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
              <div
                v-for="photo in photosByStage[activeTab]"
                :key="photo.id"
                class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
              >
                <img
                  :src="photo.google_drive_link"
                  :alt="photo.file_name"
                  class="w-full h-full object-cover"
                />
                
                <!-- Hover Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent 
                  opacity-0 group-hover:opacity-100 transition-opacity flex items-end justify-center pb-4">
                  <a
                    :href="photo.google_drive_link"
                    target="_blank"
                    class="px-4 py-2 bg-white/90 rounded-lg font-medium text-gray-700
                      hover:bg-white transform scale-95 group-hover:scale-100 transition-transform text-sm"
                  >
                    View Full Size
                  </a>
                </div>
                
                <!-- Date -->
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t 
                  from-black/60 to-transparent p-3">
                  <p class="text-white text-xs">{{ formatDate(photo.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Progress Summary -->
      <div class="mt-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-sm p-6 text-white">
        <h3 class="font-semibold mb-4">Project Progress</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <span class="text-3xl mb-1 block">📷</span>
            <p class="text-sm text-blue-100">Before</p>
            <p class="font-semibold">{{ photosByStage.before.length }}</p>
          </div>
          <div>
            <span class="text-3xl mb-1 block">🔨</span>
            <p class="text-sm text-blue-100">In Progress</p>
            <p class="font-semibold">{{ photosByStage['in-progress'].length }}</p>
          </div>
          <div>
            <span class="text-3xl mb-1 block">✨</span>
            <p class="text-sm text-blue-100">After</p>
            <p class="font-semibold">{{ photosByStage.after.length }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="bg-white border-t mt-8 py-6">
      <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500">
        <p class="font-medium text-gray-900">PaintUp - Professional Painting Services</p>
        <p class="mt-1">Mumbai's trusted painting experts</p>
      </div>
    </div>
  </div>
</template>
