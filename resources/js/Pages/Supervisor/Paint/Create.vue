<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';

// Props from controller
const props = defineProps({
    zone: Object,
    surfaces: Array,
    editItem: Object, // Optional - present when editing
});

// Check if we're in edit mode
const isEditMode = computed(() => !!props.editItem);

// Reactive state for surface selection
const selectedSurfaceId = ref('');

// Modal states
const showDeductionModal = ref(false);

// Form state
const form = useForm({
    product_id: '',
    system_id: '',
    unit_type: '',
    // Measurement source toggle
    measurement_source: 'ROOM_DEFAULT', // 'ROOM_DEFAULT' or 'MANUAL'
    // Measurements
    length: 0,
    height: 0,
    breadth: 0,
    quantity: 1,
    direct_area: 0,
    // Manual area mode for MANUAL + AREA
    manual_area_mode: 'DIMENSIONS', // 'DIMENSIONS' or 'DIRECT'
    // Lumpsum
    lumpsum_amount: 0,
    // Tier filter
    selected_tier: '',
    // Deductions
    deductions: [],
    manual_deduction_sqft: 0,
    // Color
    color: '',
    // Pricing Mode
    pricing_mode: 'CALCULATED',
    // Description
    description: '',
});

// Pre-fill form with edit data
onMounted(() => {
    if (isEditMode.value && props.editItem) {
        const item = props.editItem;
        selectedSurfaceId.value = item.surface_id;
        form.product_id = item.master_product_id;
        form.system_id = item.master_system_id;
        form.measurement_source = item.measurement_mode || 'ROOM_DEFAULT';
        form.lumpsum_amount = item.manual_price || 0;
        form.pricing_mode = item.pricing_mode || 'CALCULATED';
        form.color = item.color_code || '';
        form.description = item.description || '';
        form.manual_deduction_sqft = item.manual_deduction_sqft || 0;

        // Parse deductions if stored as JSON string
        if (item.deductions) {
            try {
                form.deductions = typeof item.deductions === 'string'
                    ? JSON.parse(item.deductions)
                    : item.deductions;
            } catch (e) {
                form.deductions = [];
            }
        }

        // Set tier based on product
        if (item.product) {
            form.selected_tier = item.product.tier || '';
        }
    }
});

// Watch for surface changes to reset dependent fields
watch(selectedSurfaceId, (newVal, oldVal) => {
    // Only reset if we're not pre-filling from edit mode
    if (!isEditMode.value || newVal !== props.editItem?.surface_id) {
        form.selected_tier = '';
        form.product_id = '';
        form.system_id = '';
        form.deductions = [];
        form.manual_deduction_sqft = 0;
    }
});

// Tier options
const tierOptions = [
    { value: '', label: 'ALL' },
    { value: 'ECONOMY', label: 'Economy' },
    { value: 'PREMIUM', label: 'Premium' },
    { value: 'LUXURY', label: 'Luxury' },
];

// Computed: Filtered Surfaces (by zone type)
const filteredSurfaces = computed(() => {
    if (!props.surfaces || !props.zone) return [];

    const zoneType = props.zone.type || 'INTERIOR';

    // Filter surfaces based on zone type
    return props.surfaces.filter(surface => {
        // If zone is INTERIOR, show INTERIOR and BOTH surfaces
        if (zoneType === 'INTERIOR') {
            return surface.category === 'INTERIOR' || surface.category === 'BOTH';
        }
        // If zone is EXTERIOR, show EXTERIOR and BOTH surfaces
        if (zoneType === 'EXTERIOR') {
            return surface.category === 'EXTERIOR' || surface.category === 'BOTH';
        }
        // Default: show all surfaces
        return true;
    });
});

// Computed: Selected Surface
const selectedSurface = computed(() => {
    return filteredSurfaces.value?.find(s => s.id === Number(selectedSurfaceId.value));
});

// Computed: Filtered Products (by tier)
const filteredProducts = computed(() => {
    let products = selectedSurface.value?.products || [];
    if (form.selected_tier) {
        products = products.filter(p => p.tier?.toUpperCase() === form.selected_tier.toUpperCase());
    }
    return products;
});

