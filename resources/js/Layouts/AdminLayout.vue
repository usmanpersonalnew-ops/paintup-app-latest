<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage();
const branding = page.props.branding || {};
const companyName = branding.company_name || 'PaintUp';
const logoUrl = branding.logo_url;
const primaryColor = branding.primary_color || '#2563eb';
const secondaryColor = branding.secondary_color || '#1e293b';
const footerText = branding.footer_text;

defineProps({
    title: {
        type: String,
        default: 'Admin Panel'
    }
});

const sidebarOpen = ref(true);
const mobileSidebarOpen = ref(false);
const currentYear = new Date().getFullYear();
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Mobile sidebar overlay -->
        <div
            v-if="mobileSidebarOpen"
            class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
            @click="mobileSidebarOpen = false"
        ></div>

        <div class="flex h-screen overflow-hidden w-full">
            <!-- Sidebar111 -->
            <aside
                :class="[
                    'text-white transition-all duration-300 ease-in-out fixed lg:relative z-50 h-full',
                    mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
                    sidebarOpen ? 'w-64' : 'w-20'
                ]"
                :style="{ backgroundColor: secondaryColor }"
            >
                <!-- Logo -->
                <div class="flex h-16 items-center justify-center border-b border-white/10">
                    <Link href="/admin/dashboard" class="flex items-center gap-2">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="companyName"
                            class="h-16 w-auto"
                        />
                        <ApplicationLogo v-else class="h-16 w-auto fill-current" />

                    </Link>
                </div>

                <!-- Navigation -->
                <nav class="mt-6 px-4">
                    <ul class="space-y-2">
                        <li>
                            <Link
                                href="/admin/dashboard"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                <span v-if="sidebarOpen">Dashboard</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/surfaces"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span v-if="sidebarOpen">Master Surfaces</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/products"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <span v-if="sidebarOpen">Master Products</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/services"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span v-if="sidebarOpen">Master Services</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/coupons"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <span v-if="sidebarOpen">Coupons</span>
                            </Link>
                        </li>
                    </ul>
                </nav>

                <!-- Divider -->
                <div class="my-4 border-t border-white/10"></div>

                <!-- TEAM & OPS Section -->
                <div class="px-4">
                    <p v-if="sidebarOpen" class="mb-2 text-xs font-semibold uppercase tracking-wider text-white/50">
                        Team & Operations
                    </p>
                    <ul class="space-y-2">
                        <li>
                            <Link
                                href="/admin/users"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span v-if="sidebarOpen">Team Members</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/projects"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span v-if="sidebarOpen">Projects</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/inquiries"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <span v-if="sidebarOpen">Inquiries</span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Divider -->
                <div class="my-4 border-t border-white/10"></div>

                <!-- ACCOUNT Section -->
                <div class="px-4 mt-6">
                    <p v-if="sidebarOpen" class="mb-2 text-xs font-semibold uppercase tracking-wider text-white/50">
                        Account
                    </p>
                    <ul class="space-y-2">
                        <li>
                            <Link
                                href="/admin/settings"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span v-if="sidebarOpen">Settings</span>
                            </Link>
                        </li>
                        <li>
                            <Link
                                href="/admin/profile"
                                class="flex items-center gap-3 rounded-lg px-4 py-3 text-white hover:bg-white/10 transition-colors"
                                :class="{ 'justify-center': !sidebarOpen }"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span v-if="sidebarOpen">My Profile</span>
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Toggle Button -->
                <div class="absolute bottom-4 left-0 right-0 px-4">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-white hover:bg-white/20 transition-colors"
                    >
                        <svg
                            :class="{ 'rotate-180': sidebarOpen }"
                            class="h-5 w-5 transition-transform"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                </div>
            </aside>

            <!-- Main Content -->
            <div class="flex flex-1 flex-col overflow-hidden">
                <!-- Top Header -->
                <header class="flex h-16 items-center justify-between bg-white px-4 lg:px-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <!-- Mobile menu button -->
                        <button
                            @click="mobileSidebarOpen = !mobileSidebarOpen"
                            class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors"
                        >
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-xl font-semibold text-gray-900">
                            {{ title }}
                        </h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500 hidden sm:block">Welcome, Admin</span>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-y-auto bg-gray-50">
                    <div class="p-6 lg:p-8">
                        <slot />
                    </div>

                    <!-- Footer -->
                    <footer class="bg-white border-t border-gray-200 py-4 px-6">
                        <p class="text-sm text-gray-500 text-center">
                            {{ footerText || `© ${currentYear} ${companyName}. All rights reserved.` }}
                        </p>
                    </footer>
                </main>
            </div>
        </div>
    </div>
</template>
