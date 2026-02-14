<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    gateways: Object,
    currentGateway: String,
});

const activeTab = ref(props.currentGateway || 'phonepe');
const showKeys = ref({});

const form = useForm({
    gateway: props.currentGateway,
    phonepe_enabled: props.gateways.phonepe.enabled || false,
    phonepe_merchant_id: '',
    phonepe_salt_key: '',
    phonepe_salt_index: 1,
    phonepe_environment: props.gateways.phonepe.environment || 'sandbox',
    ccavenue_enabled: props.gateways.ccavenue.enabled || false,
    ccavenue_merchant_id: '',
    ccavenue_working_key: '',
    ccavenue_access_code: '',
    ccavenue_environment: props.gateways.ccavenue.environment || 'sandbox',
});

const toggleKeyVisibility = (gateway) => {
    showKeys.value[gateway] = !showKeys.value[gateway];
};

const submit = () => {
    form.post(route('admin.payment-gateways.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Payment Gateway Settings</h1>

        <!-- Gateway Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Default Payment Gateway</label>
            <select v-model="form.gateway" class="w-full max-w-md border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="phonepe">PhonePe</option>
                <option value="ccavenue">CCAvenue</option>
            </select>
        </div>

        <!-- PhonePe Settings -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-600 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">P</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">PhonePe</h2>
                        <p class="text-sm text-gray-500">India's leading digital payments platform</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="form.phonepe_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div v-if="form.phonepe_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Merchant ID</label>
                    <input 
                        type="password" 
                        v-model="form.phonepe_merchant_id" 
                        placeholder="Enter PhonePe Merchant ID"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salt Key</label>
                    <input 
                        type="password" 
                        v-model="form.phonepe_salt_key" 
                        placeholder="Enter PhonePe Salt Key"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salt Index</label>
                    <input 
                        type="number" 
                        v-model="form.phonepe_salt_index" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                    <select 
                        v-model="form.phonepe_environment" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                        <option value="sandbox">Sandbox (Testing)</option>
                        <option value="production">Production (Live)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- CCAvenue Settings -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold text-lg">C</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">CCAvenue</h2>
                        <p class="text-sm text-gray-500">India's largest independent payment solutions aggregator</p>
                    </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" v-model="form.ccavenue_enabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>

            <div v-if="form.ccavenue_enabled" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Merchant ID</label>
                    <input 
                        type="password" 
                        v-model="form.ccavenue_merchant_id" 
                        placeholder="Enter CCAvenue Merchant ID"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Working Key</label>
                    <input 
                        type="password" 
                        v-model="form.ccavenue_working_key" 
                        placeholder="Enter CCAvenue Working Key"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Access Code</label>
                    <input 
                        type="password" 
                        v-model="form.ccavenue_access_code" 
                        placeholder="Enter CCAvenue Access Code"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
                    <select 
                        v-model="form.ccavenue_environment" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 h-12"
                    >
                        <option value="sandbox">Sandbox (Testing)</option>
                        <option value="production">Production (Live)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end">
            <button 
                @click="submit"
                :disabled="form.processing"
                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg shadow-md transition-colors disabled:opacity-50"
            >
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Settings</span>
            </button>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Note:</strong> After saving, clear your application cache for changes to take effect. 
                        Use sandbox mode for testing before switching to production.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>