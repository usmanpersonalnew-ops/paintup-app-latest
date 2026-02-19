<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
  room: Object,
  surfaces: Array,
  products: Array,
  masterServices: Array,
  zoneServices: Array,
});

const page = usePage();

// Local state
const selectedSurface = ref(null);
const selectedTier = ref('all');
const selectedProduct = ref(null);
const selectedSystem = ref(null);
const measurementMode = ref('ZONE_DEFAULT');
const pricingMode = ref('CALCULATED');

// Zone edit mode
const isEditingZone = ref(false);
const editZoneForm = useForm({
    name: '',
    length: 0,
    breadth: 0,
    height: 0,
});

// Service modal state
const showServiceModal = ref(false);
const serviceMode = ref('catalog'); // 'catalog' or 'custom'
const selectedService = ref(null);
const serviceQuantity = ref(1);
const serviceRate = ref(0);
const servicePhoto = ref(null);

// Form measurements (for manual mode)
const formLength = ref(null);
const formBreadth = ref(null);
const formHeight = ref(null);
const deductions = ref(0);

// Computed: Filtered Surfaces (by zone type)
const filteredSurfaces = computed(() => {
  if (!props.surfaces || !props.room) return [];

  const roomType = props.room.type || 'INTERIOR';

  // Filter surfaces based on room type
  return props.surfaces.filter(surface => {
    // If room is INTERIOR, show INTERIOR and BOTH surfaces
    if (roomType === 'INTERIOR') {
      return surface.category === 'INTERIOR' || surface.category === 'BOTH';
    }
    // If room is EXTERIOR, show EXTERIOR and BOTH surfaces
    if (roomType === 'EXTERIOR') {
      return surface.category === 'EXTERIOR' || surface.category === 'BOTH';
    }
    // Default: show all surfaces
    return true;
  });
});

// Computed: Filtered products by tier
const filteredProducts = computed(() => {
  if (selectedTier.value === 'all') {
    return props.products;
  }
  return props.products.filter(p => p.tier === selectedTier.value);
});

// Computed: Available systems for selected product
const availableSystems = computed(() => {
  if (!selectedProduct.value) return [];
  const product = props.products.find(p => p.id === selectedProduct.value);
  return product?.systems || [];
});

// Computed: Zone default dimensions display
const zoneDimensions = computed(() => {
  const l = props.room.length || 0;
  const b = props.room.breadth || 0;
  const h = props.room.height || 0;
  return `${l}x${b}x${h}`;
});

// Computed: Gross Area based on mode
const grossArea = computed(() => {
  if (selectedSurface.value?.unit_type === 'COUNT') {
    return measurementMode.value === 'ZONE_DEFAULT' ? 1 : (formBreadth.value || 1);
  }

  if (measurementMode.value === 'ZONE_DEFAULT') {
    const l = props.room.length || 0;
    const h = props.room.height || 0;
    if (selectedSurface.value?.unit_type === 'LINEAR') {
      return l; // Linear: only length
    }
    return l * h; // Area: L x H
  } else {
    const l = formLength.value || 0;
    const h = formHeight.value || 0;
    if (selectedSurface.value?.unit_type === 'LINEAR') {
      return l; // Linear: only length
    }
    return l * h; // Area: L x H
  }
});

// Computed: Net Area
const netArea = computed(() => {
  return Math.max(0, grossArea.value - (deductions.value || 0));
});

// Computed: Calculated Amount
const calculatedAmount = computed(() => {
  if (pricingMode.value === 'LUMPSUM') {
    return form.manual_price || 0;
  }
  if (selectedSystem.value) {
    const system = availableSystems.value.find(s => s.id === parseInt(selectedSystem.value));
    return netArea.value * (system?.rate || 0);
  }
  return 0;
});

// Computed: System Rate
const systemRate = computed(() => {
  if (!selectedSystem.value) return 0;
  const system = availableSystems.value.find(s => s.id === parseInt(selectedSystem.value));
  return system?.rate || 0;
});

// Computed: Service Amount
const serviceAmount = computed(() => {
  return serviceQuantity.value * serviceRate.value;
});

// Computed: Is selected service a repair
const isRepairService = computed(() => {
  if (!selectedService.value) return false;
  const service = props.masterServices.find(s => s.id === selectedService.value);
  return service?.is_repair || false;
});

