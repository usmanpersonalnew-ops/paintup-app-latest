<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const settings = page.props.settings || page.props.branding || {};
const companyName = settings.company_name || 'PaintUp';
const logoUrl = settings.logo_url;
const primaryColor = settings.primary_color || '#2563eb';
const secondaryColor = settings.secondary_color || '#1e293b';
</script>

<template>
    <div class="min-h-screen flex">
        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-center items-center p-12 text-white"
             :style="{ background: `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)` }">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>

            <!-- Content -->
            <div class="relative z-10 max-w-md text-center">
                <!-- Logo -->
                <div class="mb-8">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="companyName"
                        class="h-20 w-auto mx-auto"
                    />
                    <ApplicationLogo v-else class="h-20 w-auto fill-current text-white" />
                </div>

                <!-- Heading -->
                <h1 class="text-4xl font-bold mb-4">Welcome to {{ companyName }}</h1>



            </div>

            <!-- Footer -->
            <div class="absolute bottom-8 text-sm opacity-75">
                © {{ new Date().getFullYear() }} {{ companyName }}. All rights reserved.
            </div>
        </div>

        <!-- Right Panel - Login Card -->
        <div class="flex-1 flex items-center justify-center p-6 sm:p-12 bg-gray-50">
            <div class="w-full max-w-md">
                <!-- Mobile Logo -->
                <div class="lg:hidden text-center mb-8">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="companyName"
                        class="h-16 w-auto mx-auto mb-4"
                    />
                    <h1 class="text-2xl font-bold text-gray-900">{{ companyName }}</h1>
                </div>

                <!-- Login Card -->
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    <slot />
                </div>

                <!-- Mobile Footer -->
                <div class="lg:hidden mt-8 text-center text-sm text-gray-500">
                    © {{ new Date().getFullYear() }} {{ companyName }}. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</template>
