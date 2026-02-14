<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
});

const showDeleteModal = ref(false);
const showPasswordResetModal = ref(false);
const showStatusModal = ref(false);
const selectedUser = ref(null);
const modalAction = ref('');

const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const openPasswordResetModal = (user) => {
    selectedUser.value = user;
    showPasswordResetModal.value = true;
};

const openStatusModal = (user, action) => {
    selectedUser.value = user;
    modalAction.value = action;
    showStatusModal.value = true;
};

const deleteUser = () => {
    if (selectedUser.value) {
        useForm({}).delete(`/admin/users/${selectedUser.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                selectedUser.value = null;
            },
        });
    }
};

const resetPassword = () => {
    if (selectedUser.value) {
        useForm({}).post(`/admin/users/${selectedUser.value.id}/reset-password`, {
            onSuccess: () => {
                showPasswordResetModal.value = false;
                selectedUser.value = null;
            },
        });
    }
};

const toggleStatus = () => {
    if (selectedUser.value) {
        const url = modalAction.value === 'activate'
            ? `/admin/users/${selectedUser.value.id}/activate`
            : `/admin/users/${selectedUser.value.id}/deactivate`;
        useForm({}).post(url, {
            onSuccess: () => {
                showStatusModal.value = false;
                selectedUser.value = null;
            },
        });
    }
};
</script>

<template>
    <AdminLayout>
        <div class="mx-auto max-w-7xl">
            <!-- Page Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Staff / Users</h1>
                    <p class="mt-1 text-sm text-gray-500">Manage admin and supervisor accounts</p>
                </div>
                <Link
                    href="/admin/users/create"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-white text-sm font-medium hover:bg-blue-700"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add User
                </Link>
            </div>

            <!-- Toast Notifications -->
            <div v-if="flash.success" class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm text-green-700">{{ flash.success }}</p>
                </div>
            </div>

            <div v-if="flash.error" class="mb-4 rounded-lg bg-red-50 p-4 border border-red-200">
                <div class="flex items-center gap-3">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="text-sm text-red-700">{{ flash.error }}</p>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Phone
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Last Login
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Created
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <!-- Empty State -->
                        <tr v-if="users.length === 0">
                            <td colspan="8" class="px-6 py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating a new user.</p>
                                <div class="mt-6">
                                    <Link
                                        href="/admin/users/create"
                                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add User
                                    </Link>
                                </div>
                            </td>
                        </tr>

                        <!-- User Rows -->
                        <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">{{ user.email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">{{ user.phone }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-5"
                                    :class="{
                                        'bg-blue-100 text-blue-800': user.role === 'ADMIN',
                                        'bg-orange-100 text-orange-800': user.role === 'SUPERVISOR',
                                    }"
                                >
                                    {{ user.role }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold leading-5"
                                    :class="{
                                        'bg-green-100 text-green-800': user.status === 'ACTIVE',
                                        'bg-gray-100 text-gray-800': user.status === 'INACTIVE',
                                    }"
                                >
                                    {{ user.status }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">{{ formatDate(user.last_login_at) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="text-sm text-gray-500">{{ formatDate(user.created_at) }}</div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <!-- Edit -->
                                    <Link
                                        :href="`/admin/users/${user.id}/edit`"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>

                                    <!-- Activate/Deactivate -->
                                    <button
                                        v-if="user.status === 'ACTIVE'"
                                        @click="openStatusModal(user, 'deactivate')"
                                        class="text-gray-600 hover:text-gray-900"
                                        title="Deactivate"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button
                                        v-else
                                        @click="openStatusModal(user, 'activate')"
                                        class="text-green-600 hover:text-green-900"
                                        title="Activate"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>

                                    <!-- Reset Password -->
                                    <button
                                        @click="openPasswordResetModal(user)"
                                        class="text-orange-600 hover:text-orange-900"
                                        title="Reset Password"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                    </button>

                                    <!-- Delete (only for non-admins) -->
                                    <button
                                        v-if="user.role !== 'ADMIN'"
                                        @click="openDeleteModal(user)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete"
                                    >
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" maxWidth="sm">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Delete User</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteUser" :disabled="false">Delete</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Password Reset Confirmation Modal -->
        <Modal :show="showPasswordResetModal" @close="showPasswordResetModal = false" maxWidth="sm">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Reset Password</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Are you sure you want to reset the password for <strong>{{ selectedUser?.name }}</strong>? A new temporary password will be sent to their email.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showPasswordResetModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="resetPassword" :disabled="false">Reset Password</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Status Change Modal -->
        <Modal :show="showStatusModal" @close="showStatusModal = false" maxWidth="sm">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ modalAction === 'activate' ? 'Activate User' : 'Deactivate User' }}
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Are you sure you want to {{ modalAction }} <strong>{{ selectedUser?.name }}</strong>?
                    <span v-if="modalAction === 'deactivate'" class="block mt-2">
                        The user will no longer be able to log in.
                    </span>
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showStatusModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="toggleStatus" :disabled="false">
                        {{ modalAction === 'activate' ? 'Activate' : 'Deactivate' }}
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
