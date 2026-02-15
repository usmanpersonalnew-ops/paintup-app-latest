<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    payments: {
        type: Array,
        default: () => [],
    },
    milestoneAmounts: {
        type: Object,
        required: true,
    },
});

const showAddPaymentModal = ref(false);

const form = useForm({
    milestone_name: 'booking',
    base_amount: '',
    gst_amount: '',
    total_amount: '',
    payment_method: 'CASH',
    payment_status: 'PAID',
    payment_reference: '',
    tracking_id: '',
    bank_ref_no: '',
    paid_at: new Date().toISOString().split('T')[0],
    // Billing details
    buying_type: 'INDIVIDUAL',
    gstin: '',
    business_name: '',
    business_address: '',
    state: '',
    pincode: '',
});

const showBillingDetails = ref({});
const gstinError = ref('');

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

// Validate GSTIN
const validateGstin = () => {
    gstinError.value = '';
    if (form.buying_type === 'BUSINESS' && form.gstin) {
        const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
        if (!gstinRegex.test(form.gstin)) {
            gstinError.value = 'Invalid GSTIN format. Expected format: 22AAAAA0000A1Z5';
            return false;
        }
    }
    return true;
};

const toggleBillingDetails = (paymentId) => {
    showBillingDetails.value[paymentId] = !showBillingDetails.value[paymentId];
};

// Watch base_amount and calculate GST and total
const calculateAmounts = () => {
    if (form.base_amount) {
        const base = parseFloat(form.base_amount) || 0;
        const gstRate = props.project.gst_rate || 18;
        const gst = round(base * gstRate / 100, 2);
        const total = round(base * (1 + gstRate / 100), 2);

        form.gst_amount = gst.toFixed(2);
        form.total_amount = total.toFixed(2);
    }
};

// Watch milestone_name to auto-fill amounts
const updateMilestoneAmounts = () => {
    const milestone = form.milestone_name;
    if (milestone && props.milestoneAmounts[milestone]) {
        const amounts = props.milestoneAmounts[milestone];
        form.base_amount = amounts.base.toFixed(2);
        form.gst_amount = amounts.gst.toFixed(2);
        form.total_amount = amounts.total.toFixed(2);
    }
};

const round = (value, decimals = 2) => {
    return Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals);
};