// Computed: Selected Product
const selectedProduct = computed(() =>
    filteredProducts.value.find(p => p.id === Number(form.product_id))
);

// Computed: Available Systems (from selected product)
const availableSystems = computed(() =>
    selectedProduct.value?.systems || []
);

// Computed: Selected System
const selectedSystem = computed(() =>
    availableSystems.value.find(s => s.id === form.system_id)
);

// Computed: Room Default Area (for AREA surfaces)
const roomDefaultArea = computed(() => {
    if (!props.zone) return 0;
    const length = Number(props.zone.length) || 0;
    const breadth = Number(props.zone.breadth) || 0;
    const height = Number(props.zone.height) || 0;

    // Wall area: perimeter × height = 2 × (L + W) × H
    if (length > 0 && breadth > 0 && height > 0) {
        return 2 * (length + breadth) * height;
    }
    // Fallback: L × H
    if (length > 0 && height > 0) {
        return length * height;
    }
    return 0;
});

// Computed: Gross Quantity
const grossQty = computed(() => {
    if (!selectedSurface.value) return 0;

    if (form.measurement_source === 'ROOM_DEFAULT') {
        if (selectedSurface.value.unit_type === 'AREA') {
            const area = roomDefaultArea.value;
            return isNaN(area) ? 0 : area;
        } else if (selectedSurface.value.unit_type === 'LINEAR') {
            const length = Number(props.zone?.length) || 0;
            return isNaN(length) ? 0 : length;
        } else if (selectedSurface.value.unit_type === 'COUNT') {
            return 1;
        } else {
            return 1; // LUMPSUM
        }
    } else {
        // MANUAL mode
        if (selectedSurface.value.unit_type === 'AREA') {
            if (form.manual_area_mode === 'DIRECT') {
                const area = Number(form.direct_area) || 0;
                return isNaN(area) ? 0 : area;
            }
            const length = Number(form.length) || 0;
            const height = Number(form.height) || 0;
            const result = length * height;
            return isNaN(result) ? 0 : result;
        } else if (selectedSurface.value.unit_type === 'LINEAR') {
            const length = Number(form.length) || 0;
            return isNaN(length) ? 0 : length;
        } else if (selectedSurface.value.unit_type === 'COUNT') {
            const qty = Number(form.quantity) || 1;
            return isNaN(qty) ? 1 : qty;
        } else {
            return 1; // LUMPSUM
        }
    }
});

// Computed: Total Deductions Area (helper + manual)
const totalDeductions = computed(() => {
    const helperDeductions = form.deductions.reduce((sum, d) => {
        const area = Number(d.area) || 0;
        return sum + (isNaN(area) ? 0 : area);
    }, 0);
    const manualDeduction = Number(form.manual_deduction_sqft) || 0;
    const result = helperDeductions + (isNaN(manualDeduction) ? 0 : manualDeduction);
    return isNaN(result) ? 0 : result;
});

// Computed: Net Quantity
const netQty = computed(() => {
    const gross = grossQty.value;
    const deductions = totalDeductions.value;
    const result = Math.max(0, gross - deductions);
    return isNaN(result) ? 0 : result;
});

// Computed: Calculated Amount
const calculatedAmount = computed(() => {
    if (form.pricing_mode === 'LUMPSUM') {
        const amount = Number(form.lumpsum_amount) || 0;
        return isNaN(amount) ? 0 : amount;
    }
    if (selectedSurface.value?.unit_type === 'LUMPSUM') {
        const amount = Number(form.lumpsum_amount) || 0;
        return isNaN(amount) ? 0 : amount;
    }
    const rate = Number(selectedSystem.value?.base_rate) || 0;
    const qty = netQty.value;
    const result = qty * rate;
    return isNaN(result) ? 0 : result;
});

