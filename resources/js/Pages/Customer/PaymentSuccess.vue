<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const page = usePage();
const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    milestone: {
        type: String,
        required: true,
    },
    paymentAmount: {
        type: Number,
        required: true,
    },
    paymentMethod: {
        type: String,
        default: 'CASH',
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getMilestoneLabel = (milestone) => {
    const labels = {
        'booking': 'Booking Payment (40%)',
        'mid': 'Mid Payment (40%)',
        'final': 'Final Payment (20%)',
    };
    return labels[milestone] || milestone;
};

const getSuccessMessage = (milestone) => {
    const messages = {
        'booking': 'Your booking payment request has been submitted successfully!',
        'mid': 'Your mid payment request has been submitted successfully!',
        'final': 'Your final payment request has been submitted successfully!',
    };
    return messages[milestone] || 'Your payment request has been submitted successfully!';
};
</script>

<template>
    <CustomerLayout>
        <div class="max-w-2xl mx-auto py-8 px-4">
            <!-- Success Card -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Success Header -->
                <div class="bg-green-50 px-6 py-8 text-center border-b">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">Cash Payment Request Submitted!</h1>
                    <p class="text-gray-600">{{ getSuccessMessage(milestone) }}</p>
                </div>

                <!-- Payment Details -->
                <div class="px-6 py-6">
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Payment Details</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Project:</span>
                                <span class="font-medium text-gray-800">{{ project.client_name }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Milestone:</span>
                                <span class="font-medium text-gray-800">{{ getMilestoneLabel(milestone) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Payment Method:</span>
                                <span class="font-medium text-gray-800">💵 Cash Payment</span>
                            </div>
                            <div class="border-t pt-3 mt-3">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-semibold text-gray-800">Amount:</span>
                                    <span class="text-2xl font-bold text-green-600">{{ formatCurrency(paymentAmount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Information -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-start">
                            <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="font-semibold text-blue-800 mb-2">What's Next?</h3>
                                <ul class="text-sm text-blue-700 space-y-1">
                                    <li>• Your payment request has been recorded</li>
                                    <li>• Our team will collect the cash payment from you</li>
                                    <li>• Once payment is confirmed, your project status will be updated</li>
                                    <li>• You will receive a confirmation once the payment is processed</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3">
                        <Link
                            :href="route('customer.dashboard')"
                            class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg font-medium text-center hover:bg-blue-700 transition"
                        >
                            Go to Dashboard
                        </Link>
                        <Link
                            :href="route('customer.payment.history')"
                            class="flex-1 bg-gray-100 text-gray-700 px-6 py-3 rounded-lg font-medium text-center hover:bg-gray-200 transition"
                        >
                            View Payment History
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>

