<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    tiers: {
        type: Array,
        default: () => [],
    },
});

const deleteTier = (id) => {
    if (confirm('Are you sure you want to delete this tier?')) {
        router.delete(route('admin.tiers.destroy', id));
    }
};

// Color options for tiers
const tierColors = {
    'Gold': 'bg-yellow-100 text-yellow-800',
    'Silver': 'bg-gray-100 text-gray-800',
    'Platinum': 'bg-purple-100 text-purple-800',
    'Diamond': 'bg-blue-100 text-blue-800',
    'Basic': 'bg-green-100 text-green-800',
    'Premium': 'bg-indigo-100 text-indigo-800',
};

const getTierColor = (tierName) => {
    for (const [key, color] of Object.entries(tierColors)) {
        if (tierName.toLowerCase().includes(key.toLowerCase())) {
            return color;
        }
    }
    return 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <!-- <AdminLayout title="Tiers"> -->
        <div class="mx-auto max-w-7xl">
            <!-- Page Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Tiers</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage customer tiers for loyalty and pricing</p>
                </div>
                <Link
                    :href="route('admin.tiers.create')"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Tier
                </Link>
            </div>

            <!-- Tiers Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                ID
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Tier Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Created
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="tiers.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No tiers found. Click "Add Tier" to create one.
                            </td>
                        </tr>
                        <tr v-for="tier in tiers" :key="tier.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">#{{ tier.id }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span :class="['inline-flex rounded-full px-3 py-1 text-sm font-medium', getTierColor(tier.name)]">
                                    {{ tier.name }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">{{ new Date(tier.created_at).toLocaleDateString() }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="route('admin.tiers.edit', tier.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteTier(tier.id)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <!-- </AdminLayout> -->
</template>