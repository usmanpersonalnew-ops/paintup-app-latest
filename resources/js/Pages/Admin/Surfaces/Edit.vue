<script setup>
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ surface: Object });

const form = useForm({
    name: props.surface.name,
    category: props.surface.category,
    unit_type: props.surface.unit_type,
});

const submit = () => {
    form.put(route('admin.surfaces.update', props.surface.id));
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-2xl">
            <!-- Page Header -->
            <div class="mb-6">
                <Link
                    href="/admin/surfaces"
                    class="mb-4 inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Surfaces
                </Link>
                <h1 class="text-2xl font-bold text-gray-900">Edit Surface</h1>
            </div>

            <!-- Edit Form -->
            <div class="rounded-lg bg-white p-6 shadow">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Surface Name</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="e.g., Interior Wall"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                        <select
                            id="category"
                            v-model="form.category"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="INTERIOR">Interior</option>
                            <option value="EXTERIOR">Exterior</option>
                            <option value="BOTH">Both</option>
                        </select>
                        <p v-if="form.errors.category" class="mt-1 text-sm text-red-600">{{ form.errors.category }}</p>
                    </div>

                    <div>
                        <label for="unit_type" class="block text-sm font-medium text-gray-700">Unit Type</label>
                        <select
                            id="unit_type"
                            v-model="form.unit_type"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="AREA">Area (L × H)</option>
                            <option value="LINEAR">Linear (Length)</option>
                            <option value="COUNT">Count (Quantity)</option>
                            <option value="LUMPSUM">Lumpsum</option>
                        </select>
                        <p class="text-sm text-gray-500 mt-1">
                            Note: Changing this will change how the app calculates price for this item.
                        </p>
                        <p v-if="form.errors.unit_type" class="mt-1 text-sm text-red-600">{{ form.errors.unit_type }}</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <Link
                            href="/admin/surfaces"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            Update Surface
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
