<script setup>
import { computed } from 'vue';
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
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-6 px-4">
        <div class="max-w-4xl mx-auto" v-if="project">
            <!-- Back Link -->
            <div class="mb-4">
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
            </div>

            <!-- Company Header with Logo -->
            <div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
                <!-- Header Section with Logo -->
                <div class="p-6" :style="{ backgroundColor: primaryColor + '10', borderBottom: '2px solid ' + primaryColor }">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Logo -->
                            <div class="flex-shrink-0">
                                <img
                                    v-if="logoUrl"
                                    :src="logoUrl"
                                    :alt="companyName"
                                    class="h-16 w-auto object-contain"
                                />
                                <ApplicationLogo v-else class="h-16 w-auto fill-current" :style="{ color: primaryColor }" />
                            </div>
                            <!-- Company Name and Info -->
                            <div>
                                <h1 class="text-2xl font-bold" :style="{ color: primaryColor }">{{ companyName }}</h1>
                                <p v-if="address" class="text-sm text-gray-600 mt-1">{{ address }}</p>
                                <div v-if="supportEmail || supportWhatsapp" class="flex gap-4 mt-2 text-xs text-gray-600">
                                    <span v-if="supportEmail">📧 {{ supportEmail }}</span>
                                    <span v-if="supportWhatsapp">📱 {{ supportWhatsapp }}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Quote Badge -->
                        <div class="text-right">
                            <div class="inline-block px-4 py-2 rounded-lg" :style="{ backgroundColor: primaryColor, color: 'white' }">
                                <p class="text-sm font-medium">QUOTE</p>
                            </div>
                        </div>
                    </div>
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
                        <div v-if="gstNumber">
                            <p class="text-sm text-gray-500">GST Number</p>
                            <p class="font-medium text-gray-900">{{ gstNumber }}</p>
                        </div>
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
                                            <p class="font-medium text-gray-800">{{ service.custom_name || service.masterService?.name || 'Service' }}</p>
                                            <p class="text-sm text-gray-600 mt-1">{{ getServiceDetails(service) }}</p>
                                            <p v-if="service.masterService?.remarks" class="text-xs text-gray-500 mt-1">
                                                {{ service.masterService.remarks }}
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

            <!-- Notes / Exclusions -->
            <div v-if="displayNotes" class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">📝 Notes / Exclusions</h2>
                </div>
                <div class="px-6 py-4">
                    <p class="text-gray-700 whitespace-pre-wrap">{{ displayNotes }}</p>
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
