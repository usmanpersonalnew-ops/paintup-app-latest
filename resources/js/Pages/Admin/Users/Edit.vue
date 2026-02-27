<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
    permissions: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.user.name,
    phone: props.user.phone,
    role: props.user.role,
    status: props.user.status,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);
const changePassword = ref(false);

const submit = () => {
    // If not changing password, remove password fields from submission
    if (!changePassword.value) {
        delete form.password;
        delete form.password_confirmation;
    }
    
    form.put(`/admin/users/${props.user.id}`, {
        onSuccess: () => {
            // Success message handled by flash
            changePassword.value = false;
            form.password = '';
            form.password_confirmation = '';
        },
    });
};

const resetPassword = () => {
    if (confirm('Send password reset link to user\'s email?')) {
        form.post(`/admin/users/${props.user.id}/reset-password`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-2xl px-4 py-6">
            <div class="mb-6">
                <Link href="/admin/users" class="text-blue-600 hover:text-blue-900 text-sm font-medium inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Users
                </Link>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">Edit User</h1>
                <p class="mt-1 text-sm text-gray-500">Update user information and permissions</p>
            </div>

            <div class="overflow-hidden rounded-lg bg-white shadow">
                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Warning for self-edit -->
                    <div v-if="permissions.is_self" class="rounded-lg bg-yellow-50 p-4 border border-yellow-200">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">You are editing your own account</p>
                                <p class="text-sm text-yellow-600">You cannot change your own role or deactivate your account.</p>
                            </div>
                        </div>
                    </div>

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

                    <!-- Email (Read-only) -->
                    <div>
                        <InputLabel for="email" value="Email Address" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full h-12 bg-gray-100"
                            :value="user.email"
                            readonly
                            disabled
                        />
                        <p class="mt-1 text-xs text-gray-500">Email cannot be changed</p>
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
                            :disabled="!permissions.can_edit_role"
                            class="mt-1 block w-full h-12 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base"
                            :class="{ 'bg-gray-100 cursor-not-allowed': !permissions.can_edit_role }"
                        >
                            <option value="ADMIN">ADMIN - Full system access</option>
                            <option value="SUPERVISOR">SUPERVISOR - Field agent access</option>
                        </select>
                        <p v-if="!permissions.can_edit_role" class="mt-1 text-xs text-gray-500">
                            You cannot change your own role.
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500">
                            Admins have full access. Supervisors can only manage assigned projects.
                        </p>
                        <InputError :message="form.errors.role" class="mt-2" />
                    </div>

                    <!-- Status -->
                    <div>
                        <InputLabel for="status" value="Status *" />
                        <select
                            id="status"
                            v-model="form.status"
                            :disabled="!permissions.can_edit_status"
                            class="mt-1 block w-full h-12 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-base"
                            :class="{ 'bg-gray-100 cursor-not-allowed': !permissions.can_edit_status }"
                        >
                            <option value="ACTIVE">ACTIVE - User can log in</option>
                            <option value="INACTIVE">INACTIVE - User cannot log in</option>
                        </select>
                        <p v-if="!permissions.can_edit_status" class="mt-1 text-xs text-gray-500">
                            You cannot deactivate your own account.
                        </p>
                        <p v-else class="mt-1 text-xs text-gray-500">
                            Inactive users cannot log in but their data is preserved.
                        </p>
                        <InputError :message="form.errors.status" class="mt-2" />
                    </div>

                    <!-- Change Password Section -->
                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
                            <button
                                type="button"
                                @click="changePassword = !changePassword"
                                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                            >
                                {{ changePassword ? 'Cancel' : 'Change Password' }}
                            </button>
                        </div>

                        <!-- Password Reset Link Option -->
                        <div v-if="!changePassword" class="mt-2">
                            <button
                                type="button"
                                @click="resetPassword"
                                class="text-sm text-blue-600 hover:text-blue-800"
                            >
                                Send password reset link to user's email
                            </button>
                        </div>

                        <!-- Password Fields -->
                        <div v-if="changePassword" class="mt-4 space-y-4">
                            <!-- New Password -->
                            <div>
                                <InputLabel for="password" value="New Password" />
                                <div class="relative">
                                    <TextInput
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="mt-1 block w-full h-12 pr-10"
                                        placeholder="Enter new password"
                                    />
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg v-if="!showPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <!-- Confirm New Password -->
                            <div>
                                <InputLabel for="password_confirmation" value="Confirm New Password" />
                                <div class="relative">
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showConfirmPassword ? 'text' : 'password'"
                                        class="mt-1 block w-full h-12 pr-10"
                                        placeholder="Confirm new password"
                                    />
                                    <button
                                        type="button"
                                        @click="showConfirmPassword = !showConfirmPassword"
                                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg v-if="!showConfirmPassword" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                        </svg>
                                    </button>
                                </div>
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <!-- Password Requirements -->
                            <div class="text-xs text-gray-500">
                                <p>• Password must be at least 8 characters</p>
                                <p>• Leave blank to keep current password</p>
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
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Changes</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>