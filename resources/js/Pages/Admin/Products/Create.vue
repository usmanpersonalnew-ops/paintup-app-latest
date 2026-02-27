<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';

defineProps({
    surfaces: {
        type: Array,
        default: () => [],
    },
    tiers: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    name: '',
    brand: '',
    tier_id: '',
    surface_ids: [],
    systems: [
        { system_name: '', process_remarks: '', base_rate: 0, warranty_months: 0 }
    ]
});

const addSystem = () => {
    form.systems.push({ system_name: '', process_remarks: '', base_rate: 0, warranty_months: 0 });
};

const removeSystem = (index) => {
    form.systems.splice(index, 1);
};

const submit = () => {
    form.post(route('admin.products.store'));
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="mb-6">
                <Link
                    :href="route('admin.products.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Products
                </Link>
            </div>

            <h1 class="text-2xl font-bold mb-6 text-gray-800">Add New Product</h1>
            
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Basic Details -->
                <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2 text-gray-700">1. Basic Details</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">
                                Product Name <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.name" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="e.g. Royale Glitz" 
                                required
                            >
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">
                                Brand <span class="text-red-500">*</span>
                            </label>
                            <input 
                                v-model="form.brand" 
                                type="text" 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                                placeholder="e.g. Asian Paints" 
                                required
                            >
                            <p v-if="form.errors.brand" class="mt-1 text-sm text-red-600">{{ form.errors.brand }}</p>
                        </div>
                    </div>

                    <!-- Tier Selection -->
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tier</label>
                        <select 
                            v-model="form.tier_id" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="">Select Tier (Optional)</option>
                            <option v-for="tier in tiers" :key="tier.id" :value="tier.id">
                                {{ tier.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.tier_id" class="mt-1 text-sm text-red-600">{{ form.errors.tier_id }}</p>
                    </div>
                </div>

                <!-- Applicable Surfaces -->
                <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2 text-gray-700">2. Applicable Surfaces</h2>
                    
                    <div v-if="surfaces.length === 0" class="text-center text-gray-500 py-4 italic">
                        No surfaces available. Please create surfaces first.
                    </div>

                    <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div v-for="surface in surfaces" :key="surface.id" class="flex items-center">
                            <input
                                type="checkbox"
                                :id="'surface-' + surface.id"
                                :value="surface.id"
                                v-model="form.surface_ids"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label :for="'surface-' + surface.id" class="ml-2 text-sm text-gray-700">
                                {{ surface.name }}
                            </label>
                        </div>
                    </div>
                    <p v-if="form.errors.surface_ids" class="mt-1 text-sm text-red-600">{{ form.errors.surface_ids }}</p>
                </div>

                <!-- Painting Systems -->
                <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <div class="flex justify-between items-center border-b pb-2">
                        <h2 class="text-lg font-semibold text-gray-700">3. Painting Systems (Pricing)</h2>
                        <button 
                            type="button" 
                            @click="addSystem" 
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm font-bold shadow"
                        >
                            + ADD SYSTEM
                        </button>
                    </div>

                    <div v-if="form.systems.length === 0" class="text-center text-gray-500 py-4 italic">
                        No pricing systems added yet. Click "+ ADD SYSTEM" to start.
                    </div>

                    <div v-for="(system, index) in form.systems" :key="index" class="bg-gray-50 p-4 rounded border border-gray-200 relative shadow-sm mb-4">
                        <button 
                            type="button" 
                            @click="removeSystem(index)" 
                            class="absolute top-2 right-2 text-red-500 hover:text-red-700 font-bold text-xl" 
                            title="Remove System"
                        >
                            ×
                        </button>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">System Name</label>
                                <input 
                                    v-model="system.system_name" 
                                    type="text" 
                                    class="w-full text-sm rounded border-gray-300" 
                                    placeholder="e.g. Fresh Painting" 
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Process / Remarks</label>
                                <input 
                                    v-model="system.process_remarks" 
                                    type="text" 
                                    class="w-full text-sm rounded border-gray-300" 
                                    placeholder="e.g. 3 Coats" 
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Base Rate (₹)</label>
                                <input 
                                    v-model="system.base_rate" 
                                    type="number" 
                                    step="0.01" 
                                    class="w-full text-sm rounded border-gray-300" 
                                    placeholder="0.00" 
                                    required
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Warranty (Months)</label>
                                <input 
                                    v-model="system.warranty_months" 
                                    type="number" 
                                    min="0" 
                                    class="w-full text-sm rounded border-gray-300" 
                                    placeholder="0 = No warranty"
                                >
                                <p class="text-xs text-gray-400 mt-1">96=8yr, 60=5yr, 36=3yr, 0=None</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-4">
                    <button 
                        type="submit" 
                        class="bg-green-600 text-white px-8 py-3 rounded-lg shadow-lg hover:bg-green-700 font-bold text-lg transition duration-150 disabled:opacity-50"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'SAVING...' : '✅ SAVE PRODUCT' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>