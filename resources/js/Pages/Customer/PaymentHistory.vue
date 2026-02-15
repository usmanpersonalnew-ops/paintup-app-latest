<script setup>
import { ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const page = usePage();
const props = defineProps({
    customer: {
        type: Object,
        default: null,
    },
    projects: {
        type: Array,
        required: true,
    },
    payments: {
        type: Array,
        default: () => [],
    },
    isAdminView: {
        type: Boolean,
        default: false,
    },
});

// Use appropriate layout based on view
const Layout = props.isAdminView ? AdminLayout : CustomerLayout;

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
    if (!method) return '-';
    const labels = {
        'ONLINE': 'Online',
        'CASH': 'Cash',
        'UPI': 'UPI',
        'CARD': 'Card',
        'NETBANKING': 'Net Banking',
        'WALLET': 'Wallet',
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
    <component :is="Layout">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Payment History</h1>
                    <p class="text-gray-500 text-sm">{{ isAdminView ? 'All payment transactions' : 'View all your payment transactions' }}</p>
                </div>
                <Link
                    v-if="isAdminView"
                    :href="route('admin.projects.index')"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                >
                    ← Back to Projects
                </Link>
            </div>
        </div>

        <!-- Desktop Table View (hidden on mobile) -->
        <div v-if="payments && payments.length > 0" class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Project
                            </th>
                            <th v-if="isAdminView" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Phone
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaction ID
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
                            <td v-if="isAdminView" class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                {{ payment.customer_phone || '-' }}
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
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-mono">
                                {{ payment.transaction_id }}
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
        </div>

        <!-- Mobile Card View (visible only on mobile) -->
        <div v-if="payments && payments.length > 0" class="md:hidden space-y-4">
            <div v-for="(payment, index) in payments" :key="index" class="bg-white rounded-lg shadow p-4">
                <div class="space-y-3">
                    <!-- Header Row -->
                    <div class="flex justify-between items-start border-b pb-2">
                        <div>
                            <h3 class="font-semibold text-gray-800">{{ payment.project_name }}</h3>
                            <p class="text-sm text-gray-500">{{ formatDate(payment.paid_at) }}</p>
                        </div>
                        <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(payment.payment_status)]">
                            {{ payment.payment_status }}
                        </span>
                    </div>

                    <!-- Milestone and Method -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Milestone</p>
                            <p class="text-sm font-medium text-gray-800">{{ getMilestoneLabel(payment.milestone_name) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Method</p>
                            <p class="text-sm font-medium text-gray-800">{{ getMethodLabel(payment.payment_method) }}</p>
                        </div>
                    </div>

                    <!-- Phone (Admin only) -->
                    <div v-if="isAdminView && payment.customer_phone">
                        <p class="text-xs text-gray-500">Phone</p>
                        <p class="text-sm font-medium text-gray-800">{{ payment.customer_phone }}</p>
                    </div>

                    <!-- Transaction ID -->
                    <div>
                        <p class="text-xs text-gray-500">Transaction ID</p>
                        <p class="text-sm font-mono text-gray-800 break-all">{{ payment.transaction_id }}</p>
                    </div>

                    <!-- Amounts -->
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t">
                        <div>
                            <p class="text-xs text-gray-500">Base</p>
                            <p class="text-sm font-medium text-gray-800">{{ formatCurrency(payment.base_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">GST</p>
                            <p class="text-sm font-medium text-gray-800">{{ formatCurrency(payment.gst_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Total</p>
                            <p class="text-sm font-bold text-gray-900">{{ formatCurrency(payment.total_amount) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No Payments Yet</h3>
            <p class="text-gray-500">{{ isAdminView ? 'No payments have been made yet.' : 'You haven\'t made any payments yet.' }}</p>
        </div>
    </component>
</template>