const formatCurrency = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue) || numValue === 0) {
        return '₹0';
    }
    return '₹' + numValue.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const getStatusColor = (status) => {
    const colors = {
        'PENDING': 'bg-yellow-100 text-yellow-800',
        'PAID': 'bg-green-100 text-green-800',
        'AWAITING_CONFIRMATION': 'bg-orange-100 text-orange-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

const getMethodLabel = (method) => {
    if (!method) return '-';
    const labels = {
        'ONLINE': 'Online',
        'CASH': 'Cash',
        'UPI': 'UPI',
        'CARD': 'Card',
        'NETBANKING': 'Net Banking',
        'WALLET': 'Wallet',
    };
    return labels[method] || method;
};

const getMilestoneLabel = (milestone) => {
    const labels = {
        'booking': 'Booking (40%)',
        'mid': 'Mid Payment (40%)',
        'final': 'Final Payment (20%)',
    };
    return labels[milestone] || milestone;
};

const openAddPaymentModal = () => {
    form.reset();
    form.milestone_name = 'booking';
    form.payment_method = 'CASH';
    form.payment_status = 'PAID';
    form.paid_at = new Date().toISOString().split('T')[0];
    form.buying_type = 'INDIVIDUAL';
    form.gstin = '';
    form.business_name = '';
    form.business_address = '';
    form.state = '';
    form.pincode = '';
    gstinError.value = '';
    updateMilestoneAmounts();
    showAddPaymentModal.value = true;
};

const closeAddPaymentModal = () => {
    showAddPaymentModal.value = false;
    form.reset();
};

const submitPayment = () => {
    if (!validateGstin()) {
        return;
    }
    form.post(route('admin.projects.add-payment', props.project.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAddPaymentModal();
        },
    });
};
</script>

<template>
    <AdminLayout>
        <!-- Header -->
        <div class="mb-6">
            <Link
                :href="route('admin.projects.show', project.id)"
                class="text-sm text-blue-600 hover:text-blue-800 mb-4 inline-block"
            >
                ← Back to Project
            </Link>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Payment History</h1>
                    <p class="text-gray-500 text-sm mt-1">{{ project.client_name }} - Project #{{ project.id }}</p>
                </div>
                <PrimaryButton @click="openAddPaymentModal" class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Payment
                </PrimaryButton>
            </div>
        </div>

        <!-- Success Message -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
            {{ $page.props.flash.success }}
        </div>

        <!-- Payments Table -->
        <div v-if="payments && payments.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Milestone
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Base Amount
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                GST
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Method
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaction ID
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Billing Details
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template v-for="(payment, index) in payments" :key="index">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ formatDate(payment.paid_at) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ getMilestoneLabel(payment.milestone_name) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                    {{ formatCurrency(payment.base_amount) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600">
                                    {{ formatCurrency(payment.gst_amount) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium text-gray-800">
                                    {{ formatCurrency(payment.total_amount) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ getMethodLabel(payment.payment_method) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 font-mono">
                                    {{ payment.transaction_id }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusColor(payment.payment_status)]">
                                        {{ payment.payment_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <button
                                        v-if="payment.billing_details"
                                        @click="toggleBillingDetails(payment.id)"
                                        class="text-blue-600 hover:text-blue-800 text-sm"
                                    >
                                        {{ showBillingDetails[payment.id] ? 'Hide' : 'View' }}
                                    </button>
                                    <span v-else class="text-gray-400 text-sm">-</span>
                                </td>
                            </tr>
                            <!-- Billing Details Row -->
                            <tr v-if="payment.billing_details && showBillingDetails[payment.id]" class="bg-gray-50">
                                <td colspan="9" class="px-4 py-4">
                                    <div class="space-y-4">
                                        <!-- Billing Details -->
                                        <div v-if="payment.billing_details">
                                            <h4 class="font-semibold text-gray-800 mb-2">Billing Information</h4>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="font-medium text-gray-700">Buying Type:</span>
                                                    <span class="ml-2 text-gray-600">{{ payment.billing_details.buying_type === 'BUSINESS' ? 'Business' : 'Individual' }}</span>
                                                </div>
                                                <div v-if="payment.billing_details.gstin">
                                                    <span class="font-medium text-gray-700">GSTIN:</span>
                                                    <span class="ml-2 text-gray-600 font-mono">{{ payment.billing_details.gstin }}</span>
                                                </div>
                                                <div v-if="payment.billing_details.business_name" class="col-span-2">
                                                    <span class="font-medium text-gray-700">Business Name:</span>
                                                    <span class="ml-2 text-gray-600">{{ payment.billing_details.business_name }}</span>
                                                </div>
                                                <div v-if="payment.billing_details.business_address" class="col-span-2">
                                                    <span class="font-medium text-gray-700">Business Address:</span>
                                                    <span class="ml-2 text-gray-600">{{ payment.billing_details.business_address }}</span>
                                                </div>
                                                <div v-if="payment.billing_details.state">
                                                    <span class="font-medium text-gray-700">State:</span>
                                                    <span class="ml-2 text-gray-600">{{ payment.billing_details.state }}</span>
                                                </div>
                                                <div v-if="payment.billing_details.pincode">
                                                    <span class="font-medium text-gray-700">Pincode:</span>
                                                    <span class="ml-2 text-gray-600">{{ payment.billing_details.pincode }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Cash Confirmation Info -->
                                        <div v-if="payment.cash_confirmed_by_name" class="border-t pt-3 mt-3">
                                            <h4 class="font-semibold text-gray-800 mb-2">Cash Payment Confirmation</h4>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="font-medium text-gray-700">Confirmed By:</span>
                                                    <span class="ml-2 text-gray-600">{{ payment.cash_confirmed_by_name }}</span>
                                                </div>
                                                <div v-if="payment.cash_confirmed_at">
                                                    <span class="font-medium text-gray-700">Confirmed At:</span>
                                                    <span class="ml-2 text-gray-600">{{ formatDate(payment.cash_confirmed_at) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">No Payments Yet</h3>
            <p class="text-gray-500 mb-4">No payment entries have been added for this project.</p>
            <PrimaryButton @click="openAddPaymentModal">
                Add First Payment
            </PrimaryButton>
        </div>

        <!-- Add Payment Modal -->
        <div v-if="showAddPaymentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeAddPaymentModal">
            <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Add Payment Entry</h3>
                    <button @click="closeAddPaymentModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitPayment" class="space-y-4">
                    <!-- Milestone -->
                    <div>
                        <InputLabel for="milestone_name" value="Milestone *" />
                        <select
                            id="milestone_name"
                            v-model="form.milestone_name"
                            @change="updateMilestoneAmounts"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        >
                            <option value="booking">Booking (40%)</option>
                            <option value="mid">Mid Payment (40%)</option>
                            <option value="final">Final Payment (20%)</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.milestone_name" />
                    </div>

                    <!-- Base Amount -->
                    <div>
                        <InputLabel for="base_amount" value="Base Amount *" />
                        <TextInput
                            id="base_amount"
                            v-model="form.base_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            @input="calculateAmounts"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.base_amount" />
                    </div>

                    <!-- GST Amount -->
                    <div>
                        <InputLabel for="gst_amount" value="GST Amount" />
                        <TextInput
                            id="gst_amount"
                            v-model="form.gst_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.gst_amount" />
                    </div>

                    <!-- Total Amount -->
                    <div>
                        <InputLabel for="total_amount" value="Total Amount *" />
                        <TextInput
                            id="total_amount"
                            v-model="form.total_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError class="mt-1" :message="form.errors.total_amount" />
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <InputLabel for="payment_method" value="Payment Method *" />
                        <select
                            id="payment_method"
                            v-model="form.payment_method"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                            <option value="CASH">Cash</option>
                            <option value="ONLINE">Online</option>
                            <option value="UPI">UPI</option>
                            <option value="CARD">Card</option>
                            <option value="NETBANKING">Net Banking</option>
                            <option value="WALLET">Wallet</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.payment_method" />
                    </div>

                    <!-- Payment Status -->
                    <div>
                        <InputLabel for="payment_status" value="Payment Status *" />
                        <select
                            id="payment_status"
                            v-model="form.payment_status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                            <option value="PAID">Paid</option>
                            <option value="PENDING">Pending</option>
                            <option value="AWAITING_CONFIRMATION">Awaiting Confirmation</option>
                        </select>
                        <InputError class="mt-1" :message="form.errors.payment_status" />
                    </div>

                    <!-- Payment Reference -->
                    <div>
                        <InputLabel for="payment_reference" value="Payment Reference" />
                        <TextInput
                            id="payment_reference"
                            v-model="form.payment_reference"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.payment_reference" />
                    </div>

                    <!-- Tracking ID -->
                    <div>
                        <InputLabel for="tracking_id" value="Tracking ID" />
                        <TextInput
                            id="tracking_id"
                            v-model="form.tracking_id"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.tracking_id" />
                    </div>

                    <!-- Bank Reference Number -->
                    <div>
                        <InputLabel for="bank_ref_no" value="Bank Reference Number" />
                        <TextInput
                            id="bank_ref_no"
                            v-model="form.bank_ref_no"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.bank_ref_no" />
                    </div>

                    <!-- Paid At -->
                    <div>
                        <InputLabel for="paid_at" value="Payment Date" />
                        <TextInput
                            id="paid_at"
                            v-model="form.paid_at"
                            type="date"
                            class="mt-1 block w-full"
                        />
                        <InputError class="mt-1" :message="form.errors.paid_at" />
                    </div>

                    <!-- Billing Details Section -->
                    <div class="border-t pt-4 mt-4">
                        <h4 class="text-md font-semibold text-gray-800 mb-4">Billing Details</h4>

                        <!-- Buying Type -->
                        <div>
                            <InputLabel for="buying_type" value="Buying Type" />
                            <select
                                id="buying_type"
                                v-model="form.buying_type"
                                @change="validateGstin"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="INDIVIDUAL">Individual - Personal purchase</option>
                                <option value="BUSINESS">Business - Claim GST Input Credit</option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.buying_type" />
                        </div>

                        <!-- GSTIN (only if Business) -->
                        <div v-if="form.buying_type === 'BUSINESS'">
                            <InputLabel for="gstin" value="GSTIN *" />
                            <TextInput
                                id="gstin"
                                v-model="form.gstin"
                                type="text"
                                maxlength="15"
                                placeholder="22AAAAA0000A1Z5"
                                @input="validateGstin"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.gstin" />
                            <p v-if="gstinError" class="mt-1 text-sm text-red-600">{{ gstinError }}</p>
                            <p v-else class="mt-1 text-xs text-gray-500">15-character GSTIN (e.g., 22AAAAA0000A1Z5)</p>
                        </div>

                        <!-- Business Name (only if Business) -->
                        <div v-if="form.buying_type === 'BUSINESS'">
                            <InputLabel for="business_name" value="Business Name *" />
                            <TextInput
                                id="business_name"
                                v-model="form.business_name"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.business_name" />
                        </div>

                        <!-- Business Address (only if Business) -->
                        <div v-if="form.buying_type === 'BUSINESS'">
                            <InputLabel for="business_address" value="Business Address *" />
                            <textarea
                                id="business_address"
                                v-model="form.business_address"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            ></textarea>
                            <InputError class="mt-1" :message="form.errors.business_address" />
                        </div>

                        <!-- State (only if Business) -->
                        <div v-if="form.buying_type === 'BUSINESS'">
                            <InputLabel for="state" value="State *" />
                            <select
                                id="state"
                                v-model="form.state"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">Select State</option>
                                <option v-for="stateOption in indianStates" :key="stateOption" :value="stateOption">
                                    {{ stateOption }}
                                </option>
                            </select>
                            <InputError class="mt-1" :message="form.errors.state" />
                        </div>

                        <!-- Pincode (only if Business) -->
                        <div v-if="form.buying_type === 'BUSINESS'">
                            <InputLabel for="pincode" value="Pincode *" />
                            <TextInput
                                id="pincode"
                                v-model="form.pincode"
                                type="text"
                                maxlength="6"
                                class="mt-1 block w-full"
                            />
                            <InputError class="mt-1" :message="form.errors.pincode" />
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3 pt-4">
                        <SecondaryButton type="button" @click="closeAddPaymentModal">
                            Cancel
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" @click="validateGstin">
                            <span v-if="form.processing">Adding...</span>
                            <span v-else>Add Payment</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>

