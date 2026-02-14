<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Admin Profile" />

    <AdminLayout>
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-gray-900">Admin Profile</h1>
                    <p class="text-sm text-gray-500 mt-1">Manage your account settings and preferences</p>
                </div>
                
                <div class="space-y-6">
                    <!-- Profile Information Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Profile Information
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Update your account details and contact information
                            </p>
                        </div>
                        <div class="p-6">
                            <UpdateProfileInformationForm
                                :must-verify-email="mustVerifyEmail"
                                :status="status"
                            />
                        </div>
                    </div>

                    <!-- Update Password Section -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Update Password
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Ensure your account stays secure with a strong password
                            </p>
                        </div>
                        <div class="p-6">
                            <UpdatePasswordForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
