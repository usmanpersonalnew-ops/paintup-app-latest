<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { watch, ref } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        required: true
    },
    brands: {
        type: Array,
        required: true
    },
    tiers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const search = ref(props.filters.search || '');
const brand = ref(props.filters.brand || '');
const tier_id = ref(props.filters.tier_id || '');

// Auto-trigger filters
watch([search, brand, tier_id], () => {
    router.get(route('admin.products.index'), {
        search: search.value,
        brand: brand.value,
        tier_id: tier_id.value
    }, { preserveState: true, replace: true });
});

const reset = () => {
    search.value = '';
    brand.value = '';
    tier_id.value = '';
};

const deleteProduct = (product) => {
    if (!confirm(`Delete "${product.name}"? This cannot be undone.`)) return;
    router.delete(route('admin.products.destroy', product.id));
};

const getTierName = (product) => {
    return product.tier?.name || 'No Tier';
};

const getTierColor = (tierName) => {
    if (!tierName) return 'bg-gray-100 text-gray-800';
    
    const colors = {
        'Gold': 'bg-yellow-100 text-yellow-800',
        'Silver': 'bg-gray-100 text-gray-800',
        'Platinum': 'bg-purple-100 text-purple-800',
        'Diamond': 'bg-blue-100 text-blue-800',
        'Basic': 'bg-green-100 text-green-800',
        'Premium': 'bg-indigo-100 text-indigo-800',
    };
    
    for (const [key, color] of Object.entries(colors)) {
        if (tierName.toLowerCase().includes(key.toLowerCase())) {
            return color;
        }
    }
    return 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AdminLayout>
        <div class="max-w-7xl mx-auto px-4 py-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Master Products</h1>
                <Link 
                    :href="route('admin.products.create')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium shadow transition"
                >
                    + Add Product
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <div class="md:col-span-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                    <input 
                        v-model="search" 
                        type="text" 
                        placeholder="Search products..." 
                        class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Brand</label>
                    <select 
                        v-model="brand" 
                        class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">All Brands</option>
                        <option v-for="b in brands" :key="b" :value="b">{{ b }}</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tier</label>
                    <select 
                        v-model="tier_id" 
                        class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm"
                    >
                        <option value="">All Tiers</option>
                        <option v-for="tier in tiers" :key="tier.id" :value="tier.id">
                            {{ tier.name }}
                        </option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button 
                        @click="reset" 
                        class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2 rounded-md transition text-sm"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Products Table -->
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
                                <span 
                                    :class="['px-2 py-1 rounded text-xs font-bold', getTierColor(getTierName(product))]"
                                >
                                    {{ getTierName(product) }}
                                </span>
                            </td>
                            <td class="p-4">
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="product.systems_count > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800'"
                                >
                                    {{ product.systems_count || 0 }} Systems
                                </span>
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <Link 
                                    :href="route('admin.products.edit', product.id)" 
                                    class="text-blue-600 hover:text-blue-900 font-medium text-sm"
                                >
                                    Edit
                                </Link>
                                <span class="text-gray-300">|</span>
                                <button 
                                    type="button" 
                                    @click="deleteProduct(product)" 
                                    class="text-red-600 hover:text-red-900 font-medium text-sm"
                                >
                                    Delete
                                </button>
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
        </div>
    </AdminLayout>
</template>