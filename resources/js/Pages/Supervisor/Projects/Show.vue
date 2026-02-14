<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    project: Object
});

const form = useForm({
    name: '',
    type: 'INTERIOR',
    length: '',
    breadth: '',
    height: ''
});

const processingCashConfirm = ref(false);
const processingWorkStatus = ref(false);

// ==================== Work Status Helpers ====================

const workStatuses = [
    { value: 'PENDING', label: 'Pending', color: 'bg-gray-100 text-gray-800' },
    { value: 'ASSIGNED', label: 'Assigned', color: 'bg-blue-100 text-blue-800' },
    { value: 'IN_PROGRESS', label: 'In Progress', color: 'bg-green-100 text-green-800' },
    { value: 'ON_HOLD', label: 'On Hold', color: 'bg-yellow-100 text-yellow-800' },
    { value: 'COMPLETED', label: 'Completed', color: 'bg-purple-100 text-purple-800' },
    { value: 'CLOSED', label: 'Closed', color: 'bg-gray-500 text-white' },
];

const getWorkStatusColor = (status) => {
    const found = workStatuses.find(s => s.value === status);
    return found ? found.color : 'bg-gray-100 text-gray-800';
};

const getWorkStatusLabel = (status) => {
    const found = workStatuses.find(s => s.value === status);
    return found ? found.label : status;
};

// Get available work status transitions for supervisor
const getAvailableWorkStatus = () => {
    const currentStatus = props.project.work_status;
    
    // Supervisor transitions: ASSIGNED → IN_PROGRESS → ON_HOLD → COMPLETED
    const transitions = {
        'ASSIGNED': 'IN_PROGRESS',
        'IN_PROGRESS': 'ON_HOLD',
        'ON_HOLD': 'IN_PROGRESS',
        'IN_PROGRESS': 'COMPLETED',
    };
    
    return transitions[currentStatus] || null;
};

const getWorkActionLabel = (status) => {
    return {
        'IN_PROGRESS': 'Start Work',
        'ON_HOLD': 'Pause Work',
        'COMPLETED': 'Mark Completed',
    }[status] || 'Update Status';
};

const getWorkActionColor = (status) => {
    return {
        'IN_PROGRESS': 'bg-green-600 hover:bg-green-700',
        'ON_HOLD': 'bg-yellow-500 hover:bg-yellow-600',
        'COMPLETED': 'bg-purple-600 hover:bg-purple-700',
    }[status] || 'bg-blue-600';
};

// Update work status
const updateWorkStatus = async (newStatus) => {
    if (!confirm(`Change work status to "${getWorkStatusLabel(newStatus)}"?`)) return;
    
    processingWorkStatus.value = true;
    
    try {
        const res = await axios.post(`/supervisor/projects/${props.project.id}/work-status`, {
            work_status: newStatus
        });
        
        if (res.data.success || !res.data.error) {
            window.location.reload();
        } else {
            alert(res.data.message || res.data.error || 'Failed to update work status');
        }
    } catch (e) {
        alert(e.response?.data?.message || e.response?.data?.error || 'An error occurred');
    } finally {
        processingWorkStatus.value = false;
    }
};

const addZone = () => {
    form.post(route('supervisor.zones.store', props.project.id), {
        onSuccess: () => form.reset(),
    });
};

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(value || 0);
};

// Calculate project total
const projectTotal = () => {
    if (!props.project.rooms) return 0;
    return props.project.rooms.reduce((sum, room) => sum + (room.total_amount || 0), 0);
};

// Check if any zones have items
const hasAnyItems = () => {
    if (!props.project.rooms) return false;
    return props.project.rooms.some(room => (room.total_items_count || 0) > 0);
};

