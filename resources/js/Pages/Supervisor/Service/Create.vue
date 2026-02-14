<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({ 
    room: Object, 
    catalog: Array 
});

const serviceMode = ref('CATALOG'); // 'CATALOG' or 'CUSTOM'
const selectedService = ref(null);
const enterAreaDirectly = ref(false);
const formManualArea = ref(null);

// Form for submission
const form = useForm({
    master_service_id: null,
    custom_name: '',
    custom_description: '',
    unit_type: 'AREA',
    length: 0,
    height: 0,
    manual_area: null,
    quantity: 1,
    rate: 0,
    is_repair: false,
    remarks: ''
});

// Reset form when mode changes
watch(serviceMode, (mode) => {
    if (mode === 'CUSTOM') {
        form.master_service_id = null;
        selectedService.value = null;
        form.custom_name = '';
        form.custom_description = '';
        form.unit_type = 'AREA';
        form.length = 0;
        form.height = 0;
        form.quantity = 1;
        form.rate = 0;
        form.is_repair = false;
        form.remarks = '';
    } else {
        form.custom_name = '';
        form.custom_description = '';
        form.rate = 0;
        form.remarks = '';
    }
});

// Auto-fill from Catalog
watch(selectedService, (item) => {
    if (item) {
        form.master_service_id = item.id;
        form.custom_name = '';
        form.custom_description = '';
        form.unit_type = item.unit_type;
        form.length = 0;
        form.height = 0;
        form.manual_area = null;
        form.quantity = 1;
        form.rate = item.default_rate || 0;
        form.is_repair = item.is_repair === 1;
        // Auto-fill remarks from master service
        form.remarks = item.remarks || '';
        enterAreaDirectly.value = false;
    }
});

// Calculate Final Quantity based on Unit Type
const calculatedQuantity = computed(() => {
    if (form.unit_type === 'AREA') {
        if (enterAreaDirectly.value && formManualArea.value) {
            return parseFloat(formManualArea.value).toFixed(2);
        }
        return (form.length * form.height).toFixed(2);
    }
    if (form.unit_type === 'LINEAR') {
        return form.length.toFixed(2);
    }
    return form.quantity;
});

