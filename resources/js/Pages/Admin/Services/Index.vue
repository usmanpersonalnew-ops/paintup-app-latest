<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    services: Array
});

const showModal = ref(false);
const editingService = ref(null);
const form = ref({
    name: '',
    unit_type: 'AREA',
    default_rate: 0,
    is_repair: false,
    remarks: ''
});

const unitTypes = ['AREA', 'LINEAR', 'COUNT', 'LUMPSUM'];

const openModal = (service = null) => {
    if (service) {
        editingService.value = service.id;
        form.value = {
            name: service.name,
            unit_type: service.unit_type,
            default_rate: service.default_rate,
            is_repair: service.is_repair,
            remarks: service.remarks || ''
        };
    } else {
        editingService.value = null;
        form.value = {
            name: '',
            unit_type: 'AREA',
            default_rate: 0,
            is_repair: false,
            remarks: ''
        };
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingService.value = null;
};

const submitForm = () => {
    if (editingService.value) {
        router.put(route('admin.services.update', editingService.value), form.value, {
            onSuccess: closeModal
        });
    } else {
        router.post(route('admin.services.store'), form.value, {
            onSuccess: closeModal
        });
    }
};

const deleteService = (id) => {
    if (confirm('Are you sure you want to delete this service?')) {
        router.delete(route('admin.services.destroy', id));
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR'
    }).format(value);
};
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Master Services & Repairs</h1>
            <button 
                @click="openModal()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium shadow transition"
            >
                + Add Service
            </button>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Name</th>
                        <th class="p-4">Unit</th>
                        <th class="p-4">Rate</th>
                        <th class="p-4">Type</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="service in services" :key="service.id" class="hover:bg-gray-50 transition">
                        <td class="p-4 font-medium text-gray-800">{{ service.name }}</td>
                        <td class="p-4 text-gray-600">{{ service.unit_type }}</td>
                        <td class="p-4 text-gray-800 font-medium">{{ formatCurrency(service.default_rate) }}</td>
                        <td class="p-4">
                            <span 
                                class="px-2.5 py-1 rounded-full text-xs font-bold"
                                :class="service.is_repair ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'"
                            >
                                {{ service.is_repair ? 'Repair' : 'Service' }}
                            </span>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <button 
                                @click="openModal(service)"
                                class="text-blue-600 hover:text-blue-900 font-medium text-sm"
                            >
                                Edit
                            </button>
                            <span class="text-gray-300">|</span>
                            <button 
                                @click="deleteService(service.id)"
                                class="text-red-600 hover:text-red-900 font-medium text-sm"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-if="services.length === 0">
                        <td colspan="5" class="p-8 text-center text-gray-400 italic">
                            No services found. Add your first service using the button above.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="flex justify-between items-center p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ editingService ? 'Edit Service' : 'Add New Service' }}
                    </h3>
                    <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form @submit.prevent="submitForm" class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input 
                            v-model="form.name" 
                            type="text" 
                            required
                            placeholder="e.g., Floor Masking"
                            class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 h-12"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit Type</label>
                        <select 
                            v-model="form.unit_type" 
                            required
                            class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 h-12"
                        >
                            <option v-for="type in unitTypes" :key="type" :value="type">
                                {{ type }}
                            </option>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">
                            AREA: Length × Height | LINEAR: Length only | COUNT: Quantity | LUMPSUM: Fixed price
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Rate (INR)</label>
                        <input
                            v-model="form.default_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                            class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 h-12"
                        >
                    </div>

                    <div class="flex items-center">
                        <input
                            v-model="form.is_repair"
                            type="checkbox"
                            id="is_repair"
                            class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        >
                        <label for="is_repair" class="ml-2 text-sm text-gray-700">
                            Is this a Repair? <span class="text-gray-500">(Requires Photo Evidence)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks (Optional)</label>
                        <textarea
                            v-model="form.remarks"
                            rows="3"
                            placeholder="Add any notes about this service..."
                            class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
                        >
                            {{ editingService ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>