<script setup>
import { ref, computed } from 'vue';
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

const formatCurrencyWithDecimals = (value) => {
    if (value === null || value === undefined || isNaN(value)) {
        value = 0;
    }

    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
};

const milestonePercentage = computed(() => {
    const percentages = {
        booking: 40,
        mid: 40,
        final: 20,
    };
    return percentages[props.milestone?.name] || 0;
});

const milestoneLabel = computed(() => {
    const labels = {
        booking: 'Booking (40%)',
        mid: 'Mid Payment (40%)',
        final: 'Final Payment (20%)',
    };
    return labels[props.milestone?.name] || props.milestone?.name;
});

const baseAmount = computed(() => props.milestone?.base_amount || 0);
const totalAmount = computed(() => props.milestone?.base_amount || 0);

const validateGstin = () => {
    gstinError.value = '';

    if (buyingType.value === 'BUSINESS') {
        if (!gstin.value || gstin.value.trim() === '') {
            gstinError.value = 'GSTIN is required for business purchases';
            return false;
        }

        const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;

        if (!gstinRegex.test(gstin.value)) {
            gstinError.value = 'Invalid GSTIN format';
            return false;
        }
    }

    return true;
};

const isFormValid = computed(() => {
    if (buyingType.value === 'BUSINESS') {
        return validateGstin();
    }
    return true;
});

const saveBillingDetails = async () => {

    if (buyingType.value === 'BUSINESS' && !validateGstin()) return;

    if (buyingType.value === 'INDIVIDUAL') {
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
            processPayment();
        }

    } catch (error) {

        alert(error.response?.data?.message || 'Failed to save billing details');

    } finally {
        isSavingBilling.value = false;
    }
};

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

                window.location.href = response.data.payment_url;

            } else {

                router.visit(route('customer.payment.cash-success', [
                    props.project.id,
                    props.milestone.name
                ]));
            }

        } else {

            alert(response.data.message || 'Payment failed');

        }

    } catch (error) {

        alert(error.response?.data?.message || 'Payment error');

    } finally {

        isProcessing.value = false;

    }
};

</script>

<template>
    <CustomerLayout>

        <div class="max-w-4xl mx-auto">

            <div class="mb-4">
                <a :href="route('customer.dashboard')" class="text-gray-600 hover:text-gray-800">
                    ← Back to Dashboard
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <div class="lg:col-span-2">

                    <div class="bg-white rounded-lg shadow p-6 mb-6">

                        <h2 class="text-xl font-bold mb-4">Order Summary</h2>

                        <div class="bg-gray-50 rounded-lg p-4">

                            <div class="flex justify-between mb-4">
                                <span class="font-medium">{{ milestoneLabel }}</span>
                                <span class="text-sm text-gray-500">{{ milestonePercentage }}%</span>
                            </div>

                            <div class="space-y-3">

                                <div class="flex justify-between">
                                    <span>Amount</span>
                                    <span>{{ formatCurrencyWithDecimals(baseAmount) }}</span>
                                </div>

                                <div class="border-t pt-3 mt-3 flex justify-between text-lg font-bold">
                                    <span>Total Payable</span>
                                    <span class="text-blue-600">{{ formatCurrencyWithDecimals(totalAmount) }}</span>
                                </div>

                            </div>

                        </div>

                    </div>


                    <div class="bg-white rounded-lg shadow p-6 mb-6">

                        <h2 class="text-xl font-bold mb-4">Buying Type</h2>

                        <label class="flex items-center p-4 border rounded-lg cursor-pointer"
                            :class="buyingType === 'INDIVIDUAL' ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">

                            <input type="radio" v-model="buyingType" value="INDIVIDUAL">

                            <div class="ml-3">
                                <span class="font-medium">Buying as Individual</span>
                                <span class="text-sm text-gray-500 block">Personal purchase</span>
                            </div>

                        </label>

                    </div>


                    <div class="bg-white rounded-lg shadow p-6">

                        <h2 class="text-xl font-bold mb-4">Payment Method</h2>

                        <label class="flex items-center p-4 border rounded-lg mb-4 cursor-pointer"
                            :class="paymentMethod === 'ONLINE' ? 'border-green-500 bg-green-50' : 'border-gray-200'">

                            <input type="radio" v-model="paymentMethod" value="ONLINE">

                            <div class="ml-3">
                                <span class="font-medium">Online Payment</span>
                                <span class="text-sm text-gray-500 block">UPI / Cards / Net Banking</span>
                            </div>

                        </label>


                        <label class="flex items-center p-4 border rounded-lg cursor-pointer"
                            :class="paymentMethod === 'CASH' ? 'border-orange-500 bg-orange-50' : 'border-gray-200'">

                            <input type="radio" v-model="paymentMethod" value="CASH">

                            <div class="ml-3">
                                <span class="font-medium">Cash Payment</span>
                                <span class="text-sm text-gray-500 block">Pay at office</span>
                            </div>

                        </label>

                    </div>

                </div>


                <div>

                    <div class="bg-white rounded-lg shadow p-6 sticky top-4">

                        <h3 class="text-lg font-bold mb-4">Payment Summary</h3>

                        <div class="flex justify-between mb-3">
                            <span>Amount</span>
                            <span>{{ formatCurrencyWithDecimals(totalAmount) }}</span>
                        </div>

                        <div class="border-t pt-3 flex justify-between text-xl font-bold">
                            <span>Total</span>
                            <span class="text-blue-600">{{ formatCurrencyWithDecimals(totalAmount) }}</span>
                        </div>

                        <button @click="saveBillingDetails" :disabled="isProcessing || isSavingBilling"
                            class="w-full h-14 mt-6 text-white font-bold rounded-lg" :class="paymentMethod === 'ONLINE'
                                ? 'bg-green-600 hover:bg-green-700'
                                : 'bg-orange-500 hover:bg-orange-600'">

                            <span v-if="isProcessing || isSavingBilling">
                                Processing...
                            </span>

                            <span v-else>
                                Pay {{ formatCurrencyWithDecimals(totalAmount) }}
                            </span>

                        </button>

                        <p class="text-xs text-gray-500 text-center mt-4">
                            Secure payment powered by PaintUp
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </CustomerLayout>
</template>