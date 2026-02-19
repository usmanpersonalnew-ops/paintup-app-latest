<script setup>
import { useForm, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    supervisors: {
        type: Array,
        default: () => [],
    },
});

// Parse home_visit_supervisors if it's a JSON string
const getHomeVisitSupervisors = () => {
    if (!props.project.home_visit_supervisors) return [];
    if (typeof props.project.home_visit_supervisors === 'string') {
        try {
            return JSON.parse(props.project.home_visit_supervisors);
        } catch (e) {
            return [];
        }
    }
    return props.project.home_visit_supervisors;
};

const form = useForm({
    client_name: props.project.client_name || '',
    phone: props.project.phone || '',
    location: props.project.location || '',
    status: props.project.status || 'NEW',
    supervisor_id: props.project.supervisor_id || null,
    home_visit_date: props.project.home_visit_date || '',
    home_visit_time: props.project.home_visit_time || '',
    home_visit_supervisors: getHomeVisitSupervisors(),
});

const selectedHomeVisitSupervisors = ref(getHomeVisitSupervisors());

const processing = ref({});

// Get stored milestone amounts (calculated from Grand Total on backend)
const getBookingAmount = (project) => project.booking_amount || Math.round((project.grand_total || project.total_amount || 0) * 0.40);
const getMidAmount = (project) => project.mid_amount || Math.round((project.grand_total || project.total_amount || 0) * 0.40);
const getFinalAmount = (project) => project.final_amount || Math.round((project.grand_total || project.total_amount || 0) * 0.20);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleString('en-IN');
};

const submit = () => {
    form.home_visit_supervisors = selectedHomeVisitSupervisors.value;
    form.put(route('admin.projects.update', props.project.id));
};

// Manual payment confirmation functions
const markBookingPaid = async () => {
    if (!confirm('Manually mark booking as PAID?')) return;

    processing.value['booking'] = true;
    try {
        await axios.post(`/admin/projects/${props.project.id}/mark-booking-paid`, {});
        window.location.reload();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed');
    } finally {
        processing.value['booking'] = false;
    }
};

const markMidPaid = async () => {
    if (!confirm('Manually mark mid payment as PAID?')) return;

    processing.value['mid'] = true;
    try {
        await axios.post(`/admin/projects/${props.project.id}/mark-mid-paid`, {});
        window.location.reload();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed');
    } finally {
        processing.value['mid'] = false;
    }
};

const markFinalPaid = async () => {
    if (!confirm('Manually mark final payment as PAID?')) return;

    processing.value['final'] = true;
    try {
        await axios.post(`/admin/projects/${props.project.id}/mark-final-paid`, {});
        window.location.reload();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed');
    } finally {
        processing.value['final'] = false;
    }
};

const confirmCashPayment = async (milestone) => {
    if (!confirm(`Confirm ${milestone} cash payment received?`)) return;

    processing.value[milestone] = true;
    try {
        await axios.post(`/admin/projects/${props.project.id}/confirm-cash`, { milestone });
        // Use Inertia router to reload with fresh data
        router.reload({ only: ['project'] });
    } catch (e) {
        alert(e.response?.data?.message || 'Failed');
    } finally {
        processing.value[milestone] = false;
    }
};

