<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('admin.profile.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            // Logout admin after successful password change
            window.location.href = route('logout');
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section>
        <form @submit.prevent="updatePassword" class="space-y-5">
            <div>
                <InputLabel for="current_password" value="Current Password" class="text-gray-700 font-medium" />

                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-2 block w-full h-12 text-base"
                    autocomplete="current-password"
                    placeholder="Enter current password"
                />

                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div>
                <InputLabel for="password" value="New Password" class="text-gray-700 font-medium" />

                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-2 block w-full h-12 text-base"
                    autocomplete="new-password"
                    placeholder="Enter new password"
                />

                <p class="mt-1 text-xs text-gray-500">Minimum 8 characters with letters and numbers</p>
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm New Password"
                    class="text-gray-700 font-medium"
                />

                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-2 block w-full h-12 text-base"
                    autocomplete="new-password"
                    placeholder="Confirm new password"
                />

                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <div class="pt-2">
                <PrimaryButton :disabled="form.processing" class="w-full h-12 text-base font-medium bg-blue-600 hover:bg-blue-700">
                    <span v-if="form.processing">Updating...</span>
                    <span v-else>Update Password</span>
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-green-600 mt-2 text-center font-medium"
                    >
                        ✓ Password updated successfully. Redirecting to login...
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
