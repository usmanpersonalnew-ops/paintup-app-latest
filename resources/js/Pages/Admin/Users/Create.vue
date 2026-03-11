<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'SUPERVISOR',
    send_credentials: true,
});

const submit = () => {
    form.post('/admin/users', {
        onSuccess: () => {
            // Success message handled by flash in Index
        },
    });
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-2xl">
            <div class="mb-6">
                <Link href="/admin/users" class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                    ← Back to Users
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">Add New User</h1>
                <p class="mt-1 text-sm text-gray-500">Create a new admin or supervisor account</p>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name -->
                    <div>
                        <InputLabel for="name" value="Full Name *" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full h-12"
                            required
                            placeholder="Enter full name"
                        />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <!-- Email -->
                    <div>
                        <InputLabel for="email" value="Email Address *" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full h-12"
                            required
                            placeholder="Enter email address"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <!-- Phone -->
                    <div>
                        <InputLabel for="phone" value="Phone Number *" />
                        <TextInput
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            class="mt-1 block w-full h-12"
                            required
                            placeholder="Enter phone number"
                        />
                        <InputError :message="form.errors.phone" class="mt-2" />
                    </div>

                    <!-- Role -->
                    <div>
                        <InputLabel for="role" value="Role *" />
                        <select
                            id="role"
                            v-model="form.role"
                            class="mt-1 block w-full h-12 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base"
                        >
                            <option value="ADMIN">ADMIN - Full system access</option>
                            <option value="SUPERVISOR">SUPERVISOR - Field agent access</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Admins have full access to all features. Supervisors can only manage assigned projects.
                        </p>
                        <InputError :message="form.errors.role" class="mt-2" />
                    </div>

                    <!-- Send Credentials -->
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <Checkbox id="send_credentials" v-model:checked="form.send_credentials" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="send_credentials" class="font-medium text-gray-700">Send login credentials via email</label>
                            <p class="text-gray-500">A temporary password will be generated and sent to the user's email address.</p>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="rounded-lg bg-blue-50 p-4 border border-blue-200">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800">Auto-generated Password</p>
                                <p class="text-sm text-blue-600">A secure temporary password will be automatically generated and sent to the user if email notification is enabled.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end gap-3 pt-4">
                        <Link
                            href="/admin/users"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 h-12"
                        >
                            Cancel
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="h-12 px-6">
                            <span v-if="form.processing">Creating...</span>
                            <span v-else>Create User</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>