// Status helpers
const getStatusColor = (status) => {
    const colors = {
        'NEW': 'bg-gray-100 text-gray-800',
        'PENDING': 'bg-yellow-100 text-yellow-800',
        'ACCEPTED': 'bg-green-100 text-green-800',
        'IN_PROGRESS': 'bg-blue-100 text-blue-800',
        'REJECTED': 'bg-red-100 text-red-800',
        'COMPLETED': 'bg-purple-100 text-purple-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusColor = (status) => {
    const colors = {
        'PENDING': 'bg-yellow-100 text-yellow-800',
        'PAID': 'bg-green-100 text-green-800',
        'CASH_PENDING': 'bg-orange-100 text-orange-800',
        'LOCKED': 'bg-gray-100 text-gray-500',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

// ==================== Work Status Helpers ====================

const workStatuses = [
    { value: 'PENDING', label: 'Pending' },
    { value: 'ASSIGNED', label: 'Assigned' },
    { value: 'IN_PROGRESS', label: 'In Progress' },
    { value: 'ON_HOLD', label: 'On Hold' },
    { value: 'COMPLETED', label: 'Completed' },
    { value: 'CLOSED', label: 'Closed' },
];

const getWorkStatusColor = (status) => {
    const colors = {
        'PENDING': 'bg-gray-100 text-gray-800',
        'ASSIGNED': 'bg-blue-100 text-blue-800',
        'IN_PROGRESS': 'bg-green-100 text-green-800',
        'ON_HOLD': 'bg-yellow-100 text-yellow-800',
        'COMPLETED': 'bg-purple-100 text-purple-800',
        'CLOSED': 'bg-gray-500 text-white',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const updateWorkStatus = async (newStatus) => {
    if (!confirm(`Change work status to "${newStatus}"?`)) return;

    processing.value['work_status'] = true;
    try {
        await axios.post(`/admin/projects/${props.project.id}/work-status`, {
            work_status: newStatus
        });
        window.location.reload();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to update work status');
    } finally {
        processing.value['work_status'] = false;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="px-4 sm:px-6 lg:px-8 py-6">
            <div class="mb-6">
                <Link
                    :href="route('admin.projects.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center gap-1"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Projects
                </Link>
            </div>

            <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h1 class="text-2xl font-bold text-gray-800">Project Details</h1>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.projects.quote', project.id)"
                        class="bg-green-600 text-white px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-green-700 flex items-center gap-2 transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Quote
                    </Link>
                    <span :class="['px-4 py-2 rounded-full text-sm font-medium', getStatusColor(project.status)]">
                        {{ project.status }}
                    </span>
                </div>
            </div>

        <!-- Payment Information Section -->
        <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm border border-gray-200">
            <div class="px-6 py-4 bg-blue-600 border-b border-blue-700">
                <h2 class="text-lg font-semibold text-white">💰 Payment Information (40-40-20)</h2>
            </div>
            <div class="p-6 lg:p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-6">
                    <!-- Total Amount -->
                    <div class="pb-4 border-b sm:border-b-0 sm:border-r border-gray-200 sm:pr-6">
                        <p class="text-sm text-gray-500 mb-1.5">Total Quote Amount</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(project.total_amount || 0) }}</p>
                    </div>

                    <!-- Payment Method -->
                    <div class="pb-4 border-b sm:border-b-0 sm:border-r border-gray-200 sm:pr-6">
                        <p class="text-sm text-gray-500 mb-1.5">Payment Method</p>
                        <p class="text-lg font-semibold" :class="project.payment_method === 'ONLINE' ? 'text-green-600' : 'text-orange-600'">
                            {{ project.payment_method || 'Not Selected' }}
                        </p>
                    </div>

                    <!-- Booking Status -->
                    <div class="pb-4 border-b sm:border-b-0 sm:border-r border-gray-200 sm:pr-6 lg:border-r-0">
                        <p class="text-sm text-gray-500 mb-1.5">Booking (40%)</p>
                        <p class="text-lg font-bold text-blue-600 mb-1.5">{{ formatCurrency(getBookingAmount(project)) }}</p>
                        <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold capitalize', getPaymentStatusColor(project.booking_status)]">
                            {{ project.booking_status || 'PENDING' }}
                        </span>
                    </div>

                    <!-- Mid Status -->
                    <div class="pb-4 sm:pb-0">
                        <p class="text-sm text-gray-500 mb-1.5">Mid Payment (40%)</p>
                        <p class="text-lg font-bold text-blue-600 mb-1.5">{{ formatCurrency(getMidAmount(project)) }}</p>
                        <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold capitalize', getPaymentStatusColor(project.mid_status)]">
                            {{ project.mid_status || 'LOCKED' }}
                        </span>
                    </div>

                    <!-- Final Status -->
                    <div class="col-span-1 sm:col-span-2 lg:col-span-1 pt-4 sm:pt-0 sm:border-t sm:border-r border-gray-200 sm:pr-6 lg:border-t-0 lg:border-r-0 lg:pt-0">
                        <p class="text-sm text-gray-500 mb-1.5">Final Payment (20%)</p>
                        <p class="text-lg font-bold text-blue-600 mb-1.5">{{ formatCurrency(getFinalAmount(project)) }}</p>
                        <span :class="['inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold capitalize', getPaymentStatusColor(project.final_status)]">
                            {{ project.final_status || 'LOCKED' }}
                        </span>
                    </div>
                </div>

                <!-- Payment Timestamps -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-sm">
                        <div v-if="project.booking_paid_at" class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 mb-1.5">Booking Paid At</p>
                            <p class="font-medium text-gray-900">{{ formatDate(project.booking_paid_at) }}</p>
                        </div>
                        <div v-if="project.mid_paid_at" class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 mb-1.5">Mid Paid At</p>
                            <p class="font-medium text-gray-900">{{ formatDate(project.mid_paid_at) }}</p>
                        </div>
                        <div v-if="project.final_paid_at" class="bg-gray-50 p-3 rounded-lg">
                            <p class="text-gray-500 mb-1.5">Final Paid At</p>
                            <p class="font-medium text-gray-900">{{ formatDate(project.final_paid_at) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cash Confirmation Info -->
                <div v-if="project.cash_confirmed_at" class="mt-6 pt-6 border-t border-gray-200">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-700 font-medium flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Cash confirmed by supervisor on {{ formatDate(project.cash_confirmed_at) }}
                        </p>
                    </div>
                </div>

                <!-- Admin Manual Payment Actions -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-4">Admin Manual Actions:</p>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-if="project.booking_status !== 'PAID'"
                            @click="markBookingPaid"
                            :disabled="processing['booking']"
                            class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 disabled:opacity-50 transition-colors"
                        >
                            {{ processing['booking'] ? '...' : 'Mark Booking PAID' }}
                        </button>
                        <button
                            v-if="project.booking_status === 'PAID' && project.mid_status !== 'PAID'"
                            @click="markMidPaid"
                            :disabled="processing['mid']"
                            class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 disabled:opacity-50 transition-colors"
                        >
                            {{ processing['mid'] ? '...' : 'Mark Mid PAID' }}
                        </button>
                        <button
                            v-if="project.mid_status === 'PAID' && project.final_status !== 'PAID'"
                            @click="markFinalPaid"
                            :disabled="processing['final']"
                            class="px-4 py-2 bg-blue-50 text-blue-700 rounded-lg text-sm font-medium hover:bg-blue-100 disabled:opacity-50 transition-colors"
                        >
                            {{ processing['final'] ? '...' : 'Mark Final PAID' }}
                        </button>
                        <button
                            v-if="project.payment_method === 'CASH' && project.booking_status === 'AWAITING_CONFIRMATION'"
                            @click="confirmCashPayment('booking')"
                            :disabled="processing['booking']"
                            class="px-4 py-2 bg-orange-50 text-orange-700 rounded-lg text-sm font-medium hover:bg-orange-100 disabled:opacity-50 transition-colors"
                        >
                            {{ processing['booking'] ? '...' : 'Confirm Cash Booking' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Status Section -->
        <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm border border-gray-200">
            <div class="px-6 py-4 bg-purple-600 border-b border-purple-700">
                <h2 class="text-lg font-semibold text-white">🔧 Work Status (Admin Control)</h2>
            </div>
            <div class="p-6 lg:p-8">
                <!-- Current Work Status -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-5 mb-6">
                    <div class="flex-1">
                        <p class="text-sm text-gray-500 mb-2.5">Current Status</p>
                        <span :class="['inline-flex items-center px-5 py-2.5 rounded-full text-base font-bold', getWorkStatusColor(project.work_status)]">
                            {{ project.work_status || 'PENDING' }}
                        </span>
                    </div>
                    <div v-if="project.work_started_at" class="sm:pl-6 sm:border-l border-gray-200 flex-1">
                        <p class="text-sm text-gray-500 mb-1.5">Work Started</p>
                        <p class="font-medium text-gray-900">{{ formatDate(project.work_started_at) }}</p>
                    </div>
                    <div v-if="project.work_completed_at" class="sm:pl-6 sm:border-l border-gray-200 flex-1">
                        <p class="text-sm text-gray-500 mb-1.5">Work Completed</p>
                        <p class="font-medium text-gray-900">{{ formatDate(project.work_completed_at) }}</p>
                    </div>
                </div>

                <!-- Work Status Timeline -->
                <div class="mb-6">
                    <p class="text-sm font-semibold text-gray-700 mb-4">Progress Timeline</p>
                    <div class="flex items-center gap-2">
                        <template v-for="(status, index) in ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'COMPLETED', 'CLOSED']" :key="status">
                            <div class="flex-1 text-center">
                                <div :class="['h-3 w-full rounded-full transition-all',
                                    project.work_status === status ? 'bg-purple-600' :
                                    ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'COMPLETED', 'CLOSED'].indexOf(project.work_status) > index ? 'bg-green-500' : 'bg-gray-200'
                                ]"></div>
                                <p :class="['text-xs mt-2.5 font-medium', project.work_status === status ? 'text-purple-600' : 'text-gray-500']">
                                    {{ status.replace('_', ' ') }}
                                </p>
                            </div>
                            <div v-if="index < 5" class="w-4"></div>
                        </template>
                    </div>
                </div>

                <!-- Admin Work Status Controls -->
                <div class="pt-6 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-4">Change Work Status (Admin Override):</p>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="status in workStatuses"
                            :key="status.value"
                            @click="updateWorkStatus(status.value)"
                            :disabled="processing['work_status'] || project.work_status === status.value"
                            :class="[
                                'px-5 py-2.5 rounded-lg text-sm font-medium transition-all',
                                project.work_status === status.value
                                    ? 'bg-purple-100 text-purple-700 cursor-default'
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:shadow-sm'
                            ]"
                        >
                            {{ status.label }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Project Info -->
            <div class="lg:col-span-2 overflow-hidden rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="px-6 lg:px-8 py-6 lg:py-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Project Information</h2>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="mb-2.5 block text-sm font-medium text-gray-700">Client Name</label>
                                <input
                                    v-model="form.client_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors"
                                />
                            </div>
                            <div>
                                <label class="mb-2.5 block text-sm font-medium text-gray-700">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-700">Location</label>
                            <input
                                v-model="form.location"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors"
                            />
                        </div>

                        <!-- <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-700">Status</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors bg-white"
                            >
                                <option value="NEW">New</option>
                                <option value="PENDING">Pending</option>
                                <option value="ACCEPTED">Accepted</option>
                                <option value="IN_PROGRESS">In Progress</option>
                                <option value="REJECTED">Rejected</option>
                                <option value="COMPLETED">Completed</option>
                            </select>
                        </div> -->

                        <!-- Assign Supervisor -->
                        <div>
                            <label class="mb-2.5 block text-sm font-medium text-gray-700">Assign Supervisor</label>
                            <select
                                v-model="form.supervisor_id"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors bg-white"
                            >
                                <option :value="null">Select Supervisor...</option>
                                <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">
                                    {{ supervisor.name }} ({{ supervisor.email }})
                                </option>
                            </select>
                            <p v-if="form.errors.supervisor_id" class="mt-2 text-sm text-red-600">{{ form.errors.supervisor_id }}</p>
                        </div>

                        <!-- Home Visits Section -->
                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-5">Home Visits</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="mb-2.5 block text-sm font-medium text-gray-700">Visit Date</label>
                                    <input
                                        v-model="form.home_visit_date"
                                        type="date"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors bg-white"
                                    />
                                    <p v-if="form.errors.home_visit_date" class="mt-2 text-sm text-red-600">{{ form.errors.home_visit_date }}</p>
                                </div>

                                <div>
                                    <label class="mb-2.5 block text-sm font-medium text-gray-700">Visit Time</label>
                                    <input
                                        v-model="form.home_visit_time"
                                        type="time"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors bg-white"
                                    />
                                    <p v-if="form.errors.home_visit_time" class="mt-2 text-sm text-red-600">{{ form.errors.home_visit_time }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="mb-2.5 block text-sm font-medium text-gray-700">Select Supervisors for Visit</label>
                                <select
                                    v-model="selectedHomeVisitSupervisors"
                                    multiple
                                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-colors bg-white min-h-[120px]"
                                >
                                    <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">
                                        {{ supervisor.name }} ({{ supervisor.email }})
                                    </option>
                                </select>
                                <p class="mt-2 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple supervisors</p>
                                <p v-if="form.errors.home_visit_supervisors" class="mt-2 text-sm text-red-600">{{ form.errors.home_visit_supervisors }}</p>
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4 border-t border-gray-200">
                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600 px-6 py-3 text-white font-medium hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors shadow-sm hover:shadow-md"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Quick Actions</h2>

                    <div class="space-y-4">
                        <Link
                            :href="route('supervisor.projects.create', { project_id: project.id })"
                            class="flex items-center justify-between p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors border border-green-200 hover:border-green-300 hover:shadow-sm"
                        >
                            <span class="text-green-700 font-medium">Create Quote</span>
                            <span class="text-green-500 text-lg">→</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </AdminLayout>
</template>