// Form object
const form = useForm({
  surface_id: null,
  product_id: null,
  painting_system_id: null,
  measurement_mode: 'ZONE_DEFAULT',
  pricing_mode: 'CALCULATED',
  length: null,
  breadth: null,
  height: null,
  deductions: 0,
  color_code: null,
  description: null,
  manual_price: null,
});

// Service form
const serviceForm = useForm({
  project_zone_id: null,
  master_service_id: null,
  custom_name: '',
  unit_type: 'AREA',
  quantity: 1,
  rate: 0,
  photo: null,
  remarks: '',
});

// Watch for changes to update form values
watch([selectedSurface, selectedProduct, selectedSystem, measurementMode, pricingMode], () => {
  form.surface_id = selectedSurface.value;
  form.product_id = selectedProduct.value;
  form.painting_system_id = selectedSystem.value;
  form.measurement_mode = measurementMode.value;
  form.pricing_mode = pricingMode.value;
  form.length = formLength.value;
  form.breadth = formBreadth.value;
  form.height = formHeight.value;
  form.deductions = deductions.value;
});

// Watch for service selection
watch([selectedService, serviceMode], () => {
  if (serviceMode.value === 'catalog' && selectedService.value) {
    const service = props.masterServices.find(s => s.id === selectedService.value);
    if (service) {
      serviceForm.master_service_id = service.id;
      serviceForm.custom_name = service.name;
      serviceForm.unit_type = service.unit_type;
      serviceForm.rate = service.default_rate;
      serviceForm.remarks = service.remarks || '';
      serviceQuantity.value = 1;
      serviceRate.value = service.default_rate;
    }
  } else {
    serviceForm.master_service_id = null;
    serviceForm.custom_name = '';
    serviceForm.unit_type = 'AREA';
    serviceForm.rate = 0;
    serviceForm.remarks = '';
    serviceQuantity.value = 1;
    serviceRate.value = 0;
  }
});

const submit = () => {
  form.post(route('supervisor.zones.items.store', props.room.id), {
    onSuccess: () => {
      resetForm();
    },
  });
};

const resetForm = () => {
  selectedSurface.value = null;
  selectedProduct.value = null;
  selectedSystem.value = null;
  measurementMode.value = 'ZONE_DEFAULT';
  pricingMode.value = 'CALCULATED';
  formLength.value = null;
  formBreadth.value = null;
  formHeight.value = null;
  deductions.value = 0;
  form.reset();
};

// Service modal handlers
const openServiceModal = () => {
  showServiceModal.value = true;
  serviceMode.value = 'catalog';
  selectedService.value = null;
  serviceQuantity.value = 1;
  serviceRate.value = 0;
  servicePhoto.value = null;
  serviceForm.reset();
};

const closeServiceModal = () => {
  showServiceModal.value = false;
  serviceForm.reset();
};

const handlePhotoChange = (event) => {
  const file = event.target.files[0];
  if (file) {
    servicePhoto.value = file;
    serviceForm.photo = file;
  }
};

const submitService = () => {
  // Find the zone for this room (from room.quote.zones)
  const zone = props.room.quote?.zones?.[0];
  if (!zone) {
    alert('No zone found for this room');
    return;
  }

  serviceForm.project_zone_id = zone.id;

  // If custom mode, set unit_type based on selection
  if (serviceMode.value === 'custom') {
    serviceForm.unit_type = 'AREA'; // Default for custom
  }

  serviceForm.quantity = serviceQuantity.value;
  serviceForm.rate = serviceRate.value;

  const formData = new FormData();
  formData.append('project_zone_id', serviceForm.project_zone_id);
  if (serviceForm.master_service_id) {
    formData.append('master_service_id', serviceForm.master_service_id);
  }
  formData.append('custom_name', serviceForm.custom_name);
  formData.append('unit_type', serviceForm.unit_type);
  formData.append('quantity', serviceForm.quantity);
  formData.append('rate', serviceForm.rate);
  formData.append('remarks', serviceForm.remarks || '');
  if (servicePhoto.value) {
    formData.append('photo', servicePhoto.value);
  }

  // Use fetch for file upload
  fetch(route('supervisor.zones.services.store', zone.id), {
    method: 'POST',
    body: formData,
    headers: {
      'X-CSRF-TOKEN': page.props.csrf_token,
    },
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      closeServiceModal();
      window.location.reload(); // Reload to show new service
    } else {
      alert(data.message || 'Error saving service');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error saving service');
  });
};

