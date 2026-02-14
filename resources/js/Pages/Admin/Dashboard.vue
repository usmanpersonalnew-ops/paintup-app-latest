<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps({
    totalRevenue: {
        type: Number,
        default: 0
    },
    pendingBalance: {
        type: Number,
        default: 0
    },
    cashCollected: {
        type: Number,
        default: 0
    },
    onlineCollected: {
        type: Number,
        default: 0
    },
    supervisorCollection: {
        type: Array,
        default: () => []
    }
});

const from = ref(null);
const to = ref(null);

const applyFilter = () => {
    const form = useForm({
        from: from.value,
        to: to.value
    });
    form.get('/admin/dashboard');
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(value || 0);
};
</script>

<template>
    <AdminLayout title="Admin Dashboard">
        <div class="space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Financial Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500">Revenue and collection overview</p>
            </div>

            <!-- TOP STATS - Row 1 -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Total Revenue -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(totalRevenue) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Balance -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Balance</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(pendingBalance) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TOP STATS - Row 2 -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <!-- Cash Collected -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Cash Collected</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(cashCollected) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Online Collected -->
                <div class="rounded-lg bg-white p-6 shadow">
                    <div class="flex items-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Online Collected</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(onlineCollected) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SUPERVISOR TABLE -->
            <div class="rounded-lg bg-white shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-lg font-semibold text-gray-900">Supervisor-wise Collection</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Supervisor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Projects
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Collected
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Pending
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="row in supervisorCollection" :key="row.supervisor">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ row.supervisor }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm text-gray-900">{{ row.total_projects }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-green-600">{{ formatCurrency(row.collected_amount) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="text-sm font-medium text-red-600">{{ formatCurrency(row.pending_amount) }}</div>
                                </td>
                            </tr>
                            <tr v-if="supervisorCollection.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No supervisor data available
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DATE FILTER -->
            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="mb-4 text-lg font-medium text-gray-900">Filter by Date</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div class="min-w-0 flex-1">
                        <label for="from" class="mb-1 block text-sm font-medium text-gray-700">From Date</label>
                        <input
                            type="date"
                            id="from"
                            v-model="from"
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div class="min-w-0 flex-1">
                        <label for="to" class="mb-1 block text-sm font-medium text-gray-700">To Date</label>
                        <input
                            type="date"
                            id="to"
                            v-model="to"
                            class="block w-full rounded-lg border border-gray-300 px-4 py-2.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                        />
                    </div>
                    <div>
                        <button
                            @click="applyFilter"
                            class="inline-flex items-center rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Filter
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>