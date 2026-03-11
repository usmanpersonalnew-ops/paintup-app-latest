<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
});

const submit = () => {
    form.put(`/admin/users/${props.user.id}`, {
        onSuccess: () => {
            // Success message handled by flash
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