// Computed: Show Manual Area Input
const showManualAreaInput = computed(() => {
    return selectedSurface.value?.unit_type === 'AREA' &&
           form.measurement_source === 'MANUAL' &&
           form.manual_area_mode === 'DIRECT';
});

// Computed: Show Dimensions Input
const showDimensionsInput = computed(() => {
    if (selectedSurface.value?.unit_type !== 'AREA') return false;

    if (form.measurement_source === 'ROOM_DEFAULT') {
        return true; // Show room dimensions info
    }
    return form.measurement_source === 'MANUAL' && form.manual_area_mode === 'DIMENSIONS';
});

// Deduction helper types
const deductionTypes = [
    { type: 'door', label: 'Door', defaultArea: 21 }, // 7×3 ft
    { type: 'window', label: 'Window', defaultArea: 15 }, // 5×3 ft
    { type: 'vent', label: 'Vent', defaultArea: 6 }, // 3×2 ft
    { type: 'other', label: 'Other', defaultArea: 0 },
];

// Add deduction
const addDeduction = (type) => {
    const defaultArea = deductionTypes.find(d => d.type === type)?.defaultArea || 0;
    form.deductions.push({
        id: Date.now(),
        type,
        count: 1,
        area: defaultArea,
    });
};

// Remove deduction
const removeDeduction = (id) => {
    form.deductions = form.deductions.filter(d => d.id !== id);
};

// Update deduction count
const updateDeductionCount = (id, count) => {
    const deduction = form.deductions.find(d => d.id === id);
    if (deduction) {
        const defaultArea = deductionTypes.find(d => d.type === deduction.type)?.defaultArea || 0;
        deduction.count = count;
        deduction.area = defaultArea * count;
    }
};

// Submit form
const submit = () => {
    const data = {
        surface_id: Number(selectedSurfaceId.value),
        product_id: Number(form.product_id),
        system_id: Number(form.system_id),
        unit_type: selectedSurface.value?.unit_type,
        measurement_source: form.measurement_source,
        // Manual measurements
        length: form.measurement_source === 'MANUAL' ? form.length : 0,
        height: form.measurement_source === 'MANUAL' ? form.height : 0,
        breadth: form.measurement_source === 'MANUAL' ? form.breadth : 0,
        direct_area: form.measurement_source === 'MANUAL' && form.manual_area_mode === 'DIRECT' ? form.direct_area : 0,
        manual_area_mode: form.manual_area_mode,
        quantity: form.quantity,
        lumpsum_amount: form.lumpsum_amount,
        // Deductions
        deductions: form.deductions,
        manual_deduction_sqft: form.manual_deduction_sqft || 0,
        // Color
        color: form.color || null,
        // Pricing Mode
        pricing_mode: form.pricing_mode,
        // Description
        description: form.description || null,
    };

    if (isEditMode.value) {
        // Update existing item
        form.transform(() => data).put(route('supervisor.zones.paint.update', [props.zone.id, props.editItem.id]));
    } else {
        // Create new item
        form.transform(() => data).post(route('supervisor.zones.paint.store', props.zone.id));
    }
};
</script>

