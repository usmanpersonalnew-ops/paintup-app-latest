<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    surfaces: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Supervisor Dashboard
            </h2>
        </template>

        <div class="py-6">
            <!-- Create New Quote Button -->
            <div class="mb-6">
                <button class="h-12 w-full rounded-lg bg-blue-600 text-white font-semibold text-lg shadow-md hover:bg-blue-700 active:bg-blue-800 transition-colors">
                    + CREATE NEW QUOTE
                </button>
            </div>

            <!-- Available Surfaces List -->
            <div class="rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="border-b border-gray-200 px-4 py-3">
                    <h3 class="font-semibold text-gray-700">Available Surfaces</h3>
                </div>
                <ul class="divide-y divide-gray-100">
                    <li
                        v-for="surface in surfaces"
                        :key="surface.id"
                        class="px-4 py-3 text-gray-800"
                    >
                        {{ surface.name }}
                    </li>
                </ul>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
