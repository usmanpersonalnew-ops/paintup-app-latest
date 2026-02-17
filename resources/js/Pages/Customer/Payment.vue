<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import axios from 'axios';

const page = usePage();
const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    milestone: {
        type: Object,
        required: true,
    },
    billingDetails: {
        type: Object,
        default: null,
    },
});

// State
const buyingType = ref(props.billingDetails?.buying_type || 'INDIVIDUAL');
const gstin = ref(props.billingDetails?.gstin || '');
const businessName = ref(props.billingDetails?.business_name || '');
const businessAddress = ref(props.billingDetails?.business_address || '');
const state = ref(props.billingDetails?.state || '');
const pincode = ref(props.billingDetails?.pincode || '');
const paymentMethod = ref('ONLINE');
const isSavingBilling = ref(false);
const isProcessing = ref(false);
const gstinError = ref('');

// Constants
const GST_RATE = 18;

// Formatters
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const formatCurrencyWithDecimals = (value) => {
    if (value === null || value === undefined || isNaN(value)) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(0);
    }
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

// Computed values
const milestonePercentage = computed(() => {
    const percentages = {
        'booking': 40,
        'mid': 40,
        'final': 20,
    };
    return percentages[props.milestone?.name] || 0;
});

const milestoneLabel = computed(() => {
    const labels = {
        'booking': 'Booking (40%)',
        'mid': 'Mid Payment (40%)',
        'final': 'Final Payment (20%)',
    };
    return labels[props.milestone?.name] || props.milestone?.name;
});

const baseAmount = computed(() => props.milestone?.base_amount || 0);
const gstAmount = computed(() => props.milestone?.gst_amount || 0);
const totalAmount = computed(() => props.milestone?.total_amount || 0);

// Validate GSTIN
const validateGstin = () => {
    gstinError.value = '';
    if (buyingType.value === 'BUSINESS') {
        const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        if (!gstinRegex.test(gstin.value)) {
            gstinError.value = 'Invalid GSTIN format. Expected format: 22AAAAA0000A1Z5';
            return false;
        }
    }
    return true;
};

