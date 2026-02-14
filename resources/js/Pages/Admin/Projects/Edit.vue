<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    client_name: props.project.client_name || '',
    phone: props.project.phone || '',
    location: props.project.location || '',
    status: props.project.status || 'NEW',
});

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
        window.location.reload();
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
        <div class="mb-8">
            <Link
                :href="route('admin.projects.index')"
                class="text-sm text-blue-600 hover:text-blue-800"
            >
                ← Back to Projects
            </Link>
        </div>

        <div class="mb-8 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Project Details</h1>
            <span :class="['px-4 py-1.5 rounded-full text-sm font-medium', getStatusColor(project.status)]">
                {{ project.status }}
            </span>
        </div>

        <!-- Payment Information Section -->
        <div class="mb-8 overflow-hidden rounded-lg bg-white shadow">
            <div class="px-6 py-5 bg-blue-600 border-b">
                <h2 class="text-lg font-semibold text-white">💰 Payment Information (40-40-20)</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
                    <!-- Total Amount -->
                    <div>
                        <p class="text-sm text-gray-500">Total Quote Amount</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(project.total_amount || 0) }}</p>
                    </div>
                    
                    <!-- Payment Method -->
                    <div>
                        <p class="text-sm text-gray-500">Payment Method</p>
                        <p class="text-lg font-semibold" :class="project.payment_method === 'ONLINE' ? 'text-green-600' : 'text-orange-600'">
                            {{ project.payment_method || 'Not Selected' }}
                        </p>
                    </div>
                    
                    <!-- Booking Status -->
                    <div>
                        <p class="text-sm text-gray-500">Booking (40%)</p>
                        <p class="text-lg font-bold text-blue-600">{{ formatCurrency(getBookingAmount(project)) }}</p>
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize mt-1', getPaymentStatusColor(project.booking_status)]">
                            {{ project.booking_status || 'PENDING' }}
                        </span>
                    </div>
                    
                    <!-- Mid Status -->
                    <div>
                        <p class="text-sm text-gray-500">Mid Payment (40%)</p>
                        <p class="text-lg font-bold text-blue-600">{{ formatCurrency(getMidAmount(project)) }}</p>
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize mt-1', getPaymentStatusColor(project.mid_status)]">
                            {{ project.mid_status || 'LOCKED' }}
                        </span>
                    </div>
                    
                    <!-- Final Status -->
                    <div>
                        <p class="text-sm text-gray-500">Final Payment (20%)</p>
                        <p class="text-lg font-bold text-blue-600">{{ formatCurrency(getFinalAmount(project)) }}</p>
                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize mt-1', getPaymentStatusColor(project.final_status)]">
                            {{ project.final_status || 'LOCKED' }}
                        </span>
                    </div>
                </div>
                
                <!-- Payment Timestamps -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 text-sm">
                        <div v-if="project.booking_paid_at">
                            <p class="text-gray-500 mb-1">Booking Paid At</p>
                            <p class="font-medium text-gray-900">{{ formatDate(project.booking_paid_at) }}</p>
                        </div>
                        <div v-if="project.mid_paid_at">
                            <p class="text-gray-500 mb-1">Mid Paid At</p>
                            <p class="font-medium text-gray-900">{{ formatDate(project.mid_paid_at) }}</p>
                        </div>
                        <div v-if="project.final_paid_at">
                            <p class="text-gray-500 mb-1">Final Paid At</p>
                            <p class="font-medium text-gray-900">{{ formatDate(project.final_paid_at) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Cash Confirmation Info -->
                <div v-if="project.cash_confirmed_at" class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-green-600 font-medium">✓ Cash confirmed by supervisor on {{ formatDate(project.cash_confirmed_at) }}</p>
                </div>
                
                <!-- Admin Manual Payment Actions -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Admin Manual Actions:</p>
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
        <div class="mb-8 overflow-hidden rounded-lg bg-white shadow">
            <div class="px-6 py-5 bg-purple-600 border-b">
                <h2 class="text-lg font-semibold text-white">🔧 Work Status (Admin Control)</h2>
            </div>
            <div class="p-6">
                <!-- Current Work Status -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
                    <div>
                        <p class="text-sm text-gray-500 mb-2">Current Status</p>
                        <span :class="['inline-flex items-center px-5 py-2.5 rounded-full text-base font-bold', getWorkStatusColor(project.work_status)]">
                            {{ project.work_status || 'PENDING' }}
                        </span>
                    </div>
                    <div v-if="project.work_started_at" class="sm:pl-6 sm:border-l border-gray-200">
                        <p class="text-sm text-gray-500 mb-1">Work Started</p>
                        <p class="font-medium text-gray-900">{{ formatDate(project.work_started_at) }}</p>
                    </div>
                    <div v-if="project.work_completed_at" class="sm:pl-6 sm:border-l border-gray-200">
                        <p class="text-sm text-gray-500 mb-1">Work Completed</p>
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
                                <p :class="['text-xs mt-2 font-medium', project.work_status === status ? 'text-purple-600' : 'text-gray-500']">
                                    {{ status.replace('_', ' ') }}
                                </p>
                            </div>
                            <div v-if="index < 5" class="w-4"></div>
                        </template>
                    </div>
                </div>
                
                <!-- Admin Work Status Controls -->
                <div class="pt-6 border-t border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Change Work Status (Admin Override):</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            <!-- Project Info -->
            <div class="lg:col-span-2 overflow-hidden rounded-lg bg-white shadow">
                <div class="px-4 lg:px-6 py-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Project Information</h2>

                    <form @submit.prevent="submit" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Client Name</label>
                                <input
                                    v-model="form.client_name"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Phone</label>
                                <input
                                    v-model="form.phone"
                                    type="text"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Location</label>
                            <input
                                v-model="form.location"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="NEW">New</option>
                                <option value="PENDING">Pending</option>
                                <option value="ACCEPTED">Accepted</option>
                                <option value="IN_PROGRESS">In Progress</option>
                                <option value="REJECTED">Rejected</option>
                                <option value="COMPLETED">Completed</option>
                            </select>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button
                                type="submit"
                                class="rounded-lg bg-blue-600 px-6 py-2.5 text-white font-medium hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="px-4 lg:px-6 py-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-6">Quick Actions</h2>
                    
                    <div class="space-y-4">
                        <Link
                            :href="route('supervisor.projects.create', { project_id: project.id })"
                            class="flex items-center justify-between p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors border border-green-200"
                        >
                            <span class="text-green-700 font-medium">Create Quote</span>
                            <span class="text-green-500 text-lg">→</span>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