// Payment status helpers
const isBookingPaid = () => props.project.booking_status === 'PAID';
const isMidPaymentPaid = () => props.project.mid_status === 'PAID';
const isFinalPaymentPaid = () => props.project.final_status === 'PAID';
const isCashPayment = () => props.project.payment_method === 'CASH';
const isBookingCashPending = () => props.project.booking_status === 'CASH_PENDING';
const canConfirmCash = () => isCashPayment() && isBookingCashPending();
const canCollectMidPayment = () => isBookingPaid() && !isMidPaymentPaid();
const canCollectFinalPayment = () => isMidPaymentPaid() && !isFinalPaymentPaid();

// Confirm cash booking payment
const confirmCashBooking = async () => {
    if (!confirm('Confirm cash booking payment received from customer?')) return;
    
    processingCashConfirm.value = true;
    
    try {
        const res = await axios.post(`/supervisor/projects/${props.project.id}/confirm-cash-booking`, {});
        
        if (res.data.success) {
            window.location.reload();
        } else {
            alert(res.data.message || 'Failed to confirm cash payment');
        }
    } catch (e) {
        alert(e.response?.data?.message || 'An error occurred');
    } finally {
        processingCashConfirm.value = false;
    }
};

// Confirm cash payment for milestone
const confirmCashPayment = (milestone) => {
    if (!confirm(`Confirm ${milestone} cash payment received from customer?`)) return;
    
    processingCashConfirm.value = true;
    
    form.post(route('supervisor.projects.confirm-cash', props.project.id), {
        data: { milestone },
        onSuccess: () => {
            window.location.reload();
        },
        onFinish: () => {
            processingCashConfirm.value = false;
        },
    });
};

