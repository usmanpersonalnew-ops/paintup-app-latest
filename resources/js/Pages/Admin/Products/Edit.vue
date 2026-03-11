<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: Object,
    surfaces: Array
});

// Initialize form with existing data from the database
const form = useForm({
    name: props.product.name,
    brand: props.product.brand,
    tier: props.product.tier,
    // Convert surface objects to IDs for the checkboxes
    surface_ids: props.product.surfaces ? props.product.surfaces.map(s => s.id) : [],
    // Map systems or start empty
    systems: props.product.systems && props.product.systems.length > 0
        ? props.product.systems.map(s => ({
            system_name: s.system_name,
            process_remarks: s.process_remarks,
            base_rate: s.base_rate,
            warranty_months: s.warranty_months || 0
        }))
        : []
});

const addSystem = () => {
    form.systems.push({ system_name: '', process_remarks: '', base_rate: 0, warranty_months: 0 });
};

const removeSystem = (index) => {
    form.systems.splice(index, 1);
};

const submit = () => {
    form.put(route('admin.products.update', props.product.id));
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-4xl mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Edit Product: {{ form.name }}</h1>
            
            <form @submit.prevent="submit" class="space-y-6">
                <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2 text-gray-700">1. Basic Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Product Name</label>
                            <input v-model="form.name" type="text" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Brand</label>
                            <input v-model="form.brand" type="text" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Price Tier</label>
                        <select v-model="form.tier" class="w-full border-gray-300 rounded-md shadow-sm">
                            <option value="ECONOMY">Economy</option>
                            <option value="PREMIUM">Premium</option>
                            <option value="LUXURY">Luxury</option>
                            <option value="ULTRA_LUXURY">Ultra Luxury</option>
                        </select>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <h2 class="text-lg font-semibold border-b pb-2 text-gray-700">2. Applicable Surfaces</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <label v-for="surface in surfaces" :key="surface.id" class="flex items-center space-x-2 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" :value="surface.id" v-model="form.surface_ids" class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700">{{ surface.name }}</span>
                        </label>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md space-y-4">
                    <div class="flex justify-between items-center border-b pb-2">
                        <h2 class="text-lg font-semibold text-gray-700">3. Painting Systems (Pricing)</h2>
                        <button type="button" @click="addSystem" class="bg-blue-600 text-white px-4 py-2 rounded font-bold text-sm">+ ADD SYSTEM</button>
                    </div>

                    <div v-if="form.systems.length === 0" class="text-center text-gray-500 py-4 italic">
                        No pricing systems found. Click "+ ADD SYSTEM".
                    </div>

                    <div v-for="(system, index) in form.systems" :key="index" class="bg-gray-50 p-4 rounded border border-gray-200 relative shadow-sm mb-4">
                        <button type="button" @click="removeSystem(index)" class="absolute top-2 right-2 text-red-500 font-bold text-xl">×</button>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">System Name</label>
                                <input v-model="system.system_name" type="text" class="w-full text-sm rounded border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Remarks</label>
                                <input v-model="system.process_remarks" type="text" class="w-full text-sm rounded border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Rate (₹)</label>
                                <input v-model="system.base_rate" type="number" step="0.01" class="w-full text-sm rounded border-gray-300" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Warranty (Months)</label>
                                <input v-model="system.warranty_months" type="number" min="0" class="w-full text-sm rounded border-gray-300" placeholder="0 = No warranty">
                                <p class="text-xs text-gray-400 mt-1">96=8yr, 60=5yr, 36=3yr, 0=None</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-lg shadow-lg hover:bg-blue-700 font-bold text-lg">
                        UPDATE PRODUCT
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
