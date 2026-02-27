<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { ref } from 'vue';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    role: 'SUPERVISOR',
    password: '',
    password_confirmation: '',
    send_credentials: true,
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

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
        <div class="mx-auto max-w-2xl px-4 py-6">
            <div class="mb-6">
                <Link href="/admin/users" class="text-blue-600 hover:text-blue-900 text-sm font-medium inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Users
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

                    <!-- Password -->
                    <div>
                        <InputLabel for="password" value="Password *" />
                        <div class="relative">
                            <TextInput
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="mt-1 block w-full h-12 pr-10"
                                required
                                placeholder="Enter password"
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

                    <!-- Confirm Password -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password *" />
                        <div class="relative">
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                class="mt-1 block w-full h-12 pr-10"
                                required
                                placeholder="Confirm password"
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

                    <!-- Password Strength Meter (Optional) -->
                    <div v-if="form.password" class="mt-1">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div 
                                    class="h-full transition-all duration-300"
                                    :class="{
                                        'bg-red-500 w-1/4': form.password.length < 6,
                                        'bg-yellow-500 w-2/4': form.password.length >= 6 && form.password.length < 8,
                                        'bg-green-500 w-3/4': form.password.length >= 8 && form.password.length < 10,
                                        'bg-blue-500 w-full': form.password.length >= 10
                                    }"
                                ></div>
                            </div>
                            <span class="text-xs text-gray-500">
                                {{ form.password.length < 6 ? 'Weak' : 
                                   form.password.length < 8 ? 'Fair' : 
                                   form.password.length < 10 ? 'Good' : 'Strong' }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Password must be at least 8 characters</p>
                    </div>

                    <!-- Send Credentials -->
                    <div class="flex items-start">
                        <div class="flex h-5 items-center">
                            <Checkbox id="send_credentials" v-model:checked="form.send_credentials" />
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="send_credentials" class="font-medium text-gray-700">Send login credentials via email</label>
                            <p class="text-gray-500">Login credentials will be sent to the user's email address.</p>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="rounded-lg bg-blue-50 p-4 border border-blue-200">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-blue-800">Password Requirements</p>
                                <p class="text-sm text-blue-600">Password must be at least 8 characters and will be encrypted before saving.</p>
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