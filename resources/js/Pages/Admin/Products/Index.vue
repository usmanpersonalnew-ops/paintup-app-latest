<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { watch, ref } from 'vue';

const props = defineProps({
    products: Array,
    brands: Array,
    filters: Object
});

const search = ref(props.filters.search || '');
const brand = ref(props.filters.brand || '');
const tier = ref(props.filters.tier || '');

// Auto-trigger filters
watch([search, brand, tier], () => {
    router.get(route('admin.products.index'), { 
        search: search.value, 
        brand: brand.value, 
        tier: tier.value 
    }, { preserveState: true, replace: true });
});

const reset = () => {
    search.value = '';
    brand.value = '';
    tier.value = '';
};
</script>

<template>
    <AdminLayout>
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Master Products</h1>
            <a :href="route('admin.products.create')" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium shadow transition">
                + Add Product
            </a>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                <input v-model="search" type="text" placeholder="Search products..." class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Brand</label>
                <select v-model="brand" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">All Brands</option>
                    <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                </select>
            </div>
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tier</label>
                <select v-model="tier" class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">All Tiers</option>
                    <option value="ECONOMY">Economy</option>
                    <option value="PREMIUM">Premium</option>
                    <option value="LUXURY">Luxury</option>
                    <option value="ULTRA_LUXURY">Ultra Luxury</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button @click="reset" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2 rounded-md transition text-sm">
                    Reset
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Name</th>
                        <th class="p-4">Brand</th>
                        <th class="p-4">Tier</th>
                        <th class="p-4">Systems</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition">
                        <td class="p-4 font-medium text-gray-800">{{ product.name }}</td>
                        <td class="p-4 text-gray-600">{{ product.brand }}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-bold"
                                :class="{
                                    'bg-green-100 text-green-700': product.tier === 'ECONOMY',
                                    'bg-blue-100 text-blue-700': product.tier === 'PREMIUM',
                                    'bg-purple-100 text-purple-700': product.tier === 'LUXURY',
                                    'bg-yellow-100 text-yellow-800': product.tier === 'ULTRA_LUXURY'
                                }">
                                {{ product.tier.replace('_', ' ') }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="product.systems_count > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'">
                                {{ product.systems_count }} Systems
                            </span>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <a :href="route('admin.products.edit', product.id)" class="text-blue-600 hover:text-blue-900 font-medium text-sm">Edit</a>
                            <span class="text-gray-300">|</span>
                            <button class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="products.length === 0">
                        <td colspan="5" class="p-8 text-center text-gray-400 italic">
                            No products found. Try changing filters or add a new one.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
