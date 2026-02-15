<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: AdminLayout,
});

defineProps({
    projects: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const statusColors = {
    'DRAFT': 'bg-gray-100 text-gray-800',
    'AWAITING_CASH_CONFIRMATION': 'bg-orange-100 text-orange-800',
    'CONFIRMED': 'bg-green-100 text-green-800',
    'IN_PROGRESS': 'bg-blue-100 text-blue-800',
    'COMPLETED': 'bg-purple-100 text-purple-800',
};

const getStatusLabel = (status) => {
    const labels = {
        'DRAFT': 'Draft',
        'AWAITING_CASH_CONFIRMATION': 'Awaiting Cash',
        'CONFIRMED': 'Confirmed',
        'IN_PROGRESS': 'In Progress',
        'COMPLETED': 'Completed',
    };
    return labels[status] || status;
};

const formatCurrency = (value) => {
    if (!value) return '₹0';
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

const getPaymentBadge = (project) => {
    if (project.booking_payment_status === 'PAID') {
        return {
            text: `💰 Booking: ${formatCurrency(project.booking_amount)}`,
            class: 'bg-green-50 text-green-700 border border-green-200',
        };
    }
    if (project.payment_method === 'CASH') {
        return {
            text: '💵 Cash (Pending)',
            class: 'bg-orange-50 text-orange-700 border border-orange-200',
        };
    }
    return {
        text: `💰 Booking: ${formatCurrency(project.booking_amount || 0)}`,
        class: 'bg-gray-50 text-gray-600 border border-gray-200',
    };
};

const deleteProject = (project) => {
    if (confirm(`Are you sure you want to delete project "${project.client_name}"? This action cannot be undone.`)) {
        // Use window.location to navigate to the delete URL
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        const token = csrfToken ? csrfToken.getAttribute('content') : '';

        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/projects/${project.id}`;

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = token;
        form.appendChild(tokenInput);

        document.body.appendChild(form);
        form.submit();
    }
};

// Get CSRF token from meta tag
const csrfToken = (() => {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
})();

// Handle delete click with confirmation
const handleDelete = (e) => {
    if (!confirm('Are you sure you want to delete this project?')) {
        e.preventDefault();
    }
};
</script>

<template>
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Projects</h1>
            <Link
                :href="route('admin.projects.create')"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium"
            >
                + New Project
            </Link>
        </div>

        <!-- Search/Filter -->
        <div class="mb-6">
            <form class="flex gap-4">
                <input
                    type="text"
                    placeholder="Search projects..."
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                />
                <select class="px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="DRAFT">Draft</option>
                    <option value="AWAITING_CASH_CONFIRMATION">Awaiting Cash</option>
                    <option value="CONFIRMED">Confirmed</option>
                    <option value="IN_PROGRESS">In Progress</option>
                    <option value="COMPLETED">Completed</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="project in projects.data" :key="project.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ project.client_name }}</div>
                            <div class="text-sm text-gray-500">{{ project.location }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ project.phone }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[project.status]]">
                                {{ getStatusLabel(project.status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span :class="['px-2 py-1 text-xs rounded border', getPaymentBadge(project).class]">
                                {{ getPaymentBadge(project).text }}
                            </span>
                            <span v-if="project.payment_method" class="ml-2 text-xs text-gray-500">
                                {{ project.payment_method === 'ONLINE' ? '🟢' : '💵' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="text-sm font-bold text-gray-900">{{ formatCurrency(project.total_amount) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <Link :href="route('admin.projects.quote', project.id)" class="text-green-600 hover:text-green-900 mr-3" title="View Quote">
                                📄 Quote
                            </Link>
                            <Link :href="route('admin.projects.show', project.id)" class="text-blue-600 hover:text-blue-900 mr-3">
                                Manage
                            </Link>
                            <Link :href="route('admin.projects.edit', project.id)" class="text-gray-600 hover:text-gray-900 mr-3">
                                Edit
                            </Link>
                            <!-- Inertia Link with DELETE method -->
                            <Link
                                :href="`/admin/projects/${project.id}`"
                                method="delete"
                                as="button"
                                class="text-red-600 hover:text-red-900"
                                :data="{ _token: csrfToken }"
                                @click="handleDelete"
                            >
                                Delete
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="projects.links && projects.links.length > 3" class="px-6 py-4 border-t">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Showing {{ projects.from }} to {{ projects.to }} of {{ projects.total }} results
                    </div>
                    <div class="flex gap-2">
                        <Link
                            v-for="link in projects.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-1 rounded border text-sm',
                                link.active ? 'bg-blue-600 text-white border-blue-600' : 'text-gray-700 hover:bg-gray-50'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
