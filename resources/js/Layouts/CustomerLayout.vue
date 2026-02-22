<script setup>
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const branding = page.props.branding || {};
const companyName = branding.company_name || 'PaintUp';
const logoUrl = branding.logo_url;
const primaryColor = branding.primary_color || '#2563eb';
const footerText = branding.footer_text;
const user = page.props.auth?.customer || page.props.auth?.user;
const currentYear = new Date().getFullYear();

const mobileMenuOpen = ref(false);

const menuItems = [
    { name: 'Dashboard', route: 'customer.dashboard', icon: 'home' },
    { name: 'Payments', route: 'customer.payment.history', icon: 'credit-card' },
    { name: 'Work Progress', route: 'customer.work.progress', icon: 'chart' },
    { name: 'Profile', route: 'customer.profile', icon: 'user' },
];

const isActive = (routeName) => {
    const route = page.url || '';
    return route.includes(routeName.replace('customer.', '/customer/'));
};

const closeMobileMenu = () => {
    mobileMenuOpen.value = false;
};

const logout = () => {
    router.post(route('customer.logout'), {}, {
        preserveScroll: false,
        onSuccess: () => {
            mobileMenuOpen.value = false;
            window.location.href = route('customer.login');
        },
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Mobile Sidebar Overlay -->
        <div
            v-if="mobileMenuOpen"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            @click="closeMobileMenu"
        ></div>

        <!-- Mobile Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 w-64 bg-white shadow-lg z-50 transform transition-transform duration-300 md:hidden',
                mobileMenuOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
        >
            <!-- Header -->
            <div :style="{ backgroundColor: primaryColor }" class="p-4">
                <div class="flex justify-between items-center">
                    <Link href="/customer/dashboard" class="flex items-center gap-2">
                        <img
                            v-if="logoUrl"
                            :src="logoUrl"
                            :alt="companyName"
                            class="h-16 w-auto"
                        />
                        <span v-else class="text-xl font-bold text-white">{{ companyName }}</span>
                    </Link>
                    <button @click="closeMobileMenu" class="text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="mt-4">
                <ul class="space-y-1 px-2">
                    <li v-for="item in menuItems" :key="item.name">
                        <Link
                            :href="route(item.route)"
                            @click="closeMobileMenu"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors rounded-lg',
                                isActive(item.route) ? 'bg-opacity-10' : ''
                            ]"
                            :style="isActive(item.route) ? { backgroundColor: primaryColor + '20', color: primaryColor } : {}"
                        >
                            <!-- Home Icon -->
                            <svg v-if="item.icon === 'home'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <!-- Document Icon -->
                            <svg v-else-if="item.icon === 'document'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <!-- Credit Card Icon -->
                            <svg v-else-if="item.icon === 'credit-card'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <!-- Chart Icon -->
                            <svg v-else-if="item.icon === 'chart'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <!-- Camera Icon -->
                            <svg v-else-if="item.icon === 'camera'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Document Text Icon -->
                            <svg v-else-if="item.icon === 'document-text'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <!-- Shield Check Icon -->
                            <svg v-else-if="item.icon === 'shield-check'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <!-- User Icon -->
                            <svg v-else-if="item.icon === 'user'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="font-medium">{{ item.name }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Logout -->
            <div class="absolute bottom-0 w-full p-4 border-t border-gray-200">
                <button
                    @click="logout"
                    class="flex items-center gap-3 w-full px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors rounded-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex md:w-64 flex-col bg-white shadow-lg fixed h-full z-30">
            <!-- Logo -->
            <div :style="{ backgroundColor: primaryColor }" class="p-4">
                <Link href="/customer/dashboard" class="flex items-center justify-center">
                    <img
                        v-if="logoUrl"
                        :src="logoUrl"
                        :alt="companyName"
                        class="h-10 w-auto"
                    />
                    <span v-else class="text-2xl font-bold text-white">{{ companyName }}</span>
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4">
                <ul class="space-y-1 px-3">
                    <li v-for="item in menuItems" :key="item.name">
                        <Link
                            :href="route(item.route)"
                            :class="[
                                'flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-100 transition-colors rounded-lg',
                                isActive(item.route) ? 'bg-opacity-10 font-medium' : ''
                            ]"
                            :style="isActive(item.route) ? { backgroundColor: primaryColor + '20', color: primaryColor } : {}"
                        >
                            <!-- Home Icon -->
                            <svg v-if="item.icon === 'home'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <!-- Document Icon -->
                            <svg v-else-if="item.icon === 'document'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <!-- Credit Card Icon -->
                            <svg v-else-if="item.icon === 'credit-card'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <!-- Chart Icon -->
                            <svg v-else-if="item.icon === 'chart'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <!-- Camera Icon -->
                            <svg v-else-if="item.icon === 'camera'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Document Text Icon -->
                            <svg v-else-if="item.icon === 'document-text'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <!-- Shield Check Icon -->
                            <svg v-else-if="item.icon === 'shield-check'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <!-- User Icon -->
                            <svg v-else-if="item.icon === 'user'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-gray-200">
                <button
                    @click="logout"
                    class="flex items-center gap-3 w-full px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors rounded-lg"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="font-medium">Logout</span>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 md:ml-64 flex flex-col min-h-screen">
            <!-- Top Header -->
            <header class="bg-white shadow-sm sticky top-0 z-20">
                <div class="flex items-center justify-between px-4 py-3 md:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Mobile menu button -->
                        <button
                            @click="mobileMenuOpen = true"
                            class="md:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="md:hidden">
                            <img
                                v-if="logoUrl"
                                :src="logoUrl"
                                :alt="companyName"
                                class="h-16 w-auto"
                            />
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600 hidden sm:block">
                            {{ user?.name || user?.customer_name || 'Customer' }}
                        </span>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 md:p-6 lg:p-8">
                <slot />
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 py-4 px-6">
                <p class="text-sm text-gray-500 text-center">
                    {{ footerText || `© ${currentYear} ${companyName}. All rights reserved.` }}
                </p>
            </footer>
        </div>
    </div>
</template>
