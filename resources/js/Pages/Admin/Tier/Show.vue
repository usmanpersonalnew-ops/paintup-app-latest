<!-- resources/js/Pages/Admin/Tier/Show.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    tier: {
        type: Object,
        required: true,
    },
});

// Color mapping for tiers
const tierColors = {
    'Gold': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'Silver': 'bg-gray-100 text-gray-800 border-gray-200',
    'Platinum': 'bg-purple-100 text-purple-800 border-purple-200',
    'Diamond': 'bg-blue-100 text-blue-800 border-blue-200',
    'Basic': 'bg-green-100 text-green-800 border-green-200',
    'Premium': 'bg-indigo-100 text-indigo-800 border-indigo-200',
};

const getTierColor = (tierName) => {
    for (const [key, color] of Object.entries(tierColors)) {
        if (tierName.toLowerCase().includes(key.toLowerCase())) {
            return color;
        }
    }
    return 'bg-gray-100 text-gray-800 border-gray-200';
};

const tierColor = getTierColor(props.tier.name);
</script>

<template>
    <div class="p-6">
        <!-- Header with Breadcrumb -->
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                <Link :href="route('admin.tiers.index')" class="hover:text-blue-600">Tiers</Link>
                <span>/</span>
                <span class="text-gray-900">{{ tier.name }}</span>
            </div>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-800">Tier Details</h1>
                <div class="flex gap-3">
                    <Link
                        :href="route('admin.tiers.edit', tier.id)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 inline-flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Tier
                    </Link>
                    <Link
                        :href="route('admin.tiers.index')"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50"
                    >
                        Back to List
                    </Link>
                </div>
            </div>
        </div>

        <!-- Tier Info Card -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Tier Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tier Name -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Tier Name</p>
                        <div class="flex items-center gap-3">
                            <span :class="['px-4 py-2 rounded-full text-base font-medium border', tierColor]">
                                {{ tier.name }}
                            </span>
                        </div>
                    </div>

                    <!-- Created Date -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 mb-1">Created On</p>
                        <p class="text-lg font-semibold text-gray-900">{{ new Date(tier.created_at).toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ new Date(tier.created_at).toLocaleTimeString() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Info Card (Optional - can be removed if not needed) -->
        <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-500">Tier ID</p>
                        <p class="text-base font-medium text-gray-900">#{{ tier.id }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Last Updated</p>
                        <p class="text-base font-medium text-gray-900">{{ new Date(tier.updated_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>