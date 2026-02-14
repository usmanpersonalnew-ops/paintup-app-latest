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
    payments: {
        type: Array,
        default: () => [],
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

const getStatusColor = (status) => {
    const colors = {
        'PENDING': 'bg-yellow-100 text-yellow-800',
        'PAID': 'bg-green-100 text-green-800',
        'AWAITING_CONFIRMATION': 'bg-orange-100 text-orange-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getMethodLabel = (method) => {
    const labels = {
        'ONLINE': 'Online',
        'CASH': 'Cash',
    };
    return labels[method] || method;
};

// Get milestone display name
const getMilestoneLabel = (milestone) => {
    const labels = {
        'booking': 'Booking (40%)',
        'mid': 'Mid Payment (40%)',
        'final': 'Final Payment (20%)',
    };
    return labels[milestone] || milestone;
};
</script>

<template>
    <CustomerLayout>
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800">Payment History</h1>
            <p class="text-gray-500 text-sm">View all your payment transactions</p>
        </div>

        <!-- Payments Table -->
        <div v-if="payments && payments.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Project
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Milestone
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Base Amount
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            GST
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Total Paid
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Method
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(payment, index) in payments" :key="index" class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ formatDate(payment.paid_at) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-800">
                            {{ payment.project_name }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ getMilestoneLabel(payment.milestone_name) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                            {{ formatCurrency(payment.base_amount) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                            {{ formatCurrency(payment.gst_amount) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-800">
                            {{ formatCurrency(payment.total_amount) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ getMethodLabel(payment.payment_method) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(payment.payment_status)]">
                                {{ payment.payment_status }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No Payments Yet</h3>
            <p class="text-gray-500">You haven't made any payments yet.</p>
        </div>
    </CustomerLayout>
</template>