<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const branding = page.props.branding || {};
const companyName = branding.company_name || 'PaintUp';
const logoUrl = branding.logo_url;
const primaryColor = branding.primary_color || '#2563eb';
const supportEmail = branding.support_email;
const supportWhatsapp = branding.support_whatsapp;
const address = branding.address;
const gstNumber = branding.gst_number;

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    totals: {
        type: Object,
        required: true,
    },
    isLoggedIn: {
        type: Boolean,
        default: false,
    },
    publicToken: {
        type: String,
        default: '',
    },
    isAdminView: {
        type: Boolean,
        default: false,
    },
    notes: {
        type: String,
        default: null,
    },
});

// Formatters
const formatNumber = (value, decimals = 0) => {
    const num = Number(value);
    if (isNaN(num) || !isFinite(num)) {
        return String(value ?? '-');
    }
    return new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    }).format(num);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatCurrencyWithDecimals = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};

// Computed values
const baseTotal = computed(() => {
    return props.project?.base_total || props.totals.base_total || 0;
});

// Coupon info
const couponCode = computed(() => props.project?.coupon_code || null);
const discountAmount = computed(() => Number(props.project?.discount_amount || 0));
const hasCoupon = computed(() => !!couponCode.value && discountAmount.value > 0);

const gstRate = computed(() => props.project?.gst_rate || 18);

const bookingAmount = computed(() => Math.round(baseTotal.value * 0.40));
const midAmount = computed(() => Math.round(baseTotal.value * 0.40));
const finalAmount = computed(() => Math.round(baseTotal.value * 0.20));

const bookingGst = computed(() => formatCurrencyWithDecimals(bookingAmount.value * gstRate.value / 100));
const midGst = computed(() => formatCurrencyWithDecimals(midAmount.value * gstRate.value / 100));
const finalGst = computed(() => formatCurrencyWithDecimals(finalAmount.value * gstRate.value / 100));

const bookingTotal = computed(() => formatCurrencyWithDecimals(bookingAmount.value * (1 + gstRate.value / 100)));
const midTotal = computed(() => formatCurrencyWithDecimals(midAmount.value * (1 + gstRate.value / 100)));
const finalTotal = computed(() => formatCurrencyWithDecimals(finalAmount.value * (1 + gstRate.value / 100)));

// Payment status
const isBookingPaid = computed(() => props.project?.booking_status === 'PAID');
const isMidPaid = computed(() => props.project?.mid_status === 'PAID');
const isFinalPaid = computed(() => props.project?.final_status === 'PAID');

// Get payment URL
const getPaymentUrl = (milestone) => {
    return route('customer.payment.page', { project: props.project.id, milestone });
};

// Get dashboard URL
const getDashboardUrl = () => {
    if (props.isAdminView) {
        return route('admin.projects.index');
    }
    return route('customer.dashboard');
};

// Format measurement display
const formatMeasurement = (item) => {
    const qty = Number(item.net_qty ?? item.gross_qty ?? item.qty ?? 0);
    const unit = item.unit_type || item.measurement_mode || '';

    if (unit === 'AREA' || unit === 'MANUAL') {
        return `${formatNumber(qty, 0)} sqft`;
    } else if (unit === 'LUMPSUM') {
        return 'Lump Sum';
    } else if (unit === 'LINEAR') {
        return `${formatNumber(qty, 0)} rft`;
    } else if (unit === 'COUNT') {
        return `${formatNumber(qty, 0)} units`;
    }
    return `${formatNumber(qty, 0)} ${unit || 'units'}`;
};

// Get painting system details
const getSystemDetails = (item) => {
    const system = item.system || {};
    const product = item.product || {};
    const surface = item.surface || {};

    // Format: Surface Name + Product Name
    const surfaceName = surface.name || 'Surface';
    const productName = product.name || system.system_name || '';
    const displayName = productName ? `${surfaceName} + ${productName}` : surfaceName;

    return {
        displayName: displayName,
        surfaceName: surfaceName,
        productName: productName,
        processRemarks: system.process_remarks || null,
        colorCode: item.color_code || null,
        description: item.description || null,
        rate: Number(item.rate || 0),
        netQty: Number(item.net_qty || 0)
    };
};

