<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, usePage, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
  room: Object,
  surfaces: Array,
  products: Array,
});

const page = usePage();

// Local state
const selectedSurface = ref(null);
const selectedTier = ref('all');
const selectedProduct = ref(null);
const selectedSystem = ref(null);
const measurementMode = ref('ZONE_DEFAULT');
const pricingMode = ref('CALCULATED');

// Edit Zone state
const isEditing = ref(false);
const editForm = useForm({
  name: props.room.name,
  type: props.room.type,
  length: props.room.length,
  breadth: props.room.breadth,
  height: props.room.height,
});

const submitEdit = () => {
  editForm.put(route('supervisor.rooms.update', props.room.id), {
    onSuccess: () => {
      isEditing.value = false;
    },
  });
};

// Form measurements (for manual mode)
const formLength = ref(null);
const formBreadth = ref(null);
const formHeight = ref(null);
const deductions = ref(0);

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

const submit = () => {
  form.post(route('supervisor.rooms.items.store', props.room.id), {
    onSuccess: () => {
      // Reset form after success
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
</script>

<template>
  <div class="min-h-screen bg-gray-50 pb-20">
    <!-- Header: Zone Name & Dimensions -->
    <div class="bg-white border-b p-4 shadow-sm relative">
      <div class="flex justify-between items-start mb-2">
        <Link :href="route('supervisor.projects.show', room.project_id)" class="text-blue-600 text-sm font-medium">← Back to Project</Link>
        <button @click="isEditing = !isEditing" class="text-gray-400 hover:text-blue-600 text-sm underline">
          {{ isEditing ? 'Cancel' : 'Edit Zone' }}
        </button>
      </div>

      <div v-if="!isEditing">
        <h1 class="text-2xl font-bold text-gray-900">{{ room.name }}</h1>
        <div class="flex items-center gap-2 mt-1">
          <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full uppercase tracking-wide">{{ room.type }}</span>
          <span class="text-gray-500 text-sm">Dim: {{ room.length || 0 }}' x {{ room.breadth || 0 }}' x {{ room.height || 0 }}'</span>
        </div>
      </div>

      <div v-else class="space-y-3 mt-2">
        <input v-model="editForm.name" type="text" class="w-full border-gray-300 rounded-md shadow-sm text-lg font-bold p-2" placeholder="Zone Name">
        
        <div class="flex gap-4">
          <label class="flex items-center gap-2">
            <input type="radio" v-model="editForm.type" value="INTERIOR" class="text-blue-600"> Interior
          </label>
          <label class="flex items-center gap-2">
            <input type="radio" v-model="editForm.type" value="EXTERIOR" class="text-blue-600"> Exterior
          </label>
        </div>

        <div class="grid grid-cols-3 gap-2">
          <div><label class="text-xs text-gray-500">L</label><input v-model="editForm.length" type="number" class="w-full border-gray-300 rounded-md p-1"></div>
          <div><label class="text-xs text-gray-500">B</label><input v-model="editForm.breadth" type="number" class="w-full border-gray-300 rounded-md p-1"></div>
          <div><label class="text-xs text-gray-500">H</label><input v-model="editForm.height" type="number" class="w-full border-gray-300 rounded-md p-1"></div>
        </div>

        <button @click="submitEdit" :disabled="editForm.processing" class="w-full bg-blue-600 text-white font-bold py-2 rounded-md shadow-sm hover:bg-blue-700">
          Save Changes
        </button>
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
            <option v-for="surface in surfaces" :key="surface.id" :value="surface.id">
              {{ surface.name }} ({{ surface.unit_type }})
            </option>
          </select>
          <InputError :message="form.errors.surface_id" class="mt-2" />
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
  </div>
</template>
