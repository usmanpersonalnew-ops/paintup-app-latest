<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: AdminLayout,
});

const form = useForm({
    name: '',
});

const previewName = ref('');

const submit = () => {
    form.post(route('admin.tiers.store'));
};
</script>

<template>
    <!-- <AdminLayout title="Create Tier"> -->
        <div class="mx-auto max-w-7xl">
            <!-- Page Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Tier</h1>
                    <p class="mt-1 text-sm text-gray-500">Add a new customer tier</p>
                </div>
                <Link
                    :href="route('admin.tiers.index')"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-700 hover:bg-gray-50"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Tiers
                </Link>
            </div>

            <!-- Create Form -->
            <div class="rounded-lg bg-white shadow">
                <form @submit.prevent="submit" class="p-6">
                    <!-- Name Field -->
                    <div class="mb-6">
                        <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                            Tier Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            @input="previewName = form.name"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:border-blue-500 focus:ring-blue-500"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Enter tier name (e.g., Gold, Silver, Platinum)"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <!-- Preview -->
                    <div class="mb-6 rounded-lg bg-gray-50 p-4">
                        <p class="mb-2 text-sm text-gray-600">Preview:</p>
                        <span v-if="form.name" class="inline-block rounded-full bg-blue-100 px-4 py-2 text-sm font-medium text-blue-800">
                            {{ form.name }}
                        </span>
                        <span v-else class="text-sm text-gray-400">Tier name will appear here</span>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3">
                        <Link
                            :href="route('admin.tiers.index')"
                            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Tier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <!-- </AdminLayout> -->
</template>