// Get service details
const getServiceDetails = (service) => {
    const details = [];

    // Quantity
    const qty = Number(service.quantity || 0);
    const unit = service.unit_type || '';

    if (qty > 0) {
        if (unit === 'AREA' || unit === 'MANUAL') {
            details.push(`${formatNumber(qty, 0)} sqft`);
        } else if (unit === 'LINEAR') {
            details.push(`${formatNumber(qty, 0)} rft`);
        } else if (unit === 'COUNT') {
            details.push(`${formatNumber(qty, 0)} units`);
        } else if (unit === 'LUMPSUM') {
            details.push('Lump Sum');
        } else {
            details.push(`${formatNumber(qty, 0)} ${unit || 'units'}`);
        }
    }

    // Rate - use proper unit label
    const rate = Number(service.rate || 0);
    if (rate > 0) {
        let unitLabel = unit;
        if (unit === 'AREA' || unit === 'MANUAL') {
            unitLabel = 'sqft';
        } else if (unit === 'LINEAR') {
            unitLabel = 'rft';
        } else if (unit === 'COUNT') {
            unitLabel = 'unit';
        }

        if (unit === 'LUMPSUM') {
            details.push(`@ ₹${formatNumber(rate, 0)}`);
        } else {
            details.push(`@ ₹${formatNumber(rate, 0)}/${unitLabel}`);
        }
    }

    return details.join(' ');
};

// Get milestone status
const getMilestoneStatus = (milestone) => {
    const status = props.project?.[`${milestone}_status`];
    if (status === 'PAID') return 'Paid';
    if (milestone === 'mid' && !isBookingPaid.value) return 'Locked';
    if (milestone === 'final' && !isMidPaid.value) return 'Locked';
    return 'Pending';
};

// Get milestone icon
const getMilestoneIcon = (milestone) => {
    const status = props.project?.[`${milestone}_status`];
    if (status === 'PAID') return '✅';
    if (milestone === 'mid' && !isBookingPaid.value) return '🔒';
    if (milestone === 'final' && !isMidPaid.value) return '🔒';
    return '⏳';
};

// Get notes - check both props.notes and project.quote.notes as fallback
const displayNotes = computed(() => {
    return props.notes || props.project?.quote?.notes || null;
});

// Print functionality
const printQuote = () => {
    window.print();
};

// SMS/WhatsApp functionality
const sendingSMS = ref(false);
const smsMessage = ref('');

