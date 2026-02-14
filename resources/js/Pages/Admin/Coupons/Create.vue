<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const form = useForm({
    code: '',
    type: 'FLAT',
    value: '',
    min_order_amount: '',
    expires_at: '',
    is_active: true,
});

const submit = () => {
    form.post('/admin/coupons', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Create Coupon" />

        <div class="p-6 max-w-2xl mx-auto">
        <div class="flex items-center mb-6">
            <Link href="/admin/coupons" class="text-gray-500 hover:text-gray-700 mr-4">
                ← Back
            </Link>
            <h1 class="text-2xl font-bold text-gray-800">Create Coupon</h1>
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
                    required
                />
                <p v-if="form.errors.code" class="text-red-500 text-sm mt-1">{{ form.errors.code }}</p>
                <p class="text-gray-500 text-xs mt-1">Code will be automatically converted to uppercase</p>
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
                            class="mr-2"
                        />
                        <span class="font-medium">Flat (₹)</span>
                    </label>
                    <label class="flex items-center">
                        <input
                            v-model="form.type"
                            type="radio"
                            value="PERCENT"
                            class="mr-2"
                        />
                        <span class="font-medium">Percent (%)</span>
                    </label>
                </div>
                <p v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</p>
            </div>

            <!-- Value -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ form.type === 'FLAT' ? 'Flat Amount (₹)' : 'Percent Value (%)' }} *
                </label>
                <input
                    v-model="form.value"
                    type="number"
                    :placeholder="form.type === 'FLAT' ? 'e.g., 500' : 'e.g., 10'"
                    step="0.01"
                    min="0"
                    :max="form.type === 'PERCENT' ? 100 : ''"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    required
                />
                <p v-if="form.errors.value" class="text-red-500 text-sm mt-1">{{ form.errors.value }}</p>
                <p v-if="form.type === 'PERCENT'" class="text-gray-500 text-xs mt-1">Maximum value is 100%</p>
            </div>

            <!-- Min Order Amount -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Amount (₹)</label>
                <input
                    v-model="form.min_order_amount"
                    type="number"
                    placeholder="e.g., 5000 (leave empty for no minimum)"
                    step="0.01"
                    min="0"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.min_order_amount" class="text-red-500 text-sm mt-1">{{ form.errors.min_order_amount }}</p>
                <p class="text-gray-500 text-xs mt-">Leave empty for no minimum order requirement</p>
            </div>

            <!-- Expiry Date -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input
                    v-model="form.expires_at"
                    type="datetime-local"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <p v-if="form.errors.expires_at" class="text-red-500 text-sm mt-1">{{ form.errors.expires_at }}</p>
                <p class="text-gray-500 text-xs mt-1">Leave empty for no expiry</p>
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                    <span class="text-sm font-medium text-gray-700">Active (can be used immediately)</span>
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
                    :disabled="form.processing"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors disabled:opacity-50"
                >
                    {{ form.processing ? 'Creating...' : 'Create Coupon' }}
                </button>
            </div>
        </form>
    </div>
    </AdminLayout>
</template>