// Format currency
const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-IN', {
    style: 'currency',
    currency: 'INR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value || 0);
};

// Tier colors
const tierColors = {
  Eco: 'bg-green-100 text-green-800',
  Premium: 'bg-purple-100 text-purple-800',
  Luxury: 'bg-amber-100 text-amber-800',
};

// Unit type labels
const unitTypeLabels = {
  AREA: 'sqft',
  LINEAR: 'ft',
  COUNT: 'units',
  LUMPSUM: 'lumpsum',
};

// Zone edit functions
const startEditZone = () => {
    editZoneForm.name = props.room.name;
    editZoneForm.length = props.room.length || 0;
    editZoneForm.breadth = props.room.breadth || 0;
    editZoneForm.height = props.room.height || 0;
    isEditingZone.value = true;
};

const cancelEditZone = () => {
    isEditingZone.value = false;
    editZoneForm.reset();
};

const saveZone = () => {
    editZoneForm.put(route('supervisor.zones.update', props.room.id), {
        onSuccess: () => {
            isEditingZone.value = false;
        },
    });
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header: Zone Name & Dimensions -->
    <div class="bg-white shadow-sm sticky top-0 z-10">
      <div class="max-w-lg mx-auto px-4 py-4">
        <div class="flex items-center justify-between mb-2">
          <Link :href="route('supervisor.projects.show', room.project_id)" class="text-blue-600 text-sm">← Back</Link>
          <div class="flex gap-2">
            <button
              v-if="!isEditingZone"
              @click="startEditZone"
              class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-300"
            >
              Edit Zone
            </button>
            <template v-else>
              <button
                @click="cancelEditZone"
                class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-300"
              >
                Cancel
              </button>
              <button
                @click="saveZone"
                class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium hover:bg-blue-700"
                :disabled="editZoneForm.processing"
              >
                Save
              </button>
            </template>
            <Link
              :href="route('supervisor.projects.show', room.project_id)"
              class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700"
            >
              Done
            </Link>
          </div>
        </div>
        <div class="flex items-center justify-between">
          <!-- View Mode -->
          <div v-if="!isEditingZone">
            <h1 class="text-xl font-bold text-gray-900">{{ room.name }}</h1>
            <p class="text-sm text-gray-500">Zone Dimensions: {{ zoneDimensions }} ft</p>
          </div>
          <!-- Edit Mode -->
          <div v-else class="flex-1 space-y-3">
            <TextInput v-model="editZoneForm.name" type="text" class="w-full h-10" placeholder="Zone Name" />
            <div class="grid grid-cols-3 gap-2">
              <div>
                <InputLabel for="editLength" value="L" class="text-xs" />
                <TextInput id="editLength" v-model="editZoneForm.length" type="number" step="0.1" class="h-10 w-full" />
              </div>
              <div>
                <InputLabel for="editBreadth" value="B" class="text-xs" />
                <TextInput id="editBreadth" v-model="editZoneForm.breadth" type="number" step="0.1" class="h-10 w-full" />
              </div>
              <div>
                <InputLabel for="editHeight" value="H" class="text-xs" />
                <TextInput id="editHeight" v-model="editZoneForm.height" type="number" step="0.1" class="h-10 w-full" />
              </div>
            </div>
          </div>
          <div class="text-right">
            <p class="text-xs text-gray-500 uppercase">Project</p>
            <p class="font-medium text-gray-900">{{ room.project?.name || 'N/A' }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-lg mx-auto px-4 py-6 space-y-6">
      <!-- Flash Messages -->
      <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        {{ $page.props.flash.success }}
      </div>
      <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
        {{ $page.props.flash.error }}
      </div>

      <!-- Services & Repairs Section -->
      <div class="bg-white rounded-xl shadow-sm p-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Services & Repairs</h2>
          <button
            @click="openServiceModal"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium text-sm hover:bg-blue-700"
          >
            + Add Service / Repair
          </button>
        </div>

        <!-- Existing Services List -->
        <div v-if="zoneServices && zoneServices.length > 0" class="space-y-3">
          <div
            v-for="service in zoneServices"
            :key="service.id"
            class="bg-gray-50 rounded-lg p-3"
          >
            <div class="flex items-start justify-between">
              <div>
                <p class="font-medium text-gray-900">{{ service.custom_name }}</p>
                <p class="text-sm text-gray-500">
                  {{ service.quantity }} {{ unitTypeLabels[service.unit_type] }} × {{ formatCurrency(service.rate) }}
                </p>
                <p v-if="service.masterService?.remarks" class="text-xs text-gray-400 mt-1">
                  Note: {{ service.masterService.remarks }}
                </p>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-900">{{ formatCurrency(service.amount) }}</p>
                <p v-if="service.photo_url" class="text-xs text-blue-600">📷 Photo attached</p>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4 text-gray-500">
          No services added yet
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">

        <!-- 1. Surface Dropdown -->
        <div>
          <InputLabel for="surface" value="Surface Type" />
          <select
            id="surface"
            v-model="selectedSurface"
            class="mt-1 block w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option :value="null">Select Surface</option>
            <option v-for="surface in filteredSurfaces" :key="surface.id" :value="surface.id">
              {{ surface.name }} ({{ surface.unit_type }})
            </option>
          </select>
          <InputError :message="form.errors.surface_id" class="mt-2" />
          <p v-if="filteredSurfaces.length === 0" class="text-sm text-orange-500 mt-1">
            No surfaces available for {{ room?.type || 'INTERIOR' }} rooms
          </p>
        </div>

        <!-- 2. Tier Filter Tabs -->
        <div v-if="selectedSurface">
          <InputLabel value="Product Tier" />
          <div class="flex gap-2 mt-2">
            <button
              type="button"
              :class="[
                'flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-colors',
                selectedTier === 'all' ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700'
              ]"
              @click="selectedTier = 'all'"
            >
              All
            </button>
            <button
              type="button"
              v-for="tier in ['Eco', 'Premium', 'Luxury']"
              :key="tier"
              :class="[
                'flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-colors',
                selectedTier === tier ? 'bg-blue-600 text-white' : 'bg-white border border-gray-300 text-gray-700'
              ]"
              @click="selectedTier = tier"
            >
              {{ tier }}
            </button>
          </div>
        </div>

        <!-- 3. Product Dropdown -->
        <div v-if="filteredProducts.length > 0">
          <InputLabel for="product" value="Product" />
          <select
            id="product"
            v-model="selectedProduct"
            class="mt-1 block w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option :value="null">Select Product</option>
            <option v-for="product in filteredProducts" :key="product.id" :value="product.id">
              {{ product.name }} - {{ product.brand }}
              <span :class="['ml-2 px-2 py-0.5 rounded text-xs', tierColors[product.tier]]">
                {{ product.tier }}
              </span>
            </option>
          </select>
          <InputError :message="form.errors.product_id" class="mt-2" />
        </div>

        <!-- 4. System Dropdown -->
        <div v-if="selectedProduct && availableSystems.length > 0">
          <InputLabel for="system" value="Painting System (Rate)" />
          <select
            id="system"
            v-model="selectedSystem"
            class="mt-1 block w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option :value="null">Select System</option>
            <option v-for="system in availableSystems" :key="system.id" :value="system.id">
              {{ system.name }} - {{ formatCurrency(system.rate) }}/sqft
            </option>
          </select>
          <InputError :message="form.errors.painting_system_id" class="mt-2" />
        </div>

        <!-- 5. Measurements Section -->
        <div v-if="selectedSurface" class="bg-white rounded-xl shadow-sm p-4 space-y-4">
          <h3 class="font-semibold text-gray-900">Measurements</h3>

          <!-- Radio: Default vs Manual -->
          <div class="flex gap-4">
            <label class="flex items-center">
              <input
                type="radio"
                v-model="measurementMode"
                value="ZONE_DEFAULT"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Use Default ({{ zoneDimensions }})</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="measurementMode"
                value="MANUAL"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Manual Entry</span>
            </label>
          </div>

          <!-- Manual Inputs (shown only in Manual mode) -->
          <div v-if="measurementMode === 'MANUAL'" class="grid grid-cols-2 gap-4">
            <div>
              <InputLabel for="length" value="Length (ft)" />
              <TextInput
                id="length"
                v-model="formLength"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 h-12"
                placeholder="0.00"
              />
            </div>
            <div>
              <InputLabel for="height" value="Height (ft)" />
              <TextInput
                id="height"
                v-model="formHeight"
                type="number"
                step="0.01"
                min="0"
                class="mt-1 h-12"
                placeholder="0.00"
              />
            </div>
            <div v-if="selectedSurface.unit_type === 'COUNT'" class="col-span-2">
              <InputLabel for="count" value="Count" />
              <TextInput
                id="count"
                v-model="formBreadth"
                type="number"
                step="1"
                min="1"
                class="mt-1 h-12"
                placeholder="1"
              />
            </div>
          </div>

          <!-- Deductions -->
          <div>
            <InputLabel for="deductions" value="Deductions (sqft)" />
            <TextInput
              id="deductions"
              v-model="deductions"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 h-12"
              placeholder="0.00"
            />
          </div>

          <!-- Calculated Values Display -->
          <div class="bg-gray-50 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Gross Area:</span>
              <span class="font-medium">{{ grossArea.toFixed(2) }} sqft</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Deductions:</span>
              <span class="font-medium">- {{ (deductions || 0).toFixed(2) }} sqft</span>
            </div>
            <div class="flex justify-between text-base font-bold border-t pt-2">
              <span class="text-gray-900">Net Area:</span>
              <span class="text-blue-600">{{ netArea.toFixed(2) }} sqft</span>
            </div>
          </div>
        </div>

        <!-- 6. Pricing Section -->
        <div v-if="selectedSurface" class="bg-white rounded-xl shadow-sm p-4 space-y-4">
          <h3 class="font-semibold text-gray-900">Pricing</h3>

          <!-- Pricing Mode Radio -->
          <div class="flex gap-4">
            <label class="flex items-center">
              <input
                type="radio"
                v-model="pricingMode"
                value="CALCULATED"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Calculated</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="pricingMode"
                value="LUMPSUM"
                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">Lumpsum</span>
            </label>
          </div>

          <!-- Lumpsum Input -->
          <div v-if="pricingMode === 'LUMPSUM'">
            <InputLabel for="manual_price" value="Manual Price (₹)" />
            <TextInput
              id="manual_price"
              v-model="form.manual_price"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 h-12"
              placeholder="0.00"
            />
          </div>

          <!-- Calculated Price Display -->
          <div v-if="pricingMode === 'CALCULATED'" class="bg-blue-50 rounded-lg p-4 space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Rate:</span>
              <span class="font-medium">{{ formatCurrency(systemRate) }}/sqft</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Net Area:</span>
              <span class="font-medium">{{ netArea.toFixed(2) }} sqft</span>
            </div>
            <div class="flex justify-between text-xl font-bold border-t pt-2">
              <span class="text-gray-900">Total:</span>
              <span class="text-green-600">{{ formatCurrency(calculatedAmount) }}</span>
            </div>
          </div>
        </div>

        <!-- Optional Fields -->
        <div v-if="selectedSurface" class="bg-white rounded-xl shadow-sm p-4 space-y-4">
          <h3 class="font-semibold text-gray-900">Additional Details</h3>

          <div>
            <InputLabel for="color_code" value="Color Code (Optional)" />
            <TextInput
              id="color_code"
              v-model="form.color_code"
              type="text"
              class="mt-1 h-12"
              placeholder="e.g., RAL 7016"
            />
          </div>

          <div>
            <InputLabel for="description" value="Description (Optional)" />
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
              placeholder="Add any notes..."
            ></textarea>
          </div>
        </div>

        <!-- Save Button -->
        <div v-if="selectedSurface" class="fixed bottom-0 left-0 right-0 bg-white border-t p-4">
          <div class="max-w-lg mx-auto">
            <PrimaryButton
              type="submit"
              class="w-full h-14 text-lg font-semibold"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Saving...' : 'Save Item' }}
            </PrimaryButton>
          </div>
        </div>

      </form>

      <!-- Bottom padding for fixed button -->
      <div v-if="selectedSurface" class="h-20"></div>
    </div>

    <!-- Service Modal -->
    <Modal :show="showServiceModal" @close="closeServiceModal" max-width="md">
      <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Add Service / Repair</h2>

        <!-- Mode Toggle -->
        <div class="flex gap-2 mb-4">
          <button
            type="button"
            :class="[
              'flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-colors',
              serviceMode === 'catalog' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'
            ]"
            @click="serviceMode = 'catalog'"
          >
            Catalog
          </button>
          <button
            type="button"
            :class="[
              'flex-1 py-2 px-4 rounded-lg font-medium text-sm transition-colors',
              serviceMode === 'custom' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'
            ]"
            @click="serviceMode = 'custom'"
          >
            Custom
          </button>
        </div>

        <!-- Catalog Mode -->
        <div v-if="serviceMode === 'catalog'" class="space-y-4">
          <div>
            <InputLabel for="service" value="Select Service" />
            <select
              id="service"
              v-model="selectedService"
              class="mt-1 block w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option :value="null">Select Service</option>
              <option v-for="service in masterServices" :key="service.id" :value="service.id">
                {{ service.name }} ({{ service.unit_type }}) - {{ formatCurrency(service.default_rate) }}
                <span v-if="service.is_repair" class="text-orange-500">🔧 Repair</span>
              </option>
            </select>
          </div>
        </div>

        <!-- Custom Mode -->
        <div v-if="serviceMode === 'custom'" class="space-y-4">
          <div>
            <InputLabel for="custom_name" value="Service Name" />
            <TextInput
              id="custom_name"
              v-model="serviceForm.custom_name"
              type="text"
              class="mt-1 h-12"
              placeholder="e.g., Extra Labor"
            />
          </div>

          <div>
            <InputLabel for="custom_unit_type" value="Unit Type" />
            <select
              id="custom_unit_type"
              v-model="serviceForm.unit_type"
              class="mt-1 block w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="AREA">Area (sqft)</option>
              <option value="LINEAR">Linear (ft)</option>
              <option value="COUNT">Count (units)</option>
              <option value="LUMPSUM">Lumpsum</option>
            </select>
          </div>
        </div>

        <!-- Quantity & Rate -->
        <div class="grid grid-cols-2 gap-4 mt-4">
          <div>
            <InputLabel for="service_quantity" value="Quantity" />
            <TextInput
              id="service_quantity"
              v-model="serviceQuantity"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 h-12"
              placeholder="0.00"
            />
          </div>
          <div>
            <InputLabel for="service_rate" value="Rate (₹)" />
            <TextInput
              id="service_rate"
              v-model="serviceRate"
              type="number"
              step="0.01"
              min="0"
              class="mt-1 h-12"
              placeholder="0.00"
            />
          </div>
        </div>

        <!-- Photo Upload (Repair Only) -->
        <div v-if="isRepairService" class="mt-4">
          <InputLabel for="photo" value="Photo Evidence (Required)" />
          <input
            id="photo"
            type="file"
            accept="image/*"
            @change="handlePhotoChange"
            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
          />
          <p class="text-xs text-gray-500 mt-1">Upload a photo of the repair area</p>
        </div>

        <!-- Amount Display -->
        <div class="bg-gray-50 rounded-lg p-4 mt-4">
          <div class="flex justify-between text-lg font-bold">
            <span class="text-gray-900">Total:</span>
            <span class="text-green-600">{{ formatCurrency(serviceAmount) }}</span>
          </div>
        </div>

        <!-- Remarks -->
        <div class="mt-4">
          <InputLabel for="service_remarks" value="Remarks (Optional)" />
          <textarea
            id="service_remarks"
            v-model="serviceForm.remarks"
            rows="2"
            class="mt-1 block w-full px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
            placeholder="Add any notes about this service..."
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 mt-6">
          <button
            type="button"
            @click="closeServiceModal"
            class="flex-1 py-3 px-4 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submitService"
            class="flex-1 py-3 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700"
            :disabled="(!selectedService && serviceMode === 'catalog') || (!serviceForm.custom_name && serviceMode === 'custom')"
          >
            Save Service
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>
