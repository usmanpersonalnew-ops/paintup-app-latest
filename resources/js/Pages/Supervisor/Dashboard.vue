<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({
    layout: SupervisorLayout,
});
</script>

<template>
    <div class="p-4">
        <h1 class="text-xl font-bold text-gray-800 mb-4">Dashboard</h1>

        <div class="grid grid-cols-1 gap-4">
            <Link
                :href="route('supervisor.projects.index')"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 touch-manipulation"
            >
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800">Projects</h3>
                        <p class="text-sm text-gray-500">View and manage projects</p>
                    </div>
                </div>
            </Link>

            <Link
                :href="route('supervisor.projects.create')"
                class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 touch-manipulation"
            >
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="font-semibold text-gray-800">New Project</h3>
                        <p class="text-sm text-gray-500">Add a new client project</p>
                    </div>
                </div>
            </Link>
        </div>
    </div>
</template>