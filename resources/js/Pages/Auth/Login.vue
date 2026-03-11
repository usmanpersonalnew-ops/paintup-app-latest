<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const settings = page.props.settings || {};

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
            <p class="text-gray-600 mt-1">Access the admin panel</p>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full h-12"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Enter your email"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full h-12"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                >
                    Forgot password?
                </Link>
            </div>

            <div class="mt-6">
                <PrimaryButton
                    type="submit"
                    class="w-full h-12 rounded-lg flex items-center justify-center"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        Signing in...
                    </span>
                    <span v-else>Sign in</span>
                </PrimaryButton>
            </div>
        </form>

        <!-- Support Info -->
        <div v-if="settings.support_whatsapp || settings.support_email" class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600 text-center">
                Need help? 
                <span v-if="settings.support_whatsapp">
                    <a :href="`https://wa.me/${settings.support_whatsapp}`" target="_blank" class="text-green-600 hover:underline">WhatsApp us</a>
                </span>
                <span v-if="settings.support_email">
                    or <a :href="`mailto:${settings.support_email}`" class="text-blue-600 hover:underline">email us</a>
                </span>
            </p>
        </div>
    </GuestLayout>
</template>
