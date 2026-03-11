<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
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
    <Head title="Profile" />

    <SupervisorLayout>
        <div class="py-6 px-4 sm:px-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Profile</h1>
            
            <div class="space-y-6 max-w-2xl">
                <!-- Profile Information Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Profile Information
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Update your account details
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
                            Keep your account secure
                        </p>
                    </div>
                    <div class="p-6">
                        <UpdatePasswordForm />
                    </div>
                </div>
            </div>
        </div>
    </SupervisorLayout>
</template>