// Save billing details (only if business type)
const saveBillingDetails = async () => {
    if (buyingType.value === 'BUSINESS' && !validateGstin()) return;

    if (buyingType.value === 'INDIVIDUAL') {
        // For individual, proceed directly to payment
        processPayment();
        return;
    }

    isSavingBilling.value = true;
    try {
        const response = await axios.post(`/customer/project/${props.project.id}/billing-details`, {
            milestone_type: props.milestone.name,
            buying_type: buyingType.value,
            gstin: gstin.value,
            business_name: businessName.value,
            business_address: businessAddress.value,
            state: state.value,
            pincode: pincode.value,
        });

        if (response.data.success) {
            // Continue to payment
            processPayment();
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Failed to save billing details');
    } finally {
        isSavingBilling.value = false;
    }
};

// Process payment
const processPayment = async () => {
    isProcessing.value = true;

    try {
        let endpoint = '';
        switch (props.milestone.name) {
            case 'booking':
                endpoint = paymentMethod.value === 'ONLINE'
                    ? `/customer/project/${props.project.id}/booking/online`
                    : `/customer/project/${props.project.id}/booking/cash`;
                break;
            case 'mid':
                endpoint = `/customer/project/${props.project.id}/mid-payment`;
                break;
            case 'final':
                endpoint = `/customer/project/${props.project.id}/final-payment`;
                break;
        }

        const response = await axios.post(endpoint, {
            payment_method: paymentMethod.value,
            billing_details: {
                buying_type: buyingType.value,
                gstin: buyingType.value === 'BUSINESS' ? gstin.value : null,
                business_name: businessName.value,
                business_address: businessAddress.value,
                state: state.value,
                pincode: pincode.value,
            },
        });

        if (response.data.success) {
            if (paymentMethod.value === 'ONLINE' && response.data.payment_url) {
                // Redirect to payment gateway
                window.location.href = response.data.payment_url;
            } else {
                // Cash payment - redirect to success page
                router.visit(route('customer.payment.cash-success', [props.project.id, props.milestone.name]));
            }
        } else {
            alert(response.data.message || 'Payment failed');
        }
    } catch (error) {
        alert(error.response?.data?.message || 'An error occurred during payment');
    } finally {
        isProcessing.value = false;
    }
};

// Indian states list
const indianStates = [
    'Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar',
    'Chandigarh', 'Chhattisgarh', 'Dadra and Nagar Haveli', 'Daman and Diu', 'Delhi',
    'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu and Kashmir',
    'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep',
    'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram',
    'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan',
    'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
    'Uttarakhand', 'West Bengal'
];
</script>

<template>
    <CustomerLayout>
        <div class="max-w-4xl mx-auto">
            <!-- Back Button -->
            <div class="mb-4">
                <a
                    :href="route('customer.dashboard')"
                    class="inline-flex items-center text-gray-600 hover:text-gray-800"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column: Order Summary -->
                <div class="lg:col-span-2">
                    <!-- Section 1: Order Summary -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">📦 Order Summary</h2>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <!-- Milestone Info -->
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-lg font-medium text-gray-700">{{ milestoneLabel }}</span>
                                <span class="text-sm text-gray-500">{{ milestonePercentage }}% of Total</span>
                            </div>

                            <!-- Amount Breakdown -->
                            <div class="space-y-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">Base Amount</span>
                                    <span class="font-medium">{{ formatCurrencyWithDecimals(baseAmount) }}</span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600">GST @{{ GST_RATE }}%</span>
                                    <span class="font-medium text-orange-600">{{ formatCurrencyWithDecimals(gstAmount) }}</span>
                                </div>

                                <div class="border-t border-gray-200 pt-3 mt-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-xl font-bold text-gray-800">Total Payable</span>
                                        <span class="text-2xl font-bold text-blue-600">{{ formatCurrencyWithDecimals(totalAmount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- GST Note -->
                            <p class="text-xs text-gray-500 mt-4">
                                *GST will be shown in invoice. Amount includes GST.
                            </p>
                        </div>
                    </div>

                    <!-- Section 2: Buying Type -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">🏢 Buying Type</h2>

                        <div class="space-y-4">
                            <!-- Individual Option -->
                            <label
                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                :class="buyingType === 'INDIVIDUAL' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    v-model="buyingType"
                                    value="INDIVIDUAL"
                                    class="w-5 h-5 text-blue-600"
                                >
                                <div class="ml-3">
                                    <span class="block font-medium text-gray-800">Buying as Individual</span>
                                    <span class="block text-sm text-gray-500">Personal purchase</span>
                                </div>
                            </label>

                            <!-- Business Option -->
                            <label
                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                :class="buyingType === 'BUSINESS' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    v-model="buyingType"
                                    value="BUSINESS"
                                    class="w-5 h-5 text-blue-600"
                                >
                                <div class="ml-3">
                                    <span class="block font-medium text-gray-800">Buying as Business</span>
                                    <span class="block text-sm text-gray-500">Claim GST Input Credit</span>
                                </div>
                            </label>
                        </div>

                        <!-- Business Fields (Conditional) -->
                        <div v-if="buyingType === 'BUSINESS'" class="mt-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    GSTIN <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    v-model="gstin"
                                    @blur="validateGstin"
                                    placeholder="22AAAAA0000A1Z5"
                                    maxlength="15"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    :class="{'border-red-500': gstinError}"
                                >
                                <p v-if="gstinError" class="text-red-500 text-sm mt-1">{{ gstinError }}</p>
                                <p class="text-xs text-gray-500 mt-1">15-character GSTIN (e.g., 22AAAAA0000A1Z5)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Name</label>
                                <input
                                    type="text"
                                    v-model="businessName"
                                    placeholder="Company Name"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Business Address</label>
                                <textarea
                                    v-model="businessAddress"
                                    rows="2"
                                    placeholder="Full business address"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                ></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                    <select
                                        v-model="state"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">Select State</option>
                                        <option v-for="s in indianStates" :key="s" :value="s">{{ s }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                    <input
                                        type="text"
                                        v-model="pincode"
                                        placeholder="123456"
                                        maxlength="6"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Payment Method -->
                    <div class="bg-white rounded-lg shadow p-6 mb-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">💳 Select Payment Method</h2>

                        <div class="space-y-4">
                            <!-- Online Payment -->
                            <label
                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                :class="paymentMethod === 'ONLINE' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    v-model="paymentMethod"
                                    value="ONLINE"
                                    class="w-5 h-5 text-green-600"
                                >
                                <div class="ml-3 flex items-center">
                                    <span class="text-2xl mr-3">🟢</span>
                                    <div>
                                        <span class="block font-medium text-gray-800">Online Payment</span>
                                        <span class="block text-sm text-gray-500">Pay via UPI, Cards, Net Banking</span>
                                    </div>
                                </div>
                            </label>

                            <!-- Cash Payment -->
                            <label
                                class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                                :class="paymentMethod === 'CASH' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    v-model="paymentMethod"
                                    value="CASH"
                                    class="w-5 h-5 text-orange-600"
                                >
                                <div class="ml-3 flex items-center">
                                    <span class="text-2xl mr-3">💵</span>
                                    <div>
                                        <span class="block font-medium text-gray-800">Cash Payment</span>
                                        <span class="block text-sm text-gray-500">Pay with cash at office</span>
                                    </div>
                                </div>
                            </label>
                        </div>

                        <!-- Cash Payment Notice -->
                        <div v-if="paymentMethod === 'CASH'" class="mt-4 p-3 bg-orange-50 border border-orange-200 rounded-lg">
                            <p class="text-sm text-orange-800">
                                ⚠️ Your payment will be marked as "Cash Pending". A supervisor must confirm receipt before work begins.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Payment Summary Card -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">💰 Payment Summary</h3>

                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Base Amount</span>
                                <span class="font-medium">{{ formatCurrencyWithDecimals(baseAmount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">GST ({{ GST_RATE }}%)</span>
                                <span class="font-medium text-orange-600">{{ formatCurrencyWithDecimals(gstAmount) }}</span>
                            </div>
                            <div class="border-t border-gray-200 pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-gray-800">Total</span>
                                    <span class="text-2xl font-bold text-blue-600">{{ formatCurrencyWithDecimals(totalAmount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Pay Button -->
                        <button
                            @click="saveBillingDetails"
                            :disabled="isProcessing || isSavingBilling || (buyingType === 'BUSINESS' && !validateGstin())"
                            class="w-full h-14 text-white text-lg font-bold rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            :class="paymentMethod === 'ONLINE' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-orange-500 hover:bg-orange-600 focus:ring-orange-500'"
                        >
                            <span v-if="isProcessing || isSavingBilling">Processing...</span>
                            <span v-else>
                                {{ paymentMethod === 'ONLINE' ? '🟢' : '💵' }}
                                Pay {{ formatCurrencyWithDecimals(totalAmount) }}
                            </span>
                        </button>

                        <!-- Security Note -->
                        <p class="text-xs text-gray-500 text-center mt-4">
                            🔒 Secure payment powered by PaintUp
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>
