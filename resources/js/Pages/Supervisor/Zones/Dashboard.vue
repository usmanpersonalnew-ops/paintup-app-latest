<script setup>
import { ref } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    zone: {
        type: Object,
        required: true,
    },
});

const page = usePage();

// Edit Zone Modal State
const showEditModal = ref(false);
const editForm = useForm({
    name: '',
    length: null,
    breadth: null,
    height: null,
});

const openEditModal = () => {
    editForm.name = props.zone.name;
    editForm.length = props.zone.length || null;
    editForm.breadth = props.zone.breadth || null;
    editForm.height = props.zone.height || null;
    showEditModal.value = true;
};

const saveZone = () => {
    editForm.put(route('supervisor.zones.update', props.zone.id), {
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
};

// Helper to format currency in INR
const formatCurrency = (value) => {
    if (value === null || value === undefined) return '₹0';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// Helper to get item display name
const getPaintItemName = (item) => {
    if (item.product) {
        let name = item.product.name;
        if (item.system && item.system.process_remarks) {
            name += ` (${item.system.process_remarks})`;
        }
        return name;
    }
    return item.description || 'Paint Item';
};

// Helper to get service display name
const getServiceName = (service) => {
    if (service.masterService && service.masterService.name) {
        return service.masterService.name;
    }
    return service.custom_name || 'Additional Service';
};

// Helper to get zone type badge color
const getTypeBadgeColor = (type) => {
    return type === 'Exterior'
        ? 'bg-orange-100 text-orange-700'
        : 'bg-blue-100 text-blue-700';
};

// Handle duplicate zone
const handleDuplicate = () => {
    if (confirm('Are you sure you want to duplicate this zone? All paint items and services will be copied.')) {
        // Use Inertia to post to duplicate endpoint
        const form = useForm({});
        form.post(route('supervisor.zones.duplicate', props.zone.id), {
            onSuccess: () => {
                // Will redirect to the new zone
            },
        });
    }
};

// Handle done - navigate back to project page
const handleDone = () => {
    // Navigate to project page
    window.location.href = route('supervisor.projects.show', props.zone.project_id);
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-24">
        <!-- 1) HEADER -->
        <header class="bg-white shadow-sm sticky top-0 z-10">
            <div class="px-4 py-4">
                <!-- Back Button & Zone Name -->
                <div class="flex items-center gap-3 mb-2">
                    <Link
                        :href="route('supervisor.summary', zone.project_id)"
                        class="text-blue-600 hover:text-blue-700"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ zone.name }}
                    </h1>
                    <button
                        @click="openEditModal"
                        class="ml-2 p-2 text-blue-600 hover:bg-blue-50 rounded-lg"
                        title="Edit Zone"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.2325.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Zone Type Badge -->
                    <span 
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                        :class="getTypeBadgeColor(zone.type)"
                    >
                        {{ zone.type }}
                    </span>
                    
                    <!-- Dimensions -->
                    <div class="flex items-center text-sm text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <span>
                            {{ zone.length || 0 }} x {{ zone.breadth || 0 }} x {{ zone.height || 0 }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="px-4 py-4 space-y-6">
            
            <!-- NOTE: All photos should be uploaded at Project level -->
            <!-- Supervisor → Project → Photos -->
            <section class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="font-medium text-blue-900">Upload Photos at Project Level</p>
                        <p class="text-sm text-blue-700">Go to Project → Photos to upload Before / In Progress / After photos</p>
                    </div>
                </div>
                <Link
                    :href="route('supervisor.photos.index', zone.project_id)"
                    class="mt-3 w-full h-10 bg-blue-600 text-white rounded-lg font-medium flex items-center justify-center hover:bg-blue-700 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Go to Project Photos
                </Link>
            </section>

            <!-- 2) SECTION: ITEMS ADDED -->
            <section>
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Items Added</h2>
                
                <!-- A) Paint Items -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 mb-4">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Painting Work</h3>
                    </div>
                    
                    <!-- Paint Items List -->
                    <div v-if="zone.items && zone.items.length > 0" class="divide-y divide-gray-100">
                        <div
                            v-for="item in zone.items"
                            :key="item.id"
                            class="px-4 py-3 flex justify-between items-center"
                        >
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">{{ getPaintItemName(item) }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ item.surface ? item.surface.name : 'Surface' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-medium text-gray-900">
                                    {{ formatCurrency(item.amount) }}
                                </span>
                                <Link
                                    :href="route('supervisor.zones.paint.edit', [zone.id, item.id])"
                                    class="text-blue-600 hover:text-blue-700"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.2325.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="px-4 py-6 text-center">
                        <p class="text-gray-500 text-sm mb-4">No paint items added yet</p>
                    </div>
                    
                    <!-- Add Paint Item Button -->
                    <div class="px-4 pb-4">
                        <Link
                            :href="route('supervisor.zones.paint.create', zone.id)"
                            class="w-full h-12 bg-blue-600 text-white rounded-lg font-medium flex items-center justify-center hover:bg-blue-700 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Paint Item
                        </Link>
                    </div>
                </div>

                <!-- B) Services & Repairs -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="font-medium text-gray-900">Additional Services / Add-ons</h3>
                    </div>
                    
                    <!-- Services List -->
                    <div v-if="zone.services && zone.services.length > 0" class="divide-y divide-gray-100">
                        <div
                            v-for="service in zone.services"
                            :key="service.id"
                            class="px-4 py-3"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">{{ getServiceName(service) }}</p>
                                    <p class="text-sm text-gray-500">
                                        {{ service.unit_type }} • Qty: {{ service.quantity }}
                                    </p>
                                    <p v-if="service.masterService?.remarks" class="text-xs text-gray-400 mt-1">
                                        Note: {{ service.masterService.remarks }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-gray-900">
                                        {{ formatCurrency(service.amount) }}
                                    </span>
                                    <Link
                                        :href="route('supervisor.zones.service.edit', [zone.id, service.id])"
                                        class="text-blue-600 hover:text-blue-700"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.2325.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-else class="px-4 py-6 text-center">
                        <p class="text-gray-500 text-sm mb-4">No services added yet</p>
                    </div>
                    
                    <!-- Add Service Button -->
                    <div class="px-4 pb-4">
                        <Link
                            :href="route('supervisor.zones.service.create', zone.id)"
                            class="w-full h-12 bg-white border-2 border-blue-600 text-blue-600 rounded-lg font-medium flex items-center justify-center hover:bg-blue-50 transition-colors"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Service / Repair
                        </Link>
                    </div>
                </div>
            </section>
        </div>

        <!-- 3) FOOTER ACTIONS -->
        <footer class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 pb-24">
            <div class="flex gap-3">
                <!-- Duplicate Zone Button -->
                <button 
                    class="flex-1 h-12 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium flex items-center justify-center hover:bg-gray-50 transition-colors"
                    @click="handleDuplicate"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    Duplicate
                </button>
                
                <!-- Done Button -->
                <button 
                    class="flex-1 h-12 bg-blue-600 text-white rounded-lg font-medium flex items-center justify-center hover:bg-blue-700 transition-colors"
                    @click="handleDone"
                >
                    Done
                </button>
            </div>
        </footer>

        <!-- Edit Zone Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
            <div class="bg-white rounded-lg w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Edit Zone</h2>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Zone Name" class="text-sm font-medium text-gray-700" />
                        <TextInput id="edit_name" v-model="editForm.name" type="text" class="mt-1 h-12 border-gray-300 w-full" />
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="edit_length" value="Length (ft)" class="text-sm font-medium text-gray-700" />
                            <TextInput id="edit_length" v-model="editForm.length" type="number" step="0.01" class="mt-1 h-12 border-gray-300 w-full" />
                        </div>
                        <div>
                            <InputLabel for="edit_breadth" value="Breadth (ft)" class="text-sm font-medium text-gray-700" />
                            <TextInput id="edit_breadth" v-model="editForm.breadth" type="number" step="0.01" class="mt-1 h-12 border-gray-300 w-full" />
                        </div>
                        <div>
                            <InputLabel for="edit_height" value="Height (ft)" class="text-sm font-medium text-gray-700" />
                            <TextInput id="edit_height" v-model="editForm.height" type="number" step="0.01" class="mt-1 h-12 border-gray-300 w-full" />
                        </div>
                    </div>
                </div>
                
                <div class="flex gap-3 mt-6">
                    <button
                        @click="showEditModal = false"
                        class="flex-1 h-12 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveZone"
                        :disabled="editForm.processing"
                        class="flex-1 h-12 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ editForm.processing ? 'Saving...' : 'Save' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
