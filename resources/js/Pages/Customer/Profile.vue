<script setup>
import { ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const page = usePage();
const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
});

const form = ref({
    name: props.customer.name || '',
    email: props.customer.email || '',
    phone: props.customer.phone || '',
    address: props.customer.address || '',
    city: props.customer.city || '',
    state: props.customer.state || '',
    pincode: props.customer.pincode || '',
});

const errors = ref({});
const processing = ref(false);
const successMessage = ref('');

const validateForm = () => {
    errors.value = {};
    
    if (!form.value.name.trim()) {
        errors.value.name = 'Name is required';
    }
    
    if (form.value.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = 'Please enter a valid email address';
    }
    
    if (form.value.pincode && !/^\d{6}$/.test(form.value.pincode)) {
        errors.value.pincode = 'Pincode must be 6 digits';
    }
    
    return Object.keys(errors.value).length === 0;
};

const submitForm = () => {
    successMessage.value = '';
    
    if (!validateForm()) {
        return;
    }
    
    processing.value = true;
    
    router.patch(route('customer.profile.update'), form.value, {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Profile updated successfully!';
            processing.value = false;
        },
        onError: (err) => {
            errors.value = err;
            processing.value = false;
        },
    });
};
</script>

<template>
    <CustomerLayout>
        <!-- Header -->
        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h1 class="text-xl font-bold text-gray-800">My Profile</h1>
            <p class="text-gray-500 text-sm">Manage your personal information</p>
        </div>

        <!-- Success Message -->
        <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ successMessage }}
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form @submit.prevent="submitForm" class="p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="w-full h-12 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.name }"
                        placeholder="Enter your full name"
                    >
                    <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name }}</p>
                </div>

                <!-- Phone (Read-only) -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        readonly
                        disabled
                        class="w-full h-12 px-4 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                    >
                    <p class="mt-1 text-xs text-gray-500">Phone number cannot be changed</p>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="w-full h-12 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.email }"
                        placeholder="Enter your email"
                    >
                    <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea
                        id="address"
                        v-model="form.address"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter your address"
                    ></textarea>
                </div>

                <!-- City & State -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input
                            id="city"
                            v-model="form.city"
                            type="text"
                            class="w-full h-12 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter city"
                        >
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                        <input
                            id="state"
                            v-model="form.state"
                            type="text"
                            class="w-full h-12 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter state"
                        >
                    </div>
                </div>

                <!-- Pincode -->
                <div>
                    <label for="pincode" class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                    <input
                        id="pincode"
                        v-model="form.pincode"
                        type="text"
                        maxlength="6"
                        class="w-full h-12 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.pincode }"
                        placeholder="Enter 6-digit pincode"
                    >
                    <p v-if="errors.pincode" class="mt-1 text-sm text-red-500">{{ errors.pincode }}</p>
                </div>

                <!-- Submit Button -->
                <div class="pt-4">
                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full h-12 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </CustomerLayout>
</template>