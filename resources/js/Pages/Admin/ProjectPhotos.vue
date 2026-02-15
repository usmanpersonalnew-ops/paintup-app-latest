<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({
  layout: AdminLayout,
});

const props = defineProps({
  project: Object,
  photos: Array,
});

const form = useForm({});

const stages = [
  { value: 'before', label: 'Before', icon: '📷', color: 'bg-orange-500' },
  { value: 'in-progress', label: 'In Progress', icon: '🔨', color: 'bg-blue-500' },
  { value: 'after', label: 'After', icon: '✨', color: 'bg-green-500' },
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

const deletePhoto = (photoId) => {
  if (confirm('Are you sure you want to delete this photo?')) {
    form.delete(route('admin.project-photos.destroy', [props.project.id, photoId]));
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStageLabel = (stage) => {
  return stages.find(s => s.value === stage)?.label || stage;
};

const getStageColor = (stage) => {
  return stages.find(s => s.value === stage)?.color || 'bg-gray-500';
};

const handleImageError = (event) => {
  // If image fails to load, try to convert Google Drive link to direct image URL
  const img = event.target;
  const originalSrc = img.src;

  // Try to extract file ID and convert to direct image URL
  const fileIdMatch = originalSrc.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (fileIdMatch && fileIdMatch[1]) {
    const fileId = fileIdMatch[1];
    img.src = `https://drive.google.com/uc?export=view&id=${fileId}`;
  } else {
    // Fallback: show placeholder
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZSBub3QgYXZhaWxhYmxlPC90ZXh0Pjwvc3ZnPg==';
  }
};
</script>

<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Site Photos</h1>
        <p class="text-sm text-gray-500">{{ project.client_name }} - {{ project.location }}</p>
      </div>
      <a
        :href="route('admin.projects.show', project.id)"
        class="text-blue-600 hover:text-blue-800 text-sm font-medium"
      >
        ← Back to Project
      </a>
    </div>

    <!-- Photos by Stage Tabs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <!-- Tab Headers -->
      <div class="flex border-b">
        <button
          v-for="stage in stages"
          :key="stage.value"
          @click="activeTab = stage.value"
          :class="[
            'flex-1 py-3 px-4 text-sm font-medium border-b-2 transition-colors',
            activeTab === stage.value
              ? `${stage.color} text-white border-transparent`
              : 'text-gray-500 border-transparent hover:text-gray-700'
          ]"
        >
          <span class="mr-1">{{ stage.icon }}</span>
          {{ stage.label }} ({{ photosByStage[stage.value]?.length || 0 }})
        </button>
      </div>

      <!-- Tab Content -->
      <div class="p-6">
        <div v-if="photosByStage[activeTab]?.length === 0" class="text-center py-12 text-gray-500">
          <span class="text-4xl">{{ stages.find(s => s.value === activeTab)?.icon }}</span>
          <p class="mt-2 text-lg">No {{ stages.find(s => s.value === activeTab)?.label?.toLowerCase() }} photos available</p>
          <p class="text-sm">Supervisors will upload photos from the mobile app</p>
        </div>

        <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          <div
            v-for="photo in photosByStage[activeTab]"
            :key="photo.id"
            class="relative group aspect-square bg-gray-100 rounded-lg overflow-hidden"
          >
            <img
              :src="photo.image_url || photo.google_drive_link"
              :alt="photo.file_name"
              class="w-full h-full object-cover"
              @error="handleImageError($event)"
            />

            <!-- Stage Badge -->
            <div class="absolute top-2 left-2">
              <span :class="['text-xs px-2 py-1 rounded-full text-white', getStageColor(photo.stage)]">
                {{ getStageLabel(photo.stage) }}
              </span>
            </div>

            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100
              transition-opacity flex items-center justify-center gap-2">
                <a
                  :href="photo.image_url || photo.google_drive_link"
                  target="_blank"
                  class="p-2 bg-white rounded-full hover:bg-gray-100"
                  title="View Full Size"
                >
                <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </a>
              <button
                @click="deletePhoto(photo.id)"
                class="p-2 bg-white rounded-full hover:bg-red-50"
                title="Delete Photo"
              >
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>

            <!-- File Name -->
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t
              from-black/70 to-transparent p-2">
              <p class="text-white text-xs truncate">{{ photo.file_name }}</p>
              <p class="text-white/70 text-xs">{{ formatDate(photo.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary -->
    <div class="mt-4 bg-gray-50 rounded-lg p-4">
      <p class="text-sm text-gray-600">
        Total photos: <strong>{{ photos.length }}</strong>
        (Before: {{ photosByStage.before.length }},
        In Progress: {{ photosByStage['in-progress'].length }},
        After: {{ photosByStage.after.length }})
      </p>
    </div>
  </div>
</template>
