<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({
    layout: AdminLayout,
});

const props = defineProps({
    project: Object
});

const form = useForm({});
const processingCashConfirm = ref(false);
const sendingWhatsApp = ref(false);

// Format currency
const formatCurrency = (value) => {
    const num = toNumber(value);
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(num);
};

// Helper to safely convert to number
const toNumber = (value) => {
    if (value === null || value === undefined) return 0;
    const num = Number(value);
    return isNaN(num) ? 0 : num;
};

// Calculate milestone totals (base + GST)
const bookingTotal = computed(() => {
    const base = toNumber(props.project.booking_amount);
    const gst = toNumber(props.project.booking_gst);
    return base + gst;
});

const midPaymentTotal = computed(() => {
    const base = toNumber(props.project.mid_amount);
    const gst = toNumber(props.project.mid_gst);
    return base + gst;
});

const finalPaymentTotal = computed(() => {
    const base = toNumber(props.project.final_amount);
    const gst = toNumber(props.project.final_gst);
    return base + gst;
});

// Total project value (accounting for discount)
const totalProjectValue = computed(() => {
    const total = toNumber(props.project.total_amount);
    const discount = toNumber(props.project.discount_amount);
    return total - discount;
});

// Payment status helpers
const isBookingPaid = () => props.project.booking_status === 'PAID';
const isMidPaymentPaid = () => props.project.mid_status === 'PAID';
const isFinalPaymentPaid = () => props.project.final_status === 'PAID';
const isCashPayment = () => props.project.payment_method === 'CASH';
const isAwaitingCashConfirmation = () => props.project.status === 'AWAITING_CASH_CONFIRMATION';
const canConfirmCash = () => isCashPayment() && !isBookingPaid() && isAwaitingCashConfirmation();
const canCollectMidPayment = () => isBookingPaid() && !isMidPaymentPaid();
const canCollectFinalPayment = () => isMidPaymentPaid() && !isFinalPaymentPaid();

