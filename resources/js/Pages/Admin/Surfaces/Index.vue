<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    surfaces: {
        type: Array,
        default: () => [],
    },
});

const unitTypeLabels = {
    AREA: 'Area (L × H)',
    LINEAR: 'Linear (Length)',
    COUNT: 'Count (Quantity)',
    LUMPSUM: 'Lumpsum',
};

const deleteSurface = (id) => {
    if (confirm('Are you sure you want to delete this surface?')) {
        router.delete(route('admin.surfaces.destroy', id));
    }
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl">
            <!-- Page Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Master Surfaces</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage surface types for quotations</p>
                </div>
                <Link
                    href="/admin/surfaces/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Surface
                </Link>
            </div>

            <!-- Surfaces Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Unit Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="surfaces.length === 0">
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                No surfaces found. Click "Add Surface" to create one.
                            </td>
                        </tr>
                        <tr v-for="surface in surfaces" :key="surface.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ surface.name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">{{ surface.category }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                    :class="{
                                        'bg-green-100 text-green-800': surface.unit_type === 'AREA',
                                        'bg-blue-100 text-blue-800': surface.unit_type === 'LINEAR',
                                        'bg-purple-100 text-purple-800': surface.unit_type === 'COUNT',
                                        'bg-orange-100 text-orange-800': surface.unit_type === 'LUMPSUM',
                                    }"
                                >
                                    {{ unitTypeLabels[surface.unit_type] || surface.unit_type }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="`/admin/surfaces/${surface.id}/edit`"
                                        class="text-blue-600 hover:text-blue-900"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        @click="deleteSurface(surface.id)"
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
    </AdminLayout>
</template>