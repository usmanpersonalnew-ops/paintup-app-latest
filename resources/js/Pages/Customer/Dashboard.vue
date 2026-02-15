<script setup>
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

const formatCurrency = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue) || numValue === 0) {
        return '₹0';
    }
    return '₹' + numValue.toLocaleString('en-IN');
};

const getStatusColor = (status) => {
    const colors = {
        'NEW': 'bg-gray-100 text-gray-800',
        'PENDING': 'bg-yellow-100 text-yellow-800',
        'ACCEPTED': 'bg-green-100 text-green-800',
        'IN_PROGRESS': 'bg-blue-100 text-blue-800',
        'COMPLETED': 'bg-purple-100 text-purple-800',
        'DRAFT': 'bg-gray-100 text-gray-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getStatusLabel = (status) => {
    const labels = {
        'NEW': 'New',
        'PENDING': 'Pending',
        'ACCEPTED': 'Booking Confirmed',
        'IN_PROGRESS': 'In Progress',
        'COMPLETED': 'Completed',
        'DRAFT': 'Draft',
    };
    return labels[status] || status;
};

const getPaymentUrl = (project) => {
    if (project.booking_status !== 'PAID') {
        return route('customer.payment.page', { project: project.id, milestone: 'booking' });
    }
    if (project.mid_status !== 'PAID') {
        return route('customer.payment.page', { project: project.id, milestone: 'mid' });
    }
    if (project.final_status !== 'PAID') {
        return route('customer.payment.page', { project: project.id, milestone: 'final' });
    }
    return '#';
};

const getQuoteUrl = (project) => {
    return route('customer.quote.show', project.id);
};

const getPhotosUrl = (project) => {
    return route('customer.project.photos', project.id);
};

const getWarrantyUrl = (project) => {
    return route('customer.customer.project.warranty', project.id);
};

const getInvoiceUrl = (project) => {
    return route('customer.customer.project.invoice', project.id);
};
</script>

<template>
    <CustomerLayout>
        <!-- Welcome Header -->
        <div class="bg-white rounded-xl shadow-sm p-4 sm:p-6 mb-4 sm:mb-6">
            <h1 class="text-lg sm:text-xl font-semibold text-gray-900">
                Welcome, {{ customer.name || customer.phone }}
            </h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-1">Manage your painting projects</p>
        </div>

        <!-- Success Message -->
        <div v-if="page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-700 px-3 sm:px-4 py-2 sm:py-3 rounded-xl mb-4 sm:mb-6 text-sm">
            {{ page.props.flash.success }}
        </div>

        <!-- Projects List -->
        <div v-if="projects.length > 0" class="space-y-4 sm:space-y-6">
            <div v-for="project in projects" :key="project.id" class="bg-white rounded-xl shadow-sm overflow-hidden">
                <!-- Project Header -->
                <div class="bg-gray-50 px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-100">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2 sm:gap-4">
                        <div class="flex-1 min-w-0">
                            <h2 class="font-semibold text-base sm:text-lg text-gray-900 truncate">{{ project.client_name }}</h2>
                            <p class="text-xs sm:text-sm text-gray-500 truncate">{{ project.location }}</p>
                        </div>
                        <span :class="['px-3 py-1 rounded-full text-xs sm:text-sm font-medium whitespace-nowrap', getStatusColor(project.work_status)]">
                            {{ getStatusLabel(project.work_status) }}
                        </span>
                    </div>
                </div>

                <!-- Project Details -->
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <!-- Project Status Card -->
                    <div class="bg-gray-50 rounded-xl p-3 sm:p-4">
                        <h3 class="text-xs sm:text-sm font-semibold text-gray-700 uppercase mb-2 sm:mb-3">Project Status</h3>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-0 mb-3">
                            <span :class="['px-3 py-1 rounded-full text-xs sm:text-sm font-medium inline-block w-fit', getStatusColor(project.work_status)]">
                                {{ getStatusLabel(project.work_status) }}
                            </span>
                            <span class="text-xs text-gray-500">
                                <span v-if="project.work_started_at">Started: {{ new Date(project.work_started_at).toLocaleDateString('en-IN') }}</span>
                                <span v-if="project.work_completed_at" class="hidden sm:inline"> | Completed: {{ new Date(project.work_completed_at).toLocaleDateString('en-IN') }}</span>
                                <span v-if="project.work_completed_at" class="sm:hidden block mt-1">Completed: {{ new Date(project.work_completed_at).toLocaleDateString('en-IN') }}</span>
                            </span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="relative">
                            <div class="flex flex-wrap items-center gap-1 sm:gap-2 mb-2">
                                <span v-for="(status, index) in ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'COMPLETED']" :key="status"
                                    :class="['text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full',
                                        project.work_status === status ? 'bg-[var(--primary-color)] text-white' :
                                        ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'COMPLETED'].indexOf(project.work_status) > index ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600'
                                    ]">
                                    {{ status.replace('_', ' ') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Key Metrics Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
                        <!-- Total Amount -->
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-gray-500 mb-1">Total Amount</p>
                            <p class="text-lg sm:text-xl font-bold text-[var(--primary-color)]">{{ formatCurrency(project.base_total || 0) }}</p>
                            <p class="text-xs text-gray-400">Excl. GST</p>
                        </div>

                        <!-- Next Milestone -->
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-gray-500 mb-1">Next Milestone</p>
                            <p class="text-base sm:text-lg font-semibold text-gray-800 truncate">{{ project.next_milestone?.name || 'None' }}</p>
                            <p class="text-xs sm:text-sm text-gray-600">{{ formatCurrency(project.next_milestone?.amount || 0) }}</p>
                        </div>

                        <!-- Outstanding -->
                        <div class="bg-gray-50 rounded-xl p-3 sm:p-4">
                            <p class="text-xs sm:text-sm text-gray-500 mb-1">Outstanding</p>
                            <p class="text-lg sm:text-xl font-bold text-orange-500">{{ formatCurrency(project.outstanding_amount || 0) }}</p>
                        </div>
                    </div>

                    <!-- Payment Action -->
                    <a
                        v-if="!project.all_paid"
                        :href="getPaymentUrl(project)"
                        class="block w-full bg-[var(--primary-color)] hover:opacity-90 text-white font-medium py-3 px-4 rounded-xl text-center transition-opacity text-sm sm:text-base"
                    >
                        Make Payment
                    </a>

                    <!-- All Paid Badge -->
                    <div v-else class="bg-green-100 text-green-800 font-medium py-3 px-4 rounded-xl text-center flex items-center justify-center gap-2 text-sm sm:text-base">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        All Payments Completed
                    </div>

                    <!-- Quick Links -->
                    <div class="pt-3 sm:pt-4 border-t border-gray-100">
                        <p class="text-xs font-bold text-gray-500 uppercase mb-2 sm:mb-3">Quick Links</p>
                        <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2">
                            <a
                                :href="getQuoteUrl(project)"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-3 rounded-lg text-center text-xs sm:text-sm transition-colors"
                            >
                                View Quote
                            </a>
                            <a
                                :href="getPhotosUrl(project)"
                                class="bg-orange-100 hover:bg-orange-200 text-orange-700 font-medium py-2 px-3 rounded-lg text-center text-xs sm:text-sm transition-colors"
                            >
                                Progress Photos
                            </a>
                            <a
                                v-if="project.all_paid && project.work_status === 'COMPLETED'"
                                :href="getWarrantyUrl(project)"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-medium py-2 px-3 rounded-lg text-center text-xs sm:text-sm transition-colors"
                            >
                                Warranty
                            </a>
                            <a
                                v-if="project.all_paid && project.work_status === 'COMPLETED'"
                                :href="getInvoiceUrl(project)"
                                class="bg-purple-100 hover:bg-purple-200 text-purple-700 font-medium py-2 px-3 rounded-lg text-center text-xs sm:text-sm transition-colors"
                            >
                                GST Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-xl shadow-sm p-6 sm:p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-10 w-10 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-base sm:text-lg font-medium text-gray-800 mb-2">No Projects Yet</h3>
            <p class="text-sm sm:text-base text-gray-500">You don't have any painting projects at the moment.</p>
        </div>
    </CustomerLayout>
</template>
