<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const page = usePage();
const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
    project: {
        type: Object,
        required: true,
    },
    invoice: {
        type: Object,
        default: null,
    },
    eligible: {
        type: Boolean,
        default: false,
    },
    message: {
        type: String,
        default: '',
    },
});

const formatCurrency = (value) => {
    const numValue = Number(value);
    if (isNaN(numValue) || numValue === 0) {
        return '₹0';
    }
    return '₹' + numValue.toLocaleString('en-IN');
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <CustomerLayout>
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800">GST Invoice</h1>
            <p class="text-gray-500 text-sm">{{ project.client_name }} - {{ project.location }}</p>
        </div>

        <!-- Not Eligible State -->
        <div v-if="!eligible" class="bg-white rounded-lg shadow p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-800 mb-2">Invoice Not Available</h3>
            <p class="text-gray-500">{{ message || 'Your GST invoice will be available after final payment is completed.' }}</p>
        </div>

        <!-- Invoice Available -->
        <div v-else class="space-y-4">
            <!-- Success Banner -->
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <p class="font-medium">Your GST Invoice is ready!</p>
                <p class="text-sm">Invoice Number: {{ invoice?.invoice_number || 'N/A' }}</p>
            </div>

            <!-- Invoice Details -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="bg-gray-100 px-4 py-3 border-b flex justify-between items-center">
                    <h2 class="font-semibold text-gray-800">Invoice Details</h2>
                    <a
                        v-if="invoice?.download_url"
                        :href="invoice.download_url"
                        target="_blank"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg text-sm"
                    >
                        Download PDF
                    </a>
                </div>
                <div class="p-4">
                    <!-- Company Info -->
                    <div class="mb-6 pb-4 border-b">
                        <h3 class="font-semibold text-gray-800 mb-2">PaintUp (Revamp Homes)</h3>
                        <p class="text-sm text-gray-600">Professional Painting Services</p>
                        <p class="text-sm text-gray-600">Mumbai, Maharashtra</p>
                        <p class="text-sm text-gray-600">GSTIN: 27XXXXXXXXXX</p>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-6 pb-4 border-b">
                        <h3 class="font-semibold text-gray-800 mb-2">Bill To</h3>
                        <p class="text-sm text-gray-800 font-medium">{{ project.client_name }}</p>
                        <p class="text-sm text-gray-600">{{ project.location }}</p>
                        <p class="text-sm text-gray-600">Phone: {{ project.phone }}</p>
                    </div>

                    <!-- Invoice Meta -->
                    <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Invoice Number</p>
                            <p class="text-sm font-medium text-gray-800">{{ invoice?.invoice_number || 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Date</p>
                            <p class="text-sm font-medium text-gray-800">{{ invoice?.generated_at || formatDate(new Date()) }}</p>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="mb-4">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 text-xs font-medium text-gray-500 uppercase">Description</th>
                                    <th class="text-right py-2 text-xs font-medium text-gray-500 uppercase">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2 text-sm text-gray-600">Painting Work (Net of Discount)</td>
                                    <td class="py-2 text-sm text-right text-gray-800">{{ formatCurrency(invoice?.base_amount || project.base_total) }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2 text-sm text-gray-600">GST ({{ invoice?.gst_rate || 18 }}%)</td>
                                    <td class="py-2 text-sm text-right text-gray-800">{{ formatCurrency(invoice?.gst_amount || 0) }}</td>
                                </tr>
                                <tr>
                                    <td class="py-3 text-sm font-bold text-gray-800">Total Amount</td>
                                    <td class="py-3 text-sm font-bold text-right text-blue-600">{{ formatCurrency(invoice?.total_amount || project.total_amount) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>