<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    zone: Object,
    services: {
        type: Array,
        default: () => []
    },
    editService: Object, // Optional - present when editing
});

// Check if we're in edit mode
const isEditMode = computed(() => !!props.editService);

const form = useForm({
    master_service_id: null,
    custom_name: '',
    unit_type: 'AREA',
    length: null,
    breadth: null,
    count: null,
    manual_area: null,
    qty: 0,
    rate: 0,
    amount: 0,
    remarks: '',
});

const areaMode = ref('DIMENSIONS'); // DIMENSIONS | DIRECT

// Pre-fill form with edit data
onMounted(() => {
    if (isEditMode.value && props.editService) {
        const service = props.editService;
        form.master_service_id = service.master_service_id;
        form.custom_name = service.custom_name || '';
        form.unit_type = service.unit_type || 'AREA';
        form.length = service.length || null;
        form.breadth = service.breadth || null;
        form.count = service.count || null;
        form.qty = service.quantity || 0;
        form.rate = service.rate || 0;
        form.amount = service.amount || 0;
        form.remarks = service.remarks || '';
    }
});

const serviceMode = ref('CATALOG');
const submitting = ref(false);

const selectedService = computed(() => {
    if (!form.master_service_id) return null;
    return props.services.find(s => s.id === form.master_service_id);
});

const calculatedAmount = computed(() => {
    const qty = getQuantity();
    return qty * (form.rate || 0);
});

const getQuantity = () => {
    switch (form.unit_type) {
        case 'AREA':
            if (areaMode.value === 'DIRECT') {
                return form.manual_area || 0;
            }
            return (form.length || 0) * (form.breadth || 0);
        case 'LINEAR':
            return form.length || 0;
        case 'COUNT':
            return form.count || 0;
        default:
            return 0;
    }
};

watch(() => form.unit_type, (newType) => {
    form.length = null;
    form.breadth = null;
    form.count = null;
});

watch(selectedService, (newService) => {
    if (newService) {
        console.log('Service object:', newService);
        form.unit_type = newService.unit_type || 'AREA';
        form.rate = newService.default_rate || 0;
        // Auto-populate custom_name with service name for catalog services
        form.custom_name = newService.name || '';
        // Auto-populate remarks from master service if available
        form.remarks = newService.remarks || '';
    }
});

