<script setup>
import { computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const page = usePage();
const branding = page.props.branding || {};
const companyName = branding.company_name || 'PaintUp';
const logoPath = branding.logo_path;
const primaryColor = branding.primary_color || '#2563eb';
const footerText = branding.footer_text;
const currentRoute = computed(() => page.url);
const currentYear = new Date().getFullYear();

const navItems = [
    { name: 'Dashboard', route: 'supervisor.dashboard', icon: 'home' },
    { name: 'Projects', route: 'supervisor.projects.index', icon: 'folder' },
    { name: 'Profile', route: 'profile.edit', icon: 'user' },
];
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header -->
        <header :style="{ backgroundColor: primaryColor }" class="text-white px-4 py-3 flex items-center justify-between shadow-md">
            <div class="flex items-center gap-2">
                <img 
                    v-if="logoPath" 
                    :src="`/storage/${logoPath}`" 
                    :alt="companyName"
                    class="h-8 w-auto"
                />
                <h1 v-else class="text-lg font-semibold">{{ companyName }}</h1>
            </div>
            <span class="text-sm opacity-80">Supervisor</span>
        </header>

        <!-- Main Content -->
        <main class="flex-1 pb-20 overflow-y-auto">
            <slot />
        </main>

        <!-- Bottom Navigation Bar -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
            <div class="flex justify-around items-center h-16">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="route(item.route)"
                    class="flex flex-col items-center justify-center w-full h-full transition-colors"
                    :class="currentRoute.startsWith(route(item.route).split('?')[0]) ? '' : 'text-gray-500 hover:text-gray-700'"
                    :style="{ color: currentRoute.startsWith(route(item.route).split('?')[0]) ? primaryColor : '' }"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path v-if="item.icon === 'home'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        <path v-else-if="item.icon === 'folder'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                        <path v-else-if="item.icon === 'user'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-xs mt-1">{{ item.name }}</span>
                </Link>
            </div>
        </nav>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-3 px-4 text-center">
            <p class="text-xs text-gray-500">
                {{ footerText || `© ${currentYear} ${companyName}. All rights reserved.` }}
            </p>
        </footer>
    </div>
</template>