// Confirm cash payment
const confirmCashPayment = (milestone) => {
    if (!confirm(`Confirm ${milestone} cash payment received from customer?`)) return;

    processingCashConfirm.value = true;

    form.post(route('admin.projects.confirm-cash', props.project.id), {
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
const collectPayment = (type) => {
    const milestone = type === 'mid' ? 'mid' : 'final';
    if (!confirm(`Confirm ${milestone} payment collected?`)) return;

    processingCashConfirm.value = true;

    const routeName = type === 'mid' ? 'admin.projects.collect-mid' : 'admin.projects.collect-final';

    form.post(route(routeName, props.project.id), {
        onSuccess: () => {
            window.location.reload();
        },
        onFinish: () => {
            processingCashConfirm.value = false;
        },
    });
};

const statusColors = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'AWAITING_CASH_CONFIRMATION': 'bg-orange-100 text-orange-800',
    'CONFIRMED': 'bg-green-100 text-green-800',
    'IN_PROGRESS': 'bg-blue-100 text-blue-800',
    'COMPLETED': 'bg-purple-100 text-purple-800',
};

const getStatusLabel = (status) => {
    const labels = {
        'DRAFT': 'Draft',
        'AWAITING_CASH_CONFIRMATION': 'Awaiting Cash Confirmation',
        'CONFIRMED': 'Confirmed',
        'IN_PROGRESS': 'In Progress',
        'COMPLETED': 'Completed',
    };
    return labels[status] || status;
};

// Send WhatsApp message
const sendWhatsAppMessage = () => {
    if (!confirm(`Send WhatsApp message to ${props.project.client_name} (${props.project.phone}) about home visit schedule?`)) {
        return;
    }

    sendingWhatsApp.value = true;

    fetch(route('admin.projects.send-whatsapp', props.project.id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
        } else {
            alert('❌ ' + (data.message || 'Failed to send WhatsApp message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Error sending WhatsApp message');
    })
    .finally(() => {
        sendingWhatsApp.value = false;
    });
};
</script>

<template>
    <div class="p-6">
        <div class="mb-6">
            <Link :href="route('admin.projects.index')" class="text-blue-600 text-sm mb-2 block">← Back to Projects</Link>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ project.client_name }}</h1>
                    <p class="text-gray-600">📍 {{ project.location }} | 📞 {{ project.phone }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <button
                        @click="sendWhatsAppMessage"
                        :disabled="sendingWhatsApp"
                        class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-green-700 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="!sendingWhatsApp" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span v-if="sendingWhatsApp" class="animate-spin">⏳</span>
                        {{ sendingWhatsApp ? 'Sending...' : 'Send WhatsApp Message' }}
                    </button>
                    <Link
                        :href="route('admin.projects.quote', project.id)"
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Quote
                    </Link>
                </div>
            </div>
        </div>

        <!-- Payment Summary Card -->
        <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
            <div class="px-6 py-4 bg-gray-100 border-b">
                <h2 class="text-lg font-semibold text-gray-800">💰 Payment Details</h2>
            </div>
            <div class="p-6">
                <!-- Status and Method -->
                <div class="flex flex-wrap gap-3 mb-4">
                    <span :class="['px-3 py-1 rounded-full text-sm font-medium', statusColors[project.status]]">
                        {{ getStatusLabel(project.status) }}
                    </span>
                    <span v-if="project.payment_method" :class="['px-3 py-1 rounded-full text-sm font-medium', {
                        'bg-green-50 text-green-700': project.payment_method === 'ONLINE',
                        'bg-orange-50 text-orange-700': project.payment_method === 'CASH',
                    }]">
                        {{ project.payment_method === 'ONLINE' ? '🟢 Online Payment' : '💵 Cash Payment' }}
                    </span>
                </div>

                <!-- Milestone Amounts -->
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600 font-medium">Booking (40%)</p>
                        <p class="text-xl font-bold text-blue-700">{{ formatCurrency(bookingTotal) }}</p>
                        <p :class="['text-sm mt-1', isBookingPaid() ? 'text-green-600' : 'text-gray-500']">
                            {{ isBookingPaid() ? '✓ Paid' : 'Pending' }}
                        </p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600 font-medium">Mid Payment (40%)</p>
                        <p class="text-xl font-bold text-blue-700">{{ formatCurrency(midPaymentTotal) }}</p>
                        <p :class="['text-sm mt-1', isMidPaymentPaid() ? 'text-green-600' : 'text-gray-500']">
                            {{ isMidPaymentPaid() ? '✓ Paid' : 'Pending' }}
                        </p>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p class="text-sm text-blue-600 font-medium">Final Payment (20%)</p>
                        <p class="text-xl font-bold text-blue-700">{{ formatCurrency(finalPaymentTotal) }}</p>
                        <p :class="['text-sm mt-1', isFinalPaymentPaid() ? 'text-green-600' : 'text-gray-500']">
                            {{ isFinalPaymentPaid() ? '✓ Paid' : 'Pending' }}
                        </p>
                    </div>
                </div>

                <!-- Total Breakdown -->
                <div class="border-t pt-4">
                    <h3 class="text-lg font-medium text-gray-800 mb-3">Price Breakdown</h3>

                    <!-- Subtotal (Before GST) -->
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Subtotal (Before GST)</span>
                        <span class="text-sm font-medium text-gray-700">{{ formatCurrency(project.base_total) }}</span>
                    </div>

                    <!-- GST -->
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">GST ({{ project.gst_rate }}%)</span>
                        <span class="text-sm font-medium text-gray-700">{{ formatCurrency(toNumber(project.base_total) * (toNumber(project.gst_rate) / 100)) }}</span>
                    </div>

                    <!-- Discount Applied -->
                    <div v-if="project.discount_amount > 0" class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Discount ({{ project.coupon_code || 'Manual' }})</span>
                        <span class="text-sm font-medium text-green-600">-{{ formatCurrency(project.discount_amount) }}</span>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 my-3"></div>

                    <!-- Total Project Value -->
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">Total Project Value</span>
                        <span class="text-2xl font-bold text-blue-600">{{ formatCurrency(totalProjectValue) }}</span>
                    </div>
                </div>

                <!-- Cash Confirmation Actions -->
                <div v-if="canConfirmCash()" class="mt-4 bg-orange-50 border border-orange-200 rounded-lg p-4">
                    <p class="font-medium text-orange-800 mb-2">💰 Cash Payment Pending Confirmation</p>
                    <button
                        @click="confirmCashPayment('booking')"
                        :disabled="processingCashConfirm"
                        class="w-full h-12 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 disabled:opacity-50"
                    >
                        {{ processingCashConfirm ? 'Processing...' : '✓ Mark Booking Cash Received' }}
                    </button>
                </div>

                <!-- Mid Payment Collection -->
                <div v-if="canCollectMidPayment()" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="font-medium text-blue-800 mb-2">💰 Collect Mid Payment</p>
                    <button
                        @click="collectPayment('mid')"
                        :disabled="processingCashConfirm"
                        class="w-full h-12 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 disabled:opacity-50"
                    >
                        {{ processingCashConfirm ? 'Processing...' : '✓ Mark Mid Payment Received' }}
                    </button>
                </div>

                <!-- Final Payment Collection -->
                <div v-if="canCollectFinalPayment()" class="mt-4 bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <p class="font-medium text-purple-800 mb-2">💰 Collect Final Payment</p>
                    <button
                        @click="collectPayment('final')"
                        :disabled="processingCashConfirm"
                        class="w-full h-12 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 disabled:opacity-50"
                    >
                        {{ processingCashConfirm ? 'Processing...' : '✓ Mark Final Payment Received' }}
                    </button>
                </div>

                <!-- Cash Confirmed By Info -->
                <div v-if="project.cash_confirmed_by" class="mt-4 text-sm text-green-600">
                    ✓ Cash confirmed by supervisor on {{ project.cash_confirmed_at }}
                </div>
            </div>
        </div>

        <!-- Rooms/Zones -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 bg-gray-100 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Rooms/Zones</h2>
            </div>
            <div class="p-6">
                <div v-if="project.rooms && project.rooms.length > 0" class="space-y-4">
                    <div v-for="room in project.rooms" :key="room.id" class="border rounded-lg p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium text-gray-800">{{ room.name }}</h3>
                                <p class="text-sm text-gray-500">{{ room.type }}</p>
                            </div>
                            <span class="font-bold text-gray-900">{{ formatCurrency(room.total_amount) }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                    No rooms/zones added yet.
                </div>
            </div>
        </div>

        <!-- Project Photos -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 bg-orange-100 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">📷 Project Photos</h2>
                <Link
                    :href="route('admin.project-photos.index', project.id)"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg text-sm font-medium hover:bg-orange-600"
                >
                    View Photos
                </Link>
            </div>
            <div class="p-6">
                <p class="text-gray-500 text-sm">
                    View and manage project photos (Before / In Progress / After) uploaded by supervisors.
                </p>
            </div>
        </div>

        <!-- Warranty Section -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 bg-green-100 border-b flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">🛡️ Warranty Certificate</h2>
                <a
                    v-if="isBookingPaid() && isMidPaymentPaid() && isFinalPaymentPaid()"
                    :href="route('admin.warranty.download', project.id)"
                    target="_blank"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700"
                >
                    📥 Download PDF
                </a>
            </div>
            <div class="p-6">
                <div v-if="isBookingPaid() && isMidPaymentPaid() && isFinalPaymentPaid()">
                    <p class="text-green-600 font-medium mb-2">✓ Project is fully paid - Warranty available</p>
                    <p class="text-gray-500 text-sm">
                        The warranty certificate has been generated based on eligible painting systems used in this project.
                        Download the PDF to view the complete warranty schedule.
                    </p>
                </div>
                <div v-else class="text-gray-500">
                    <p>Warranty will be available after 100% payment completion.</p>
                    <p class="text-sm mt-1">All milestone payments must be completed to generate the warranty certificate.</p>
                </div>
            </div>
        </div>
    </div>
</template>
