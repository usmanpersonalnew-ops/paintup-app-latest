<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const page = usePage();
const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    project: {
        type: Object,
        required: true,
    },
    warranty: {
        type: Object,
        default: null,
    },
    eligible: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: '',
    },
});

const formatCurrency = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue) || numValue === 0) {
        return '₹0';
    }
    return '₹' + numValue.toLocaleString('en-IN');
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <CustomerLayout>
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800">Warranty Certificate</h1>
            <p class="text-gray-500 text-sm">{{ project.client_name }} - {{ project.location }}</p>
        </div>

        <!-- Not Eligible State -->
        <div v-if="!eligible" class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Warranty Not Available</h3>
            <p class="text-gray-500">{{ message || 'Your warranty will be available after all payments are completed.' }}</p>
        </div>

        <!-- Eligible but Not Generated -->
        <div v-else-if="eligible && !warranty" class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Warranty Not Generated Yet</h3>
            <p class="text-gray-500">Your warranty certificate is being prepared. Please check back later.</p>
        </div>

        <!-- Warranty Available -->
        <div v-else class="space-y-4">
            <!-- Success Banner -->
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <p class="font-medium">Your warranty certificate is ready!</p>
                <p class="text-sm">Generated on: {{ warranty?.generated_at || formatDate(new Date()) }}</p>
            </div>

            <!-- Warranty Details -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gray-100 px-4 py-3 border-b">
                    <h2 class="font-semibold text-gray-800">Warranty Coverage</h2>
                </div>
                <div class="p-4">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Area</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Surface</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Painting System</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warranty Period</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Until</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="(item, index) in warranty?.schedule || []" :key="index" class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-800">{{ item.area }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ item.surface }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ item.system }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ item.warranty_months }} months</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ item.valid_until }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Project Info -->
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-800 mb-3">Project Details</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Customer Name</p>
                        <p class="font-medium text-gray-800">{{ project.client_name }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Location</p>
                        <p class="font-medium text-gray-800">{{ project.location }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Phone</p>
                        <p class="font-medium text-gray-800">{{ project.phone }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Completed On</p>
                        <p class="font-medium text-gray-800">{{ warranty?.completed_at || '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>