// Collect mid/final payment
const collectPayment = async (type) => {
    const milestone = type === 'mid' ? 'mid' : 'final';
    const paymentMethod = props.project.payment_method === 'CASH' ? 'CASH' : 'ONLINE';
    if (!confirm(`Confirm ${milestone} payment collected via ${paymentMethod}?`)) return;
    
    processingCashConfirm.value = true;
    
    try {
        const res = await axios.post(`/supervisor/projects/${props.project.id}/collect-${milestone}`, {
            payment_method: paymentMethod
        });
        
        if (res.data.success) {
            window.location.reload();
        } else {
            alert(res.data.message || 'Failed to collect payment');
        }
    } catch (e) {
        alert(e.response?.data?.message || 'An error occurred');
    } finally {
        processingCashConfirm.value = false;
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-24">
        <div class="bg-blue-600 p-4 text-white shadow-md">
            <Link :href="route('supervisor.projects.index')" class="text-blue-100 text-sm mb-2 block">← Back to Projects</Link>
            <h1 class="text-xl font-bold">{{ project.client_name }}</h1>
            <p class="text-blue-100 text-sm">📍 {{ project.location }}</p>
            
            <!-- Payment Status Indicator -->
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <!-- Status Badge -->
                <span :class="['px-2 py-1 rounded text-xs font-bold', {
                    'bg-gray-100 text-gray-800': project.status === 'DRAFT',
                    'bg-orange-100 text-orange-800': project.status === 'AWAITING_CASH_CONFIRMATION',
                    'bg-green-100 text-green-800': project.status === 'CONFIRMED',
                    'bg-blue-100 text-blue-800': project.status === 'IN_PROGRESS',
                    'bg-purple-100 text-purple-800': project.status === 'COMPLETED',
                }]">
                    {{ project.status === 'AWAITING_CASH_CONFIRMATION' ? 'AWAITING CASH' : project.status }}
                </span>
                
                <!-- Payment Method -->
                <span v-if="project.payment_method" :class="['px-2 py-1 rounded text-xs font-bold', {
                    'bg-green-50 text-green-700': project.payment_method === 'ONLINE',
                    'bg-orange-50 text-orange-700': project.payment_method === 'CASH',
                }]">
                    {{ project.payment_method === 'ONLINE' ? '🟢 Online' : '💵 Cash' }}
                </span>
            </div>
            
            <!-- Cash Confirmation Alert -->
            <div v-if="canConfirmCash()" class="mt-3 bg-orange-500 p-3 rounded-lg">
                <p class="text-sm font-medium">💰 Cash Booking Pending Confirmation</p>
                <p class="text-xs text-orange-100">Booking Amount: {{ formatCurrency(project.booking_amount || 0) }}</p>
                <button
                    @click="confirmCashBooking"
                    :disabled="processingCashConfirm"
                    class="mt-2 w-full h-10 bg-white text-orange-600 rounded-lg font-bold text-sm hover:bg-orange-50 disabled:opacity-50"
                >
                    {{ processingCashConfirm ? 'Processing...' : '✓ Confirm Cash Booking Received' }}
                </button>
            </div>
            
            <!-- Milestone Payments Progress -->
            <div v-if="isBookingPaid()" class="mt-3 bg-blue-700 p-3 rounded-lg">
                <p class="text-xs font-medium mb-2">📊 Payment Milestones</p>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div :class="['p-2 rounded', isBookingPaid() ? 'bg-green-500' : 'bg-blue-500']">
                        <p class="font-bold">Booking</p>
                        <p>{{ formatCurrency(project.booking_amount || 0) }}</p>
                        <p class="text-xs opacity-75">{{ isBookingPaid() ? '✓ Paid' : 'Pending' }}</p>
                    </div>
                    <div :class="['p-2 rounded', isMidPaymentPaid() ? 'bg-green-500' : 'bg-blue-500']">
                        <p class="font-bold">Mid</p>
                        <p>{{ formatCurrency(project.mid_amount || 0) }}</p>
                        <p class="text-xs opacity-75">{{ isMidPaymentPaid() ? '✓ Paid' : 'Pending' }}</p>
                    </div>
                    <div :class="['p-2 rounded', isFinalPaymentPaid() ? 'bg-green-500' : 'bg-blue-500']">
                        <p class="font-bold">Final</p>
                        <p>{{ formatCurrency(project.final_amount || 0) }}</p>
                        <p class="text-xs opacity-75">{{ isFinalPaymentPaid() ? '✓ Paid' : 'Pending' }}</p>
                    </div>
                </div>
                
                <!-- Collect Mid Payment Button -->
                <button 
                    v-if="canCollectMidPayment()"
                    @click="collectPayment('mid')"
                    :disabled="processingCashConfirm"
                    class="mt-2 w-full h-10 bg-white text-blue-600 rounded-lg font-bold text-sm hover:bg-blue-50 disabled:opacity-50"
                >
                    {{ processingCashConfirm ? 'Processing...' : '💰 Collect Mid Payment' }}
                </button>
                
                <!-- Collect Final Payment Button -->
                <button 
                    v-if="canCollectFinalPayment()"
                    @click="collectPayment('final')"
                    :disabled="processingCashConfirm"
                    class="mt-2 w-full h-10 bg-white text-purple-600 rounded-lg font-bold text-sm hover:bg-purple-50 disabled:opacity-50"
                >
                    {{ processingCashConfirm ? 'Processing...' : '💰 Collect Final Payment' }}
                </button>
                
                <!-- Cash Confirmed By Info -->
                <p v-if="project.cash_confirmed_by" class="mt-2 text-xs text-green-200">
                    ✓ Cash confirmed by supervisor
                </p>
            </div>
        </div>

        <!-- Work Status Section -->
        <div class="bg-white p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase mb-1">Work Progress</p>
                    <div class="flex items-center gap-2">
                        <span :class="['px-3 py-1.5 rounded-lg text-sm font-bold', getWorkStatusColor(project.work_status)]">
                            {{ getWorkStatusLabel(project.work_status) }}
                        </span>
                        <span v-if="project.work_started_at" class="text-xs text-gray-500">
                            Started: {{ new Date(project.work_started_at).toLocaleDateString() }}
                        </span>
                        <span v-if="project.work_completed_at" class="text-xs text-gray-500">
                            Completed: {{ new Date(project.work_completed_at).toLocaleDateString() }}
                        </span>
                    </div>
                </div>
                
                <!-- Work Status Action Button -->
                <button
                    v-if="getAvailableWorkStatus()"
                    @click="updateWorkStatus(getAvailableWorkStatus())"
                    :disabled="processingWorkStatus"
                    :class="['h-12 px-6 rounded-lg font-bold text-white shadow transition disabled:opacity-50', getWorkActionColor(getAvailableWorkStatus())]"
                >
                    {{ processingWorkStatus ? 'Processing...' : getWorkActionLabel(getAvailableWorkStatus()) }}
                </button>
            </div>
            
            <!-- Work Status Timeline -->
            <div class="mt-4 flex items-center gap-1">
                <template v-for="(status, index) in ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'COMPLETED', 'CLOSED']" :key="status">
                    <div class="flex-1">
                        <div :class="['h-2 rounded-full transition',
                            project.work_status === status ? 'bg-blue-600' :
                            ['PENDING', 'ASSIGNED', 'IN_PROGRESS', 'ON_HOLD', 'COMPLETED', 'CLOSED'].indexOf(project.work_status) > index ? 'bg-green-500' : 'bg-gray-200'
                        ]"></div>
                        <p :class="['text-xs mt-1 text-center', project.work_status === status ? 'font-bold text-blue-600' : 'text-gray-500']">
                            {{ getWorkStatusLabel(status) }}
                        </p>
                    </div>
                    <div v-if="index < 5" class="w-2"></div>
                </template>
            </div>
        </div>

        <div class="p-4 max-w-2xl mx-auto">
            <!-- Manual Payment Actions for Supervisor -->
            <div v-if="project.booking_status === 'PAID'" class="mt-3 bg-gray-800 p-3 rounded-lg">
                <p class="text-xs font-medium mb-2 text-gray-300">💳 Manual Payment Actions</p>
                <div class="flex flex-wrap gap-2">
                    <button
                        v-if="project.mid_status !== 'PAID'"
                        @click="collectPayment('mid')"
                        :disabled="processingCashConfirm"
                        class="px-3 py-1 bg-blue-500 text-white rounded text-xs font-medium hover:bg-blue-600 disabled:opacity-50"
                    >
                        Mark Mid PAID
                    </button>
                    <button
                        v-if="project.mid_status === 'PAID' && project.final_status !== 'PAID'"
                        @click="collectPayment('final')"
                        :disabled="processingCashConfirm"
                        class="px-3 py-1 bg-purple-500 text-white rounded text-xs font-medium hover:bg-purple-600 disabled:opacity-50"
                    >
                        Mark Final PAID
                    </button>
                </div>
            </div>
        </div>

        <div class="p-4 max-w-2xl mx-auto">
            <!-- Screen A: Zone Creation Form -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Create New Zone</h2>
                
                <!-- Zone Name -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Zone Name</label>
                    <input v-model="form.name" type="text" placeholder="e.g. Master Bedroom" 
                        class="w-full h-12 px-4 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-base">
                </div>

                <!-- Zone Type -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Zone Type</label>
                    <div class="flex gap-3">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" v-model="form.type" value="INTERIOR" class="sr-only peer">
                            <div class="h-12 flex items-center justify-center border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 text-gray-600 peer-checked:text-blue-600 font-medium transition">
                                Interior
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" v-model="form.type" value="EXTERIOR" class="sr-only peer">
                            <div class="h-12 flex items-center justify-center border-2 border-gray-200 rounded-lg peer-checked:border-blue-600 peer-checked:bg-blue-50 text-gray-600 peer-checked:text-blue-600 font-medium transition">
                                Exterior
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Default Dimensions -->
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Default Dimensions (ft)</label>
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <input v-model="form.length" type="number" step="0.01" placeholder="L"
                                class="w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center text-base">
                        </div>
                        <div class="flex-1">
                            <input v-model="form.breadth" type="number" step="0.01" placeholder="B"
                                class="w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center text-base">
                        </div>
                        <div class="flex-1">
                            <input v-model="form.height" type="number" step="0.01" placeholder="H"
                                class="w-full h-12 px-3 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 text-center text-base">
                        </div>
                    </div>
                </div>

                <!-- Create Button -->
                <button @click="addZone" :disabled="form.processing"
                    class="w-full h-12 bg-blue-600 text-white rounded-lg font-bold shadow hover:bg-blue-700 disabled:opacity-50 transition text-base">
                    Create Zone
                </button>
            </div>

            <!-- Zones List (Overview) -->
            <h3 class="text-gray-800 font-bold mb-3">Project Zones</h3>
            
            <div v-if="project.rooms && project.rooms.length > 0" class="space-y-3">
                <Link v-for="room in project.rooms" :key="room.id"
                     :href="route('supervisor.zones.show', room.id)"
                     class="block bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:bg-blue-50 cursor-pointer transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-bold text-gray-900">{{ room.name }}</h4>
                                <span :class="room.type === 'INTERIOR' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'" 
                                      class="px-2 py-0.5 rounded-full text-xs font-medium">
                                    {{ room.type }}
                                </span>
                            </div>
                            <p v-if="room.length || room.breadth || room.height" class="text-sm text-gray-600">
                                {{ room.length || '-' }} × {{ room.breadth || '-' }} × {{ room.height || '-' }} ft
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ room.total_items_count || 0 }} Items Added
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(room.total_amount) }}</p>
                            <span class="text-gray-300">→</span>
                        </div>
                    </div>
                </Link>
            </div>

            <div v-else class="text-center py-10 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="text-gray-300 text-4xl mb-2">🏠</div>
                <p class="text-gray-500 font-medium">No zones yet</p>
                <p class="text-xs text-gray-400">Create a zone above to start measuring.</p>
            </div>

            <!-- Warranty Section (Read-only for Supervisor) -->
            <div class="mt-4 bg-white p-4 rounded-lg border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                            <span class="text-xl">🛡️</span>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">Warranty Certificate</p>
                            <p v-if="isBookingPaid() && isMidPaymentPaid() && isFinalPaymentPaid()" class="text-sm text-green-600">
                                ✓ Available - Project fully paid
                            </p>
                            <p v-else class="text-sm text-gray-500">
                                Available after 100% payment completion
                            </p>
                        </div>
                    </div>
                    <a
                        v-if="isBookingPaid() && isMidPaymentPaid() && isFinalPaymentPaid()"
                        :href="route('supervisor.warranty.view', project.id)"
                        target="_blank"
                        class="h-12 px-6 bg-green-600 text-white rounded-lg font-bold flex items-center justify-center hover:bg-green-700"
                    >
                        View Warranty
                    </a>
                </div>
            </div>

            <!-- Proceed to Quote Summary CTA -->
            <div v-if="hasAnyItems()" class="fixed bottom-0 left-0 right-0 bg-white border-t p-4 shadow-lg z-50">
                <div class="max-w-2xl mx-auto flex gap-2">
                    <Link
                        :href="route('supervisor.photos.index', project.id)"
                        class="flex-1 h-14 bg-orange-500 text-white rounded-xl font-bold text-lg shadow hover:bg-orange-600 transition text-center flex items-center justify-center"
                    >
                        📷 Photos
                    </Link>
                    <Link
                        :href="route('supervisor.summary', project.id)"
                        class="flex-[2] h-14 bg-green-600 text-white rounded-xl font-bold text-lg shadow hover:bg-green-700 transition text-center flex items-center justify-center"
                    >
                        Proceed to Quote Summary → {{ formatCurrency(projectTotal()) }}
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
