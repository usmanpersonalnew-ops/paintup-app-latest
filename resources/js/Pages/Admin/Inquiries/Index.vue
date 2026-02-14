<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    inquiries: Object
});

const search = ref('');

// Safety: Check if we actually have data
const hasInquiries = computed(() => {
    return props.inquiries && props.inquiries.data && props.inquiries.data.length > 0;
});

watch(search, debounce((value) => {
    router.get(route('admin.inquiries.index'), { search: value }, { preserveState: true, replace: true });
}, 300));

const bookVisit = (id) => {
    if(confirm('Create a new Home Visit Project from this lead?')) {
        router.post(route('admin.inquiries.book', id));
    }
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('en-IN', { 
        day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' 
    });
};
</script>

<template>
    <AdminLayout>
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Inquiries & Leads</h1>
                <p class="text-sm text-gray-500">Leads from Website & Calculator</p>
            </div>
            
            <div class="mt-4 md:mt-0 w-full md:w-1/3 relative">
                <input v-model="search" type="text" placeholder="Search leads..." 
                    class="w-full border-gray-300 rounded-lg pl-10 py-2 focus:ring-blue-500 focus:border-blue-500">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
            </div>
        </div>

        <div v-if="!hasInquiries" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl">📢</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900">No inquiries yet</h3>
            <p class="text-gray-500 max-w-sm mx-auto mt-2">
                Your lead pipeline is empty. Connect your WordPress form to the API to see leads appear here instantly.
            </p>
            <div class="mt-6 p-4 bg-gray-50 rounded-lg inline-block text-left text-xs text-gray-600 font-mono border">
                POST https://your-domain.com/api/submit-lead<br>
                { "name": "...", "phone": "..." }
            </div>
        </div>

        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase">
                        <th class="p-4">Date</th>
                        <th class="p-4">Client</th>
                        <th class="p-4">Source</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="lead in inquiries.data" :key="lead.id" class="hover:bg-blue-50 transition group">
                        <td class="p-4 text-sm text-gray-500 font-medium">{{ formatDate(lead.created_at) }}</td>
                        
                        <td class="p-4">
                            <div class="font-bold text-gray-900">{{ lead.name }}</div>
                            <div class="text-xs text-gray-500">{{ lead.phone }}</div>
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs font-bold text-gray-600 uppercase tracking-wide">
                                {{ lead.source }}
                            </span>
                        </td>

                        <td class="p-4">
                            <span class="px-2 py-1 rounded-full text-xs font-bold capitalize"
                                :class="lead.status === 'NEW' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'">
                                {{ lead.status.replace('_', ' ') }}
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            <button v-if="lead.status !== 'VISIT_BOOKED'" 
                                @click="bookVisit(lead.id)" 
                                class="bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-blue-700 shadow-sm">
                                Book Visit ➜
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
