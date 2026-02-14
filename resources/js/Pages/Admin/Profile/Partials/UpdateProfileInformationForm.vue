<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const submit = () => {
    form.patch(route('admin.profile.update'), {
        onSuccess: () => {
            // Success handled by flash message
        },
    });
};
</script>

<template>
    <section>
        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <InputLabel for="name" value="Full Name" class="text-gray-700 font-medium" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-2 block w-full h-12 text-base"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Enter your full name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email Address" class="text-gray-700 font-medium" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-2 block w-full h-12 text-base"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="Enter your email address"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-600">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="text-blue-600 underline hover:text-blue-800"
                    >
                        Resend verification
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    Verification link sent!
                </div>
            </div>

            <div class="pt-2">
                <PrimaryButton :disabled="form.processing" class="w-full h-12 text-base font-medium bg-blue-600 hover:bg-blue-700">
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Changes</span>
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
                        ✓ Profile updated successfully
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
