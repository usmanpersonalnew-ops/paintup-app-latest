<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const page = usePage();
const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    projects: {
        type: Array,
        required: true,
    },
});

const getStatusColor = (status) => {
    const colors = {
        'PENDING': 'bg-gray-100 text-gray-600',
        'ASSIGNED': 'bg-blue-100 text-blue-600',
        'IN_PROGRESS': 'bg-green-100 text-green-600',
        'ON_HOLD': 'bg-yellow-100 text-yellow-600',
        'COMPLETED': 'bg-purple-100 text-purple-600',
        'CLOSED': 'bg-gray-300 text-gray-600',
    };
    return colors[status] || 'bg-gray-100 text-gray-600';
};

const getStatusLabel = (status) => {
    const labels = {
        'PENDING': 'Pending',
        'ASSIGNED': 'Assigned',
        'IN_PROGRESS': 'In Progress',
        'ON_HOLD': 'On Hold',
        'COMPLETED': 'Completed',
        'CLOSED': 'Closed',
    };
    return labels[status] || status;
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const workStatuses = [
    { value: 'PENDING', label: 'Pending' },
    { value: 'ASSIGNED', label: 'Assigned' },
    { value: 'IN_PROGRESS', label: 'In Progress' },
    { value: 'ON_HOLD', label: 'On Hold' },
    { value: 'COMPLETED', label: 'Completed' },
    { value: 'CLOSED', label: 'Closed' },
];
</script>

<template>
    <CustomerLayout>
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800">Work Progress</h1>
            <p class="text-gray-500 text-sm">Track the status of your painting projects</p>
        </div>

        <!-- Projects List -->
        <div v-if="projects.length > 0" class="space-y-4">
            <div v-for="project in projects" :key="project.id" class="bg-white rounded-lg shadow overflow-hidden">
                <!-- Project Header -->
                <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
                    <div>
                        <h2 class="font-semibold text-gray-800">{{ project.client_name }}</h2>
                        <p class="text-sm text-gray-500">{{ project.location }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusColor(project.work_status)]">
                        {{ getStatusLabel(project.work_status) }}
                    </span>
                </div>

                <!-- Work Progress Details -->
                <div class="p-4">
                    <!-- Status Info -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Current Status</p>
                            <p class="font-medium text-gray-800">{{ getStatusLabel(project.work_status) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Started On</p>
                            <p class="font-medium text-gray-800">{{ formatDate(project.work_started_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Completed On</p>
                            <p class="font-medium text-gray-800">{{ formatDate(project.work_completed_at) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Project Value</p>
                            <p class="font-medium text-blue-600">₹{{ (project.base_total || 0).toLocaleString() }}</p>
                        </div>
                    </div>

                    <!-- Progress Timeline -->
                    <div class="mt-6">
                        <p class="text-sm font-bold text-gray-500 uppercase mb-4">Progress Timeline</p>
                        <div class="relative">
                            <!-- Timeline Line -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                            
                            <!-- Timeline Items -->
                            <div v-for="(status, index) in workStatuses" :key="status.value" class="relative pl-10 pb-6">
                                <!-- Status Dot -->
                                <div 
                                    :class="[
                                        'absolute left-2.5 w-3 h-3 rounded-full border-2',
                                        project.work_status === status.value 
                                            ? 'bg-blue-600 border-blue-600' 
                                            : workStatuses.map(s => s.value).indexOf(project.work_status) > index
                                                ? 'bg-green-500 border-green-500'
                                                : 'bg-white border-gray-300'
                                    ]"
                                ></div>
                                
                                <!-- Content -->
                                <div :class="[
                                    'p-3 rounded-lg border',
                                    project.work_status === status.value ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200'
                                ]">
                                    <p :class="[
                                        'font-medium',
                                        project.work_status === status.value ? 'text-blue-800' : 'text-gray-600'
                                    ]">
                                        {{ status.label }}
                                    </p>
                                    <p v-if="project.work_status === status.value" class="text-sm text-blue-600 mt-1">
                                        Current Stage
                                    </p>
                                    <p v-else-if="workStatuses.map(s => s.value).indexOf(project.work_status) > index" class="text-sm text-green-600 mt-1">
                                        Completed
                                    </p>
                                    <p v-else class="text-sm text-gray-400 mt-1">
                                        Pending
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No Projects Yet</h3>
            <p class="text-gray-500">You don't have any painting projects at the moment.</p>
        </div>
    </CustomerLayout>
</template>