const submit = () => {
    submitting.value = true;

    const qty = getQuantity();
    const amount = qty * (form.rate || 0);

    // Add calculated fields to form data
    form.qty = qty;
    form.amount = amount;

    if (isEditMode.value) {
        // Update existing service
        form.put(
            route('supervisor.zones.service.update', { projectRoom: props.zone.id, service: props.editService.id }),
            {
                onSuccess: () => {
                    submitting.value = false;
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors);
                    submitting.value = false;
                },
                onFinish: () => {
                    submitting.value = false;
                }
            }
        );
    } else {
        // Create new service
        form.post(
            route('supervisor.zones.service.store', { projectRoom: props.zone.id }),
            {
                onSuccess: () => {
                    submitting.value = false;
                },
                onError: (errors) => {
                    console.log('Validation errors:', errors);
                    submitting.value = false;
                },
                onFinish: () => {
                    submitting.value = false;
                }
            }
        );
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};
</script>
<template>
    <form @submit.prevent="submit" class="min-h-screen bg-gray-50 pb-24">
        <!-- Header - Screen D Wireframe -->
        <div class="bg-white border-b p-4 shadow-sm sticky top-0 z-10">
            <Link :href="route('supervisor.zones.show', zone?.id)" class="text-blue-600 text-sm font-medium block mb-1">← Back</Link>
            <h1 class="text-xl font-bold text-gray-900">ADD SERVICE / REPAIR</h1>
        </div>




            <!-- MODE TOGGLE - Screen D Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex gap-4">
                    <label class="flex items-center cursor-pointer flex-1">
                        <input type="radio" v-model="serviceMode" value="CATALOG" class="w-4 h-4 text-blue-600">
                        <span class="ml-2 text-sm font-medium" :class="serviceMode === 'CATALOG' ? 'text-blue-600' : 'text-gray-600'">
                            {{ serviceMode === 'CATALOG' ? '•' : '( )' }} Catalog
                        </span>
                    </label>
                    <label class="flex items-center cursor-pointer flex-1">
                        <input type="radio" v-model="serviceMode" value="CUSTOM" class="w-4 h-4 text-blue-600">
                        <span class="ml-2 text-sm font-medium" :class="serviceMode === 'CUSTOM' ? 'text-blue-600' : 'text-gray-600'">
                            {{ serviceMode === 'CUSTOM' ? '•' : '( )' }} Custom
                        </span>
                    </label>
                </div>
            </div>

            <!-- 1. SELECT SERVICE - Screen D Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">1. SELECT SERVICE</h2>

                <div v-if="serviceMode === 'CATALOG'">
                    <select v-model="form.master_service_id" class="w-full p-3 border border-gray-300 rounded-lg h-12 bg-gray-50">
                        <option :value="null">-- Choose Service --</option>
                        <option v-for="service in services" :key="service.id" :value="service.id">
                            {{ service.name }} ({{ formatCurrency(service.default_rate) }}/{{ service.unit_type?.toLowerCase() }})
                        </option>
                    </select>

                    <div v-if="selectedService" class="mt-3 p-3 bg-gray-100 rounded-lg">
                        <p class="text-sm text-gray-600">
                            <span class="font-medium">*System detects Unit: {{ selectedService.unit_type }}*</span>
                        </p>
                        <p v-if="selectedService.is_repair" class="text-sm text-orange-600 font-medium">
                            *System detects Is Repair: YES*
                        </p>
                    </div>
                </div>

                <div v-else class="space-y-4">
                    <div>
                        <InputLabel for="custom_name" value="Service Name" class="text-sm font-medium text-gray-700" />
                        <TextInput id="custom_name" v-model="form.custom_name" type="text" class="mt-1 h-12 border-gray-300" placeholder="e.g., Patching Work" />
                    </div>
                    <div>
                        <InputLabel for="unit_type" value="Unit Type" class="text-sm font-medium text-gray-700" />
                        <select v-model="form.unit_type" class="w-full p-3 border border-gray-300 rounded-lg h-12 bg-gray-50">
                            <option value="AREA">Area (sqft)</option>
                            <option value="LINEAR">Linear (ft)</option>
                            <option value="COUNT">Count (pcs)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. MEASUREMENTS (Dynamic UI) - Screen D Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">2. MEASUREMENTS</h2>

                <!-- IF UNIT == 'AREA' -->
                <div v-if="form.unit_type === 'AREA'" class="space-y-4">
                    <!-- Area Mode Toggle -->
                    <div class="bg-gray-100 rounded-lg p-1 flex">
                        <button
                            type="button"
                            @click="areaMode = 'DIMENSIONS'"
                            class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors"
                            :class="areaMode === 'DIMENSIONS' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'"
                        >
                            Length × Height
                        </button>
                        <button
                            type="button"
                            @click="areaMode = 'DIRECT'"
                            class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors"
                            :class="areaMode === 'DIRECT' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-600'"
                        >
                            Enter Area Directly
                        </button>
                    </div>

                    <!-- DIMENSIONS Mode -->
                    <div v-if="areaMode === 'DIMENSIONS'" class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="length" value="Length (ft)" class="text-sm font-medium text-gray-700" />
                            <TextInput id="length" v-model="form.length" type="number" step="0.01" class="mt-1 h-12 border-gray-300" />
                        </div>
                        <div>
                            <InputLabel for="breadth" value="Height (ft)" class="text-sm font-medium text-gray-700" />
                            <TextInput id="breadth" v-model="form.breadth" type="number" step="0.01" class="mt-1 h-12 border-gray-300" />
                        </div>
                    </div>

                    <!-- DIRECT Mode -->
                    <div v-if="areaMode === 'DIRECT'">
                        <InputLabel for="manual_area" value="Area (sqft)" class="text-sm font-medium text-gray-700" />
                        <TextInput id="manual_area" v-model="form.manual_area" type="number" step="0.01" class="mt-1 h-12 border-gray-300" placeholder="e.g., 120" />
                    </div>
                </div>

                <!-- IF UNIT == 'LINEAR' -->
                <div v-if="form.unit_type === 'LINEAR'">
                    <InputLabel for="linear_length" value="Running Feet" class="text-sm font-medium text-gray-700" />
                    <TextInput id="linear_length" v-model="form.length" type="number" step="0.01" class="mt-1 h-12 border-gray-300" placeholder="e.g., 50" />
                </div>

                <!-- IF UNIT == 'COUNT' -->
                <div v-if="form.unit_type === 'COUNT'">
                    <InputLabel for="count" value="Quantity (Nos)" class="text-sm font-medium text-gray-700" />
                    <TextInput id="count" v-model="form.count" type="number" step="1" class="mt-1 h-12 border-gray-300" />
                </div>
            </div>

            <!-- 3. RATE & TOTAL - Screen D Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">4. RATE & TOTAL</h2>

                <div class="mb-4">
                    <InputLabel for="rate" value="Rate (₹)" class="text-sm font-medium text-gray-700" />
                    <TextInput id="rate" v-model="form.rate" type="number" step="0.01" class="mt-1 h-12 border-gray-300" />
                </div>

                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm text-blue-800 mb-1">
                        {{ getQuantity().toFixed(2) }} {{ form.unit_type === 'AREA' ? 'sqft' : form.unit_type === 'LINEAR' ? 'Rft' : 'Nos' }} × {{ formatCurrency(form.rate) }}
                    </p>
                    <p class="text-xl font-bold text-blue-600">{{ formatCurrency(calculatedAmount) }}</p>
                </div>
            </div>

            <!-- REMARKS -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3 border-b pb-2">REMARKS</h2>
                <div>
                    <InputLabel for="remarks" value="Remarks (Optional)" class="text-sm font-medium text-gray-700" />
                    <textarea
                        id="remarks"
                        v-model="form.remarks"
                        rows="3"
                        class="mt-1 block w-full px-3 border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 h-24"
                        placeholder="Add any notes about this service..."
                    ></textarea>
                </div>
            </div>

            <!-- SAVE BUTTON -->
            <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t shadow-lg">
                <button
                    type="button"
                    @click="submit"
                    :disabled="submitting"
                    class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg disabled:opacity-50"
                >
                    {{ submitting ? 'Saving...' : 'SAVE SERVICE' }}
                </button>
            </div>
        </form>
    </template>
