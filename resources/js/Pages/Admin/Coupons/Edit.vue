<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    coupon: Object,
    hasUsage: Boolean,
});

const form = useForm({
    code: props.coupon.code,
    type: props.coupon.type,
    value: props.coupon.value,
    min_order_amount: props.coupon.min_order_amount || '',
    expires_at: props.coupon.expires_at ? props.coupon.expires_at.substring(0, 16) : '',
    is_active: props.coupon.is_active,
});

const submit = () => {
    form.put(`/admin/coupons/${props.coupon.id}`, {
        onSuccess: () => {
            // Success handling
        },
    });
};

const formatValue = (coupon) => {
    if (coupon.type === 'FLAT') {
        return 'Flat (Fixed Amount)';
    }
    return 'Percent (Percentage)';
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit Coupon" />

        <div class="p-6 max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <Link href="/admin/coupons" class="text-gray-500 hover:text-gray-700 mr-4">
                Back
            </Link>
            <h1 class="text-2xl font-bold text-gray-800">Edit Coupon</h1>
        </div>

        <!-- Usage Warning -->
        <div v-if="hasUsage" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        This coupon has been used in projects. You can only toggle the active status.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6">
            <!-- Code -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Coupon Code *</label>
                <input
                    v-model="form.code"
                    type="text"
                    placeholder="e.g., PAINTUP500"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase"
                    :disabled="hasUsage"
                    required
                />
                <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</p>
            </div>

            <!-- Type -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type *</label>
                <div class="flex space-x-4">
                    <label class="flex items-center">
                        <input
                            v-model="form.type"
                            type="radio"
                            value="FLAT"
                            :disabled="hasUsage"
                            class="mr-2"
                        />
                        <span class="font-medium">Flat (Fixed Amount)</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            v-model="form.type"
                            type="radio"
                            value="PERCENT"
                            :disabled="hasUsage"
                            class="mr-2"
                        />
                        <span class="font-medium">Percent (Percentage)</span>
                    </label>
                </div>
                <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</p>
            </div>

            <!-- Value -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ form.type === 'FLAT' ? 'Flat Amount (Fixed Amount)' : 'Percent Value (%)' }} *
                </label>
                <input
                    v-model="form.value"
                    type="number"
                    step="0.01"
                    min="0"
                    :max="form.type === 'PERCENT' ? 100 : ''"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :disabled="hasUsage"
                    required
                />
                <p v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}</p>
                <p v-if="form.type === 'PERCENT'" class="text-gray-500 text-xs mt-1">Maximum value is 100%</p>
            </div>

            <!-- Min Order Amount -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Amount (Fixed Amount)</label>
                <input
                    v-model="form.min_order_amount"
                    type="number"
                    placeholder="e.g., 5000 (leave empty for no minimum)"
                    step="0.01"
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :disabled="hasUsage"
                />
                <p v-if="form.errors.min_order_amount" class="text-red-500 text-sm mt-1">{{ form.errors.min_order_amount }}</p>
            </div>

            <!-- Expiry Date -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input
                    v-model="form.expires_at"
                    type="datetime-local"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :disabled="hasUsage"
                />
                <p v-if="form.errors.expires_at" class="text-red-500 text-sm mt-1">{{ form.errors.expires_at }}</p>
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="text-sm font-medium text-gray-700">Active</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <Link
                    href="/admin/coupons"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-medium mr-3 transition-colors"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing || hasUsage"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : 'Update Coupon' }}
                </button>
            </div>
        </form>
    </div>
    </AdminLayout>
</template>