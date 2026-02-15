<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';

defineOptions({
  layout: SupervisorLayout,
});

const props = defineProps({
  project: Object,
  photos: Array,
});

const isUploading = ref(false);
const selectedFiles = ref([]);
const selectedStage = ref('before');

const form = useForm({
  photos: [],
  stage: 'before',
});

const stages = [
  { value: 'before', label: 'Before', icon: '📷', color: 'bg-orange-500' },
  { value: 'in-progress', label: 'In Progress', icon: '🔨', color: 'bg-blue-500' },
  { value: 'after', label: 'After', icon: '✨', color: 'bg-green-500' },
];

const selectedStageInfo = computed(() => {
  return stages.find(s => s.value === selectedStage.value) || stages[0];
});

const handleFileSelect = (event) => {
  selectedFiles.value = Array.from(event.target.files);
  form.photos = selectedFiles.value;
  form.stage = selectedStage.value;
};

const uploadPhotos = () => {
  if (selectedFiles.value.length === 0) return;

  isUploading.value = true;

  form.post(route('supervisor.photos.store', props.project.id), {
    onSuccess: () => {
      selectedFiles.value = [];
      form.photos = [];
      isUploading.value = false;
    },
    onError: () => {
      isUploading.value = false;
    },
  });
};

const deletePhoto = (photoId) => {
  if (confirm('Are you sure you want to delete this photo?')) {
    form.delete(route('supervisor.photos.destroy', [props.project.id, photoId]));
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
  
  // Handle drive.usercontent.google.com format
  if (originalSrc.includes('drive.usercontent.google.com')) {
    const fileIdMatch = originalSrc.match(/[?&]id=([a-zA-Z0-9_-]+)/);
    if (fileIdMatch && fileIdMatch[1]) {
      const fileId = fileIdMatch[1];
      img.src = `https://drive.google.com/uc?export=view&id=${fileId}`;
      return;
    }
  }
  
  // Try to extract file ID from other Google Drive URL formats
  const fileIdMatch = originalSrc.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (fileIdMatch && fileIdMatch[1]) {
    const fileId = fileIdMatch[1];
    img.src = `https://drive.google.com/uc?export=view&id=${fileId}`;
  } else {
    // Fallback: show placeholder
    img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2U1ZTdlYiIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5Y2EzYWYiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JbWFnZSBub3QgYXZhaWxhYmxlPC90ZXh0Pjwvc3ZnPg==';
  }
};

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

const activeTab = ref('before');
</script>

<template>
  <SupervisorLayout :title="`Site Photos - ${project.client_name}`">
    <div class="p-4 space-y-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Site Photos</h1>
          <p class="text-sm text-gray-500">{{ project.client_name }} - {{ project.location }}</p>
        </div>
        <a
          :href="route('supervisor.summary', project.id)"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
          ← Back to Summary
        </a>
      </div>

      <!-- Upload Section -->
      <div class="bg-white rounded-lg shadow p-4">
        <h2 class="text-lg font-semibold mb-3">Upload New Photos</h2>

        <!-- Stage Selection -->
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Select Stage</label>
          <div class="grid grid-cols-3 gap-2">
            <button
              v-for="stage in stages"
              :key="stage.value"
              @click="selectedStage = stage.value"
              :class="[
                'h-12 rounded-lg border-2 font-medium transition-all',
                selectedStage === stage.value
                  ? `${stage.color} text-white border-transparent`
                  : 'border-gray-200 text-gray-600 hover:border-gray-300'
              ]"
            >
              <span class="text-lg mr-1">{{ stage.icon }}</span>
              {{ stage.label }}
            </button>
          </div>
        </div>

        <div class="space-y-3">
          <input
            type="file"
            multiple
            accept="image/*"
            @change="handleFileSelect"
            class="block w-full text-sm text-gray-500
              file:mr-4 file:py-3 file:px-4
              file:rounded-lg file:border-0
              file:text-sm file:font-semibold
              file:bg-blue-50 file:text-blue-700
              hover:file:bg-blue-100
              cursor-pointer"
          />

          <div v-if="selectedFiles.length > 0" class="flex items-center gap-3">
            <span class="text-sm text-gray-600">
              {{ selectedFiles.length }} file(s) selected - {{ selectedStageInfo.label }}
            </span>
            <button
              @click="uploadPhotos"
              :disabled="isUploading"
              :class="[
                'h-12 px-6 text-white rounded-lg font-medium flex items-center justify-center',
                isUploading ? 'opacity-50 cursor-not-allowed' : `${selectedStageInfo.color} hover:opacity-90`
              ]"
            >
              <span v-if="isUploading">Uploading...</span>
              <span v-else>Upload {{ selectedStageInfo.label }} Photos</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Photos by Stage Tabs -->
      <div class="bg-white rounded-lg shadow">
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
        <div class="p-4">
          <div v-if="photosByStage[activeTab]?.length === 0" class="text-center py-8 text-gray-500">
            <span class="text-4xl">{{ selectedStageInfo.icon }}</span>
            <p class="mt-2">No {{ selectedStageInfo.label.toLowerCase() }} photos uploaded yet.</p>
          </div>

          <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
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
                  :href="photo.google_drive_link"
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

      <!-- All Photos Summary -->
      <div class="bg-gray-50 rounded-lg p-4">
        <p class="text-sm text-gray-600">
          Total photos: <strong>{{ photos.length }}</strong>
          (Before: {{ photosByStage.before.length }},
          In Progress: {{ photosByStage['in-progress'].length }},
          After: {{ photosByStage.after.length }})
        </p>
      </div>
    </div>
  </SupervisorLayout>
</template>