const sendSMS = async () => {
    if (sendingSMS.value) return;

    sendingSMS.value = true;
    smsMessage.value = '';

    try {
        const response = await fetch(route('customer.quote.send-sms', props.project.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = await response.json();

        if (data.success) {
            smsMessage.value = data.message || 'Quote sent successfully!';
        } else {
            smsMessage.value = data.message || 'Failed to send quote. Please try again.';
        }
    } catch (error) {
        smsMessage.value = 'An error occurred. Please try again.';
        console.error('SMS Error:', error);
    } finally {
        sendingSMS.value = false;
        // Clear message after 5 seconds
        setTimeout(() => {
            smsMessage.value = '';
        }, 5000);
    }
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-6 px-4">
        <div class="max-w-4xl mx-auto" v-if="project">
            <!-- Back Link and Action Buttons -->
            <div class="mb-4 flex items-center justify-between no-print">
                <a
                    v-if="isLoggedIn || isAdminView"
                    :href="getDashboardUrl()"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ isAdminView ? 'Back to Projects' : 'Back to Dashboard' }}
                </a>

                <!-- Print and SMS Buttons (only for logged-in customers) -->
                <div v-if="isLoggedIn && !isAdminView" class="flex items-center gap-3">
                    <button
                        @click="printQuote"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors shadow-sm"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button
                        @click="sendSMS"
                        :disabled="sendingSMS"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-white transition-colors shadow-sm"
                        :style="{ backgroundColor: sendingSMS ? '#9ca3af' : primaryColor }"
                        :class="{ 'cursor-not-allowed opacity-50': sendingSMS }"
                    >
                        <svg v-if="!sendingSMS" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                        </svg>
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ sendingSMS ? 'Sending...' : 'Send via WhatsApp' }}
                    </button>
                    <p v-if="smsMessage" class="text-sm px-3 py-1 rounded" :class="smsMessage.includes('success') ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'">
                        {{ smsMessage }}
                    </p>
                </div>
            </div>

            <!-- Company Header with Logo -->
            <div class="bg-white rounded-xl shadow-lg mb-6 overflow-hidden border border-gray-100">
                <!-- Top Bar: Dark Blue-Grey with Logo and Company Name -->
                <div class="px-6 py-4" style="background-color: #2c3e50;">
                    <div class="flex items-center justify-center">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <img
                                v-if="logoUrl"
                                :src="logoUrl"
                                :alt="companyName"
                                class="h-10 w-10 object-contain"
                            />
                            <ApplicationLogo v-else class="h-10 w-10 fill-current text-white" />
                        </div>
                    </div>
                </div>

                <!-- Red Bar: Quotation Title -->
                <div class="px-6 py-4 text-center" :style="{ backgroundColor: primaryColor }">
                    <p class="text-lg font-bold text-white uppercase tracking-wide">Quotation for Home Painting service</p>
                </div>

                <!-- Support Contact Information -->
                <div v-if="supportEmail || supportWhatsapp" class="px-6 py-3 bg-gray-50 border-b border-gray-200">
                    <div class="flex items-center justify-center gap-6 flex-wrap">
                        <a v-if="supportEmail" :href="`mailto:${supportEmail}`" class="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ supportEmail }}
                        </a>
                        <a v-if="supportWhatsapp" :href="`https://wa.me/${supportWhatsapp.replace(/[^0-9]/g, '')}`" target="_blank" class="inline-flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            {{ supportWhatsapp }}
                        </a>
                    </div>
                </div>

                <!-- Greeting Section -->
                <div class="px-6 py-6 bg-white border-b border-gray-200">
                    <p class="text-lg font-medium text-gray-900 mb-3">Hi {{ project.client_name || 'Customer' }},</p>
                    <p class="text-gray-700 leading-relaxed">
                        We thank you for giving us the opportunity to quote you for Home Painting service. We are pleased to quote you our best offer for same as under:
                    </p>
                </div>

                <!-- Quote Details Section -->
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Quote Details</h2>
                            <p class="text-gray-500 mt-1">Project #{{ project.id }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Quote Date</p>
                            <p class="font-medium">{{ new Date(project.created_at || new Date()).toLocaleDateString('en-IN', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                        </div>
                    </div>

                    <!-- Client Info -->
                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                        <div>
                            <p class="text-sm text-gray-500">Client</p>
                            <p class="font-medium text-gray-900">{{ project.client_name || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="font-medium text-gray-900">{{ project.location || 'N/A' }}</p>
                        </div>
                        <!-- <div v-if="gstNumber">
                            <p class="text-sm text-gray-500">GST Number</p>
                            <p class="font-medium text-gray-900">{{ gstNumber }}</p>
                        </div> -->
                    </div>
                </div>
            </div>

            <!-- Total Amount Card - NoBroker Style -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm text-gray-500">Total Amount</p>
                        <p class="text-3xl font-bold text-gray-900">{{ formatCurrency(baseTotal) }}</p>
                        <p class="text-sm text-gray-500 mt-1">(Excl. GST)</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Rooms</p>
                        <p class="text-xl font-semibold text-gray-700">{{ project.rooms?.length || 0 }}</p>
                    </div>
                </div>

                <!-- Coupon Applied -->
                <div v-if="hasCoupon" class="mt-4 p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="text-green-600 mr-2">🏷️</span>
                            <div>
                                <p class="text-sm font-medium text-green-700">Coupon Applied</p>
                                <p class="text-xs text-green-600">{{ couponCode }}</p>
                            </div>
                        </div>
                        <span class="text-green-600 font-medium">-{{ formatCurrency(discountAmount) }}</span>
                    </div>
                </div>

                <!-- GST Notice -->
                <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-blue-700">
                        ℹ️ GST will be applicable at the time of payment. Amount shown above is excluding GST.
                    </p>
                </div>
            </div>

            <!-- Itemized Breakdown -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">📋 Itemized Breakdown</h2>
                </div>

                <!-- Rooms -->
                <div v-if="project.rooms?.length > 0">
                    <div v-for="(room, index) in project.rooms" :key="index" class="border-b border-gray-100 last:border-0">
                        <div class="px-6 py-4">
                            <div class="flex justify-between items-center mb-3">
                                <h3 class="font-medium text-gray-800">{{ room.name || `Room ${index + 1}` }}</h3>
                                <span class="text-sm text-gray-500">{{ room.items?.length || 0 }} items</span>
                            </div>

                            <!-- Painting Items -->
                            <div v-if="room.items?.length > 0" class="ml-4 space-y-3">
                                <div v-for="(item, itemIndex) in room.items" :key="itemIndex" class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800">{{ getSystemDetails(item).surfaceName }}</p>
                                            <p class="text-sm text-gray-600">{{ getSystemDetails(item).productName }}</p>
                                            <p class="text-sm text-gray-600 mt-1">
                                                {{ formatMeasurement(item) }}
                                                <span v-if="getSystemDetails(item).rate > 0">@ ₹{{ formatNumber(getSystemDetails(item).rate, 0) }}/sqft</span>
                                            </p>
                                            <p v-if="getSystemDetails(item).processRemarks" class="text-xs text-gray-500 mt-1">
                                                {{ getSystemDetails(item).processRemarks }}
                                            </p>
                                            <p v-if="getSystemDetails(item).colorCode" class="text-xs text-gray-500">
                                                🎨 Color: {{ getSystemDetails(item).colorCode }}
                                            </p>
                                            <p v-if="item.remarks" class="text-xs text-gray-400 mt-1">
                                                Note: {{ item.remarks }}
                                            </p>
                                        </div>
                                        <p class="font-bold text-gray-900 ml-4">{{ formatCurrency(item.amount || 0) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Services -->
                            <div v-if="room.services?.length > 0" class="mt-3 ml-4 space-y-3">
                                <div v-for="(service, serviceIndex) in room.services" :key="serviceIndex" class="bg-orange-50 rounded-lg p-3">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <p class="font-medium text-gray-800">{{ service.custom_name || service.master_service?.name || service.masterService?.name || 'Service' }}</p>
                                            <p class="text-sm text-gray-600 mt-1">{{ getServiceDetails(service) }}</p>
                                            <p v-if="service.remarks || service.master_service?.remarks || service.masterService?.remarks" class="text-xs text-gray-500 mt-1">
                                                {{ service.remarks || service.master_service?.remarks || service.masterService?.remarks }}
                                            </p>
                                        </div>
                                        <p class="font-bold text-gray-900 ml-4">{{ formatCurrency(service.amount || 0) }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Total -->
                            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between">
                                <span class="font-medium text-gray-700">Room Total</span>
                                <span class="font-bold text-gray-900">
                                    {{ formatCurrency((room.items?.reduce((sum, i) => sum + Number(i.amount || 0), 0) || 0) + (room.services?.reduce((sum, s) => sum + Number(s.amount || 0), 0) || 0)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="px-6 py-8 text-center text-gray-500">
                    No items added yet
                </div>
            </div>

            <!-- Notes / Exclusions -->
            <div v-if="displayNotes" class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">📝 Notes / Exclusions</h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ displayNotes }}</p>
                </div>
            </div>

            <!-- Payment Stages -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">💰 Payment Stages</h2>
                </div>

                <div class="p-6">
                    <!-- Booking (40%) -->
                    <div class="flex items-center justify-between p-4 rounded-lg mb-3" :class="isBookingPaid ? 'bg-green-50 border border-green-200' : 'bg-gray-50'">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ getMilestoneIcon('booking') }}</span>
                            <div>
                                <p class="font-medium text-gray-900">Booking (40%)</p>
                                <p class="text-sm text-gray-500">{{ formatCurrency(bookingAmount) }} + {{ bookingGst }} GST = {{ bookingTotal }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium" :class="isBookingPaid ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                            {{ getMilestoneStatus('booking') }}
                        </span>
                    </div>

                    <!-- Mid (40%) -->
                    <div class="flex items-center justify-between p-4 rounded-lg mb-3" :class="isMidPaid ? 'bg-green-50 border border-green-200' : 'bg-gray-50'">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ getMilestoneIcon('mid') }}</span>
                            <div>
                                <p class="font-medium text-gray-900">Mid Payment (40%)</p>
                                <p class="text-sm text-gray-500">{{ formatCurrency(midAmount) }} + {{ midGst }} GST = {{ midTotal }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium" :class="isMidPaid ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                            {{ getMilestoneStatus('mid') }}
                        </span>
                    </div>

                    <!-- Final (20%) -->
                    <div class="flex items-center justify-between p-4 rounded-lg" :class="isFinalPaid ? 'bg-green-50 border border-green-200' : 'bg-gray-50'">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ getMilestoneIcon('final') }}</span>
                            <div>
                                <p class="font-medium text-gray-900">Final Payment (20%)</p>
                                <p class="text-sm text-gray-500">{{ formatCurrency(finalAmount) }} + {{ finalGst }} GST = {{ finalTotal }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-sm font-medium" :class="isFinalPaid ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                            {{ getMilestoneStatus('final') }}
                        </span>
                    </div>
                </div>
            </div>


            <!-- Payment CTA (only for logged in customers, not admin view) -->
            <div v-if="isLoggedIn && !isAdminView && !isFinalPaid" class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">📝 Proceed to Payment</h2>
                <p class="text-gray-600 mb-4">Click below to proceed with payment for your booking.</p>

                <div class="space-y-3">
                    <a
                        v-if="!isBookingPaid"
                        :href="getPaymentUrl('booking')"
                        class="block w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg text-center"
                    >
                        Pay Booking (40%): {{ bookingTotal }}
                    </a>

                    <a
                        v-if="isBookingPaid && !isMidPaid"
                        :href="getPaymentUrl('mid')"
                        class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-center"
                    >
                        Pay Mid (40%): {{ midTotal }}
                    </a>

                    <a
                        v-if="isMidPaid && !isFinalPaid"
                        :href="getPaymentUrl('final')"
                        class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded-lg text-center"
                    >
                        Pay Final (20%): {{ finalTotal }}
                    </a>

                    <div v-if="isFinalPaid" class="p-4 bg-green-50 rounded-lg text-center">
                        <p class="text-green-700 font-medium">✅ All payments completed!</p>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="mt-6 p-4 bg-gray-100 rounded-lg">
                <h3 class="font-semibold text-gray-700 mb-2">Terms & Conditions</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Quote is valid for 30 days from the date of issue</li>
                    <li>• Work will commence only after booking amount is paid</li>
                    <li>• GST will be added at the time of payment as per government regulations</li>
                    <li>• Any additional work not in the quote will be charged extra</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    /* Hide navigation and action buttons when printing */
    .no-print {
        display: none !important;
    }

    /* Ensure proper page breaks */
    .page-break {
        page-break-after: always;
    }

    /* Optimize colors for printing */
    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