// Calculate Total Amount
const calculatedAmount = computed(() => {
    return (parseFloat(calculatedQuantity.value) * form.rate).toFixed(2);
});

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        quantity: parseFloat(calculatedQuantity.value),
        amount: parseFloat(calculatedAmount.value)
    })).post(route('supervisor.zones.service.store', props.room.id));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-24">
        <!-- Header -->
        <div class="bg-white border-b p-4 shadow-sm sticky top-0 z-10">
            <Link :href="route('supervisor.projects.show', room?.project_id)" class="text-blue-600 text-sm font-medium block mb-2">← Back to Project</Link>
            <h1 class="text-xl font-bold text-gray-900">Add Service - {{ room?.name || 'Zone' }}</h1>
        </div>

        <div class="max-w-lg mx-auto px-4 py-6 space-y-4">
            <!-- Error State: Empty Catalog -->
            <div v-if="!catalog || catalog.length === 0" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                <div class="text-red-600 text-5xl mb-3">⚠️</div>
                <h2 class="text-lg font-bold text-red-800 mb-2">No Services Available</h2>
                <p class="text-red-600 mb-4">
                    Unable to load services catalog. Please contact your administrator.
                </p>
                <button 
                    @click="$inertia.get(route('supervisor.dashboard'))"
                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                >
                    Return to Dashboard
                </button>
            </div>

            <!-- Normal Form -->
            <form v-else @submit.prevent="submit" class="space-y-4">
                <!-- Category Toggle -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">1. SERVICE TYPE</h2>
                    
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer flex-1">
                            <input type="radio" v-model="serviceMode" value="CATALOG" class="w-4 h-4 text-blue-600">
                            <span class="ml-2 text-sm font-medium" :class="serviceMode === 'CATALOG' ? 'text-blue-600' : 'text-gray-600'">• Catalog Service</span>
                        </label>
                        <label class="flex items-center cursor-pointer flex-1">
                            <input type="radio" v-model="serviceMode" value="CUSTOM" class="w-4 h-4 text-blue-600">
                            <span class="ml-2 text-sm font-medium" :class="serviceMode === 'CUSTOM' ? 'text-blue-600' : 'text-gray-600'">• Custom Write-in</span>
                        </label>
                    </div>
                </div>

                <!-- Catalog Mode: Service Dropdown -->
                <div v-if="serviceMode === 'CATALOG'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">2. SELECT SERVICE</h2>
                    
                    <select v-model="selectedService" class="w-full p-3 border border-gray-300 rounded-lg h-12 bg-gray-50">
                        <option :value="null">-- Choose Service --</option>
                        <option v-for="s in catalog" :key="s.id" :value="s">
                            {{ s.name }} ({{ s.unit_type }})
                        </option>
                    </select>
                </div>

                <!-- Custom Mode: Manual Entry -->
                <div v-if="serviceMode === 'CUSTOM'" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">2. CUSTOM SERVICE</h2>
                    
                    <div class="mb-4">
                        <InputLabel for="custom_name" value="Service Name" class="text-sm font-medium text-gray-700" />
                        <TextInput id="custom_name" v-model="form.custom_name" type="text" class="mt-1 h-12" placeholder="e.g., Special Cleaning" />
                    </div>
                    
                    <div class="mb-4">
                        <InputLabel for="custom_description" value="Description (Optional)" class="text-sm font-medium text-gray-700" />
                        <textarea id="custom_description" v-model="form.custom_description" class="w-full p-3 border border-gray-300 rounded-lg h-20" placeholder="Describe the service..."></textarea>
                    </div>
                </div>

                <!-- Measurements -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">3. MEASUREMENTS</h2>
                    
                    <!-- AREA Type: With Direct Area Toggle -->
                    <div v-if="form.unit_type === 'AREA'">
                        <!-- Enter Area Directly Checkbox -->
                        <div class="mb-4">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" v-model="enterAreaDirectly" class="w-4 h-4 text-blue-600 rounded">
                                <span class="ml-2 text-sm font-medium text-gray-700">Enter Area Directly</span>
                            </label>
                        </div>

                        <!-- Direct Area Input -->
                        <div v-if="enterAreaDirectly" class="mb-4">
                            <InputLabel for="manual_area" value="Total Area (sqft)" class="text-sm font-medium text-gray-700" />
                            <TextInput id="manual_area" v-model="formManualArea" type="number" step="0.01" class="mt-1 h-12" />
                        </div>

                        <!-- Length x Height Inputs -->
                        <div v-else class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="length" value="Length (ft)" class="text-sm font-medium text-gray-700" />
                                <TextInput id="length" v-model="form.length" type="number" step="0.01" class="mt-1 h-12" />
                            </div>
                            <div>
                                <InputLabel for="height" value="Height (ft)" class="text-sm font-medium text-gray-700" />
                                <TextInput id="height" v-model="form.height" type="number" step="0.01" class="mt-1 h-12" />
                            </div>
                        </div>
                    </div>

                    <!-- LINEAR Type: Length Only -->
                    <div v-if="form.unit_type === 'LINEAR'">
                        <InputLabel for="linear_length" value="Length (Rft)" class="text-sm font-medium text-gray-700" />
                        <TextInput id="linear_length" v-model="form.length" type="number" step="0.01" class="mt-1 h-12" />
                    </div>

                    <!-- COUNT Type: Quantity -->
                    <div v-if="['COUNT', 'LUMPSUM'].includes(form.unit_type)">
                        <InputLabel for="quantity" value="Quantity / Nos" class="text-sm font-medium text-gray-700" />
                        <TextInput id="quantity" v-model="form.quantity" type="number" class="mt-1 h-12" />
                    </div>

                    <!-- Rate Input -->
                    <div class="mt-4">
                        <InputLabel for="rate" value="Rate (₹)" class="text-sm font-medium text-gray-700" />
                        <TextInput id="rate" v-model="form.rate" type="number" step="0.01" class="mt-1 h-12" />
                    </div>

                    <!-- Pricing Display -->
                    <div class="mt-4 p-3 bg-gray-100 rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">
                                {{ form.unit_type === 'AREA' ? `${form.length} x ${form.height}` : (form.unit_type === 'LINEAR' ? form.length : form.quantity) }}
                                {{ form.unit_type === 'AREA' ? 'sqft' : (form.unit_type === 'LINEAR' ? 'Rft' : 'Qty') }}
                                × {{ formatCurrency(form.rate) }}
                            </span>
                            <span class="font-bold text-lg text-blue-600">{{ formatCurrency(calculatedAmount) }}</span>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="mt-4">
                        <InputLabel for="remarks" value="Remarks (Optional)" class="text-sm font-medium text-gray-700" />
                        <textarea
                            id="remarks"
                            v-model="form.remarks"
                            rows="2"
                            class="w-full p-3 border border-gray-300 rounded-lg mt-1"
                            placeholder="Add any notes about this service..."
                        ></textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t shadow-lg">
                    <button 
                        type="submit" 
                        :disabled="(serviceMode === 'CATALOG' && !selectedService) || (serviceMode === 'CUSTOM' && !form.custom_name)"
                        class="w-full bg-green-600 text-white py-4 rounded-lg font-bold text-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Save Service - {{ formatCurrency(calculatedAmount) }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