<template>
    <div class="p-4 bg-gray-50 min-h-screen pb-20">
        <!-- Header with back button -->
        <div class="flex items-center gap-2 mb-4">
            <Link :href="route('supervisor.zones.show', zone.id)" class="font-bold text-xl text-gray-600">
                ←
            </Link>
            <h1 class="font-bold text-xl">ADD PAINT ITEM</h1>
        </div>

        <!-- Zone info -->
        <div class="bg-blue-100 p-3 rounded mb-4">
            <p class="text-sm text-blue-800">
                <span class="font-bold">{{ zone?.name || 'Room' }}</span>
                <span v-if="zone?.length && zone?.height" class="text-blue-600 ml-2">
                    ({{ zone.length }}' × {{ zone.breadth }}' × {{ zone.height }}'h)
                </span>
            </p>
        </div>

        <!-- Error state -->
        <div v-if="!surfaces || surfaces.length === 0" class="p-4 bg-red-100 text-red-700 rounded">
            ⚠️ Master Data Missing. Please add Surfaces/Products in Admin.
        </div>
        <div v-else-if="filteredSurfaces.length === 0" class="p-4 bg-orange-100 text-orange-700 rounded">
            ⚠️ No surfaces available for {{ zone?.type || 'INTERIOR' }} rooms. Please add surfaces in Admin.
        </div>

        <!-- Form -->
        <form v-else-if="filteredSurfaces.length > 0" @submit.prevent="submit" class="space-y-4">

            <!-- 1) SURFACE SELECTION -->
            <div class="bg-white p-4 rounded shadow">
                <label class="text-xs font-bold text-gray-500 uppercase">1. SURFACE</label>
                <select
                    v-model="selectedSurfaceId"
                    class="w-full border border-gray-300 rounded p-3 h-12 mt-1 bg-white"
                >
                    <option value="">Select Surface...</option>
                    <option v-for="s in filteredSurfaces" :key="s.id" :value="s.id">
                        {{ s.name }} ({{ s.unit_type }})
                    </option>
                </select>
            </div>

            <!-- 2) PRODUCT FILTER (shown after surface selected) -->
            <div v-if="selectedSurfaceId" class="bg-white p-4 rounded shadow">
                <label class="text-xs font-bold text-gray-500 uppercase">2. PRODUCT FILTER</label>
                <div class="flex gap-2 mt-2">
                    <button
                        type="button"
                        v-for="tier in tierOptions"
                        :key="tier.value"
                        @click="form.selected_tier = tier.value"
                        :class="[
                            'px-3 py-2 rounded text-sm font-medium h-10 flex-1',
                            form.selected_tier === tier.value
                                ? 'bg-blue-600 text-white'
                                : 'bg-gray-100 text-gray-600'
                        ]"
                    >
                        {{ tier.label }}
                    </button>
                </div>
            </div>

            <!-- 3) PRODUCT SELECTION -->
            <div class="bg-white p-4 rounded shadow">
                <label class="text-xs font-bold text-gray-500 uppercase">3. PRODUCT</label>
                <select
                    v-model="form.product_id"
                    class="w-full border border-gray-300 rounded p-3 h-12 mt-1 bg-white"
                    :disabled="!selectedSurface || filteredProducts.length === 0"
                >
                    <option value="">Select Product...</option>
                    <option v-for="p in filteredProducts" :key="p.id" :value="p.id">
                        {{ p.name }} ({{ p.brand }}) - {{ p.tier }}
                    </option>
                </select>
                <p v-if="!selectedSurface" class="text-sm text-gray-500 mt-1">
                    Select a surface to view products
                </p>
                <p v-else-if="filteredProducts.length === 0" class="text-sm text-orange-500 mt-1">
                    No products available for selected surface/tier
                </p>
            </div>

            <!-- 4) PAINTING SYSTEM -->
            <div class="bg-white p-4 rounded shadow">
                <label class="text-xs font-bold text-gray-500 uppercase">4. PAINTING SYSTEM</label>
                <select
                    v-model="form.system_id"
                    class="w-full border border-gray-300 rounded p-3 h-12 mt-1 bg-white"
                    :disabled="!selectedProduct || !selectedProduct?.systems?.length"
                >
                    <option value="">Select System...</option>
                    <option v-for="s in availableSystems" :key="s.id" :value="s.id">
                        {{ s.system_name }} {{ s.process_remarks ? `(${s.process_remarks})` : '' }} (₹{{ s.base_rate }} / {{ s.unit }})
                    </option>
                </select>
                <p v-if="!selectedProduct" class="text-sm text-gray-500 mt-1">
                    Select a product to view painting systems
                </p>
                <p v-else-if="!selectedProduct?.systems?.length" class="text-sm text-orange-500 mt-1">
                    No painting systems available for this product
                </p>
            </div>

            <!-- 5) MEASUREMENTS (shown after system selected) -->
            <div v-if="form.system_id" class="bg-white p-4 rounded shadow">
                <h3 class="font-bold border-b pb-2 mb-3">5. MEASUREMENTS</h3>

                <!-- Measurement Source Toggle -->
                <div class="flex gap-4 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            v-model="form.measurement_source"
                            value="ROOM_DEFAULT"
                            class="w-4 h-4 text-blue-600"
                        />
                        <span class="text-sm font-medium">Use Room Default</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            v-model="form.measurement_source"
                            value="MANUAL"
                            class="w-4 h-4 text-blue-600"
                        />
                        <span class="text-sm font-medium">Manual Measure</span>
                    </label>
                </div>

                <!-- AREA: Length × Height OR Direct Area -->
                <div v-if="selectedSurface?.unit_type === 'AREA'" class="space-y-4">
                    <!-- Room Default Info -->
                    <div v-if="form.measurement_source === 'ROOM_DEFAULT'" class="bg-blue-50 p-3 rounded">
                        <p class="text-sm text-blue-800">
                            <span class="font-bold">Room Default:</span>
                            {{ (isNaN(roomDefaultArea) ? 0 : roomDefaultArea).toFixed(2) }} sqft
                            <span v-if="zone?.length && zone?.height" class="text-blue-600 text-xs ml-2">
                                (2 × ({{ Number(zone.length) || 0 }}' + {{ Number(zone.breadth) || 0 }}') × {{ Number(zone.height) || 0 }}'h)
                            </span>
                        </p>
                    </div>

                    <!-- Manual Area Mode Toggle -->
                    <div v-if="form.measurement_source === 'MANUAL'" class="flex gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="form.manual_area_mode"
                                value="DIMENSIONS"
                                class="w-4 h-4 text-blue-600"
                            />
                            <span class="text-sm">Length × Height</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="form.manual_area_mode"
                                value="DIRECT"
                                class="w-4 h-4 text-blue-600"
                            />
                            <span class="text-sm">Enter Area Directly</span>
                        </label>
                    </div>

                    <!-- Option A: Length × Height -->
                    <div v-if="showDimensionsInput" class="flex gap-3">
                        <div class="flex-1">
                            <label class="text-xs font-bold text-gray-500">Length (ft)</label>
                            <input
                                v-model="form.length"
                                type="number"
                                step="0.01"
                                class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                                placeholder="0.00"
                            />
                        </div>
                        <div class="flex-1">
                            <label class="text-xs font-bold text-gray-500">Height (ft)</label>
                            <input
                                v-model="form.height"
                                type="number"
                                step="0.01"
                                class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                                placeholder="0.00"
                            />
                        </div>
                    </div>

                    <!-- Option B: Enter Area Directly -->
                    <div v-if="showManualAreaInput">
                        <label class="text-xs font-bold text-gray-500">Area (sqft)</label>
                        <input
                            v-model="form.direct_area"
                            type="number"
                            step="0.01"
                            class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <!-- LINEAR: Length only -->
                <div v-if="selectedSurface?.unit_type === 'LINEAR'" class="space-y-3">
                    <div v-if="form.measurement_source === 'ROOM_DEFAULT'" class="bg-blue-50 p-3 rounded">
                        <p class="text-sm text-blue-800">
                            <span class="font-bold">Room Default:</span>
                            {{ (Number(zone?.length) || 0).toFixed(2) }} ft
                        </p>
                    </div>
                    <div v-else>
                        <label class="text-xs font-bold text-gray-500">Length (ft)</label>
                        <input
                            v-model="form.length"
                            type="number"
                            step="0.01"
                            class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <!-- COUNT: Quantity only -->
                <div v-if="selectedSurface?.unit_type === 'COUNT'" class="space-y-3">
                    <div>
                        <label class="text-xs font-bold text-gray-500">Quantity</label>
                        <input
                            v-model="form.quantity"
                            type="number"
                            class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                            placeholder="1"
                        />
                    </div>
                </div>

                <!-- LUMPSUM: Manual amount -->
                <div v-if="selectedSurface?.unit_type === 'LUMPSUM'" class="space-y-3">
                    <div>
                        <label class="text-xs font-bold text-gray-500">Lumpsum Amount (₹)</label>
                        <input
                            v-model="form.lumpsum_amount"
                            type="number"
                            step="0.01"
                            class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                            placeholder="0.00"
                        />
                    </div>
                </div>

                <!-- Gross Quantity Display -->
                <div class="mt-4 p-3 bg-gray-100 rounded">
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Gross Qty:</span> {{ (isNaN(grossQty) ? 0 : grossQty).toFixed(2) }}
                        <span v-if="selectedSurface?.unit_type === 'AREA'">sqft</span>
                        <span v-else-if="selectedSurface?.unit_type === 'LINEAR'">ft</span>
                        <span v-else-if="selectedSurface?.unit_type === 'COUNT'">units</span>
                    </p>
                </div>
            </div>

            <!-- 6) DEDUCTIONS (shown after system selected) -->
            <div v-if="form.system_id && selectedSurface?.unit_type === 'AREA'" class="bg-white p-4 rounded shadow">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold border-b-0">6. DEDUCTIONS</h3>
                    <button
                        type="button"
                        @click="showDeductionModal = true"
                        class="text-sm text-blue-600 font-medium"
                    >
                        + Add Deduction
                    </button>
                </div>

                <!-- Deductions List -->
                <div v-if="form.deductions.length > 0" class="space-y-2">
                    <div
                        v-for="deduction in form.deductions"
                        :key="deduction.id"
                        class="flex items-center gap-3 p-2 bg-gray-50 rounded"
                    >
                        <span class="text-sm font-medium flex-1">
                            {{ deductionTypes.find(d => d.type === deduction.type)?.label }}
                        </span>
                        <input
                            type="number"
                            :value="deduction.count"
                            @input="updateDeductionCount(deduction.id, Number($event.target.value))"
                            class="w-16 border border-gray-300 rounded p-2 h-10 text-center"
                            min="1"
                        />
                        <span class="text-sm text-gray-500">×</span>
                        <span class="text-sm font-medium w-20 text-right">
                            {{ deduction.area.toFixed(2) }} sqft
                        </span>
                        <button
                            type="button"
                            @click="removeDeduction(deduction.id)"
                            class="text-red-500 font-bold px-2"
                        >
                            ×
                        </button>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500 italic">
                    No deductions added
                </p>

                <!-- Manual Deduction Input -->
                <div class="mt-3">
                    <label class="text-xs font-bold text-gray-500">Custom Area Deduction (sqft)</label>
                    <input
                        v-model="form.manual_deduction_sqft"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                        placeholder="0.00"
                    />
                </div>

                <!-- Total Deductions -->
                <div class="mt-3 p-2 bg-orange-50 rounded">
                    <p class="text-sm text-orange-700 font-medium">
                        Total Deductions: {{ (isNaN(totalDeductions) ? 0 : totalDeductions).toFixed(2) }} sqft
                    </p>
                </div>
            </div>

            <!-- 7) COLOR SELECTION -->
            <div v-if="form.system_id" class="bg-white p-4 rounded shadow">
                <h3 class="font-bold border-b pb-2 mb-3">7. COLOR</h3>
                <input
                    v-model="form.color"
                    type="text"
                    class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                    placeholder="e.g., Morning Glory 0765, Snow White"
                />
                <p class="text-xs text-gray-500 mt-1">
                    Enter color names separated by commas
                </p>
            </div>

            <!-- 8) PRICING MODE -->
            <div v-if="form.system_id" class="bg-white p-4 rounded shadow">
                <h3 class="font-bold border-b pb-2 mb-3">8. PRICING MODE</h3>

                <div class="flex gap-4 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            v-model="form.pricing_mode"
                            value="CALCULATED"
                            class="w-4 h-4 text-blue-600"
                        />
                        <span class="text-sm font-medium">Calculated</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            v-model="form.pricing_mode"
                            value="LUMPSUM"
                            class="w-4 h-4 text-blue-600"
                        />
                        <span class="text-sm font-medium">Lumpsum</span>
                    </label>
                </div>

                <!-- Lumpsum Amount Input -->
                <div v-if="form.pricing_mode === 'LUMPSUM'" class="mt-3">
                    <label class="text-xs font-bold text-gray-500">Enter Lumpsum Amount (₹)</label>
                    <input
                        v-model="form.lumpsum_amount"
                        type="number"
                        step="0.01"
                        class="w-full border border-gray-300 rounded p-3 h-12 mt-1"
                        placeholder="0.00"
                    />
                </div>
            </div>

            <!-- 9) PRICING SUMMARY -->
            <div v-if="form.system_id" class="bg-green-50 p-4 rounded shadow border-2 border-green-200">
                <h3 class="font-bold text-green-800 mb-3">PRICING SUMMARY</h3>

                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Net Quantity:</span>
                        <span class="font-medium">{{ (isNaN(netQty) ? 0 : netQty).toFixed(2) }}
                            <span v-if="selectedSurface?.unit_type === 'AREA'">sqft</span>
                            <span v-else-if="selectedSurface?.unit_type === 'LINEAR'">ft</span>
                            <span v-else-if="selectedSurface?.unit_type === 'COUNT'">units</span>
                        </span>
                    </div>

                    <div v-if="form.pricing_mode === 'CALCULATED' && selectedSurface?.unit_type !== 'LUMPSUM'" class="flex justify-between text-sm">
                        <span class="text-gray-600">Rate:</span>
                        <span class="font-medium">₹{{ selectedSystem?.base_rate || 0 }} /
                            <span v-if="selectedSurface?.unit_type === 'AREA'">sqft</span>
                            <span v-else-if="selectedSurface?.unit_type === 'LINEAR'">ft</span>
                            <span v-else-if="selectedSurface?.unit_type === 'COUNT'">unit</span>
                        </span>
                    </div>

                    <div v-if="form.pricing_mode === 'LUMPSUM'" class="flex justify-between text-sm">
                        <span class="text-gray-600">Lumpsum:</span>
                        <span class="font-medium">₹{{ form.lumpsum_amount || 0 }}</span>
                    </div>

                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between text-lg">
                            <span class="font-bold text-green-800">TOTAL:</span>
                            <span class="font-bold text-green-700">₹{{ (isNaN(calculatedAmount) ? 0 : calculatedAmount).toFixed(2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 10) SAVE ACTION -->
            <button
                type="submit"
                :disabled="!form.system_id"
                class="w-full bg-blue-600 text-white py-4 rounded font-bold shadow text-lg h-14"
                :class="!form.system_id ? 'bg-gray-400' : 'bg-blue-600'"
            >
                SAVE ITEM - ₹{{ (isNaN(calculatedAmount) ? 0 : calculatedAmount).toFixed(2) }}
            </button>
        </form>

        <!-- Deduction Helper Modal -->
        <div v-if="showDeductionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-sm w-full">
                <div class="p-4 border-b">
                    <h3 class="font-bold text-lg">Add Deduction</h3>
                </div>
                <div class="p-4 space-y-3">
                    <button
                        v-for="type in deductionTypes"
                        :key="type.type"
                        @click="addDeduction(type.type); showDeductionModal = false"
                        class="w-full p-3 text-left border rounded hover:bg-gray-50"
                    >
                        <span class="font-medium">{{ type.label }}</span>
                        <span v-if="type.defaultArea > 0" class="text-sm text-gray-500 ml-2">
                            ({{ type.defaultArea }} sqft each)
                        </span>
                    </button>
                    <button
                        @click="showDeductionModal = false"
                        class="w-full p-3 text-left border rounded bg-gray-50"
                    >
                        <span class="font-medium text-gray-600">Custom Area Deduction</span>
                        <span class="text-sm text-gray-500 ml-2">(enter manually below)</span>
                    </button>
                </div>
                <div class="p-4 border-t">
                    <button
                        @click="showDeductionModal = false"
                        class="w-full py-2 text-gray-600 hover:bg-gray-100 rounded"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
