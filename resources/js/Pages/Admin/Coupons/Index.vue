<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    coupons: Object,
    flash: {
        type: Object,
        default: () => ({}),
    },
});

const formatDate = (date) => {
    if (!date) return 'No expiry';
    return new Date(date).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const formatValue = (coupon) => {
    if (coupon.type === 'FLAT') {
        return '₹' + parseFloat(coupon.value).toLocaleString('en-IN');
    }
    return coupon.value + '%';
};
</script>

<template>
    <AdminLayout>
        <Head title="Manage Coupons" />

        <div class="p-6">
            <!-- Flash Messages -->
            <div v-if="flash.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                {{ flash.success }}
            </div>
            <div v-if="flash.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                {{ flash.error }}
            </div>
            
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Coupons</h1>
                <Link
                    href="/admin/coupons/create"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                    Create Coupon
                </Link>
            </div>

        <!-- Coupons Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Value
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Min Order
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Expires
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="coupon in coupons.data" :key="coupon.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono font-bold text-blue-600">{{ coupon.code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                    coupon.type === 'FLAT' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800'
                                ]"
                            >
                                {{ coupon.type }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">
                            {{ formatValue(coupon) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ coupon.min_order_amount ? '₹' + parseFloat(coupon.min_order_amount).toLocaleString('en-IN') : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                            {{ formatDate(coupon.expires_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span
                                :class="[
                                    'px-2 py-1 text-xs font-semibold rounded-full',
                                    coupon.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]"
                            >
                                {{ coupon.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                                <Link
                                    :href="`/admin/coupons/${coupon.id}/edit`"
                                    class="text-blue-600 hover:text-blue-900 font-medium text-sm"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="`/admin/coupons/${coupon.id}/toggle`"
                                    method="post"
                                    class="text-gray-600 hover:text-gray-900 font-medium text-sm"
                                >
                                    {{ coupon.is_active ? 'Deactivate' : 'Activate' }}
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="coupons.last_page > 1" class="px-6 py-4 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Showing {{ coupons.from }} to {{ coupons.to }} of {{ coupons.total }} coupons
                    </div>
                    <div class="flex space-x-2">
                        <Link
                            v-if="coupons.current_page > 1"
                            :href="`/admin/coupons?page=${coupons.current_page - 1}`"
                            class="px-3 py-1 border rounded hover:bg-gray-50"
                        >
                            Previous
                        </Link>
                        <Link
                            v-if="coupons.current_page < coupons.last_page"
                            :href="`/admin/coupons?page=${coupons.current_page + 1}`"
                            class="px-3 py-1 border rounded hover:bg-gray-50"
                        >
                            Next
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </AdminLayout>
</template>