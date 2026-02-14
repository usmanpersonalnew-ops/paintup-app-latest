<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: SupervisorLayout,
});

defineProps({
    projects: {
        type: Array,
        required: true,
    },
});

// Check if project has cash payment pending confirmation
const hasCashPendingConfirmation = (project) => {
    return project.payment_method === 'CASH' &&
           project.booking_status === 'AWAITING_CONFIRMATION' &&
           !project.cash_confirmed_by;
};

// Mark cash as received
const markCashReceived = async (project, e) => {
    e.preventDefault();
    e.stopPropagation();
    
    if (!confirm(`Mark cash received for ${project.client_name}'s booking payment of ₹${formatCurrency(project.booking_amount || 0)}?`)) {
        return;
    }
    
    try {
        const response = await axios.post(`/supervisor/projects/${project.id}/confirm-cash-booking`);
        
        if (response.data.success) {
            // Refresh the page to show updated status
            router.visit(route('supervisor.projects.index'));
        } else {
            alert(response.data.message || 'Failed to confirm cash payment');
        }
    } catch (error) {
        alert(error.response?.data?.message || 'An error occurred');
    }
};

const statusColors = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'AWAITING_CASH_CONFIRMATION': 'bg-orange-100 text-orange-800',
    'AWAITING_CONFIRMATION': 'bg-orange-100 text-orange-800',
    'CONFIRMED': 'bg-green-100 text-green-800',
    'IN_PROGRESS': 'bg-blue-100 text-blue-800',
    'COMPLETED': 'bg-purple-100 text-purple-800',
};

const getStatusLabel = (status) => {
    const labels = {
        'DRAFT': 'Draft',
        'AWAITING_CASH_CONFIRMATION': '⏳ Awaiting Cash',
        'AWAITING_CONFIRMATION': '⏳ Cash Pending',
        'CONFIRMED': '✅ Confirmed',
        'IN_PROGRESS': '🔵 In Progress',
        'COMPLETED': '✅ Completed',
    };
    return labels[status] || status;
};

const formatCurrency = (value) => {
    if (!value) return '₹0';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getPaymentBadge = (project) => {
    // Show payment info based on milestones
    if (project.booking_status === 'PAID') {
        return {
            text: `💰 Booking: ${formatCurrency(project.booking_amount)}`,
            class: 'bg-green-50 text-green-700 border border-green-200',
        };
    }
    if (project.payment_method === 'CASH') {
        return {
            text: '💵 Cash (Pending)',
            class: 'bg-orange-50 text-orange-700 border border-orange-200',
        };
    }
    return {
        text: `💰 Booking: ${formatCurrency(project.booking_amount || 0)}`,
        class: 'bg-gray-50 text-gray-600 border border-gray-200',
    };
};
</script>

<template>
    <div class="p-4">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-xl font-bold text-gray-800">Projects</h1>
            <Link
                :href="route('supervisor.projects.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium touch-manipulation"
            >
                + New Project
            </Link>
        </div>

        <div v-if="projects.length === 0" class="text-center py-8 text-gray-500">
            <p>No projects yet. Create your first project!</p>
        </div>

        <div v-else class="space-y-3">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="route('supervisor.projects.show', project.id)"
                class="block bg-white rounded-lg shadow-sm border border-gray-200 p-4 touch-manipulation hover:bg-gray-50 cursor-pointer"
            >
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">{{ project.client_name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ project.location }}</p>
                    </div>
                    <span
                        class="px-2 py-1 text-xs font-medium rounded-full"
                        :class="statusColors[project.status]"
                    >
                        {{ getStatusLabel(project.status) }}
                    </span>
                </div>
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    {{ project.phone }}
                </div>
                
                <!-- Payment Info -->
                <div class="mt-2 flex items-center gap-2">
                    <span
                        class="px-2 py-1 text-xs rounded border"
                        :class="getPaymentBadge(project).class"
                    >
                        {{ getPaymentBadge(project).text }}
                    </span>
                    <span v-if="project.payment_method" class="text-xs text-gray-500">
                        {{ project.payment_method === 'ONLINE' ? '🟢 Online' : '💵 Cash' }}
                    </span>
                </div>
                
                <!-- Cash Confirmation Info -->
                <div v-if="project.cash_confirmed_by" class="mt-1 text-xs text-green-600">
                    ✓ Cash confirmed by supervisor
                </div>
                
                <!-- Mark Cash Received Button -->
                <div v-if="hasCashPendingConfirmation(project)" class="mt-2">
                    <button
                        @click="(e) => markCashReceived(project, e)"
                        class="w-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg text-sm touch-manipulation"
                    >
                        💵 Mark Cash Received
                    </button>
                </div>
            </Link>
        </div>
    </div>
</template>