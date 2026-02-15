<script setup>
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    settings: {
        type: Object,
        default: () => ({
            company_name: 'PaintUp',
            logo_path: '',
            signature_path: '',
            primary_color: '#2563eb',
            secondary_color: '#1e293b',
            support_whatsapp: '',
            support_email: '',
            footer_text: '',
            gst_number: '',
            address: '',
            invoice_prefix: 'INV',
        }),
    },
});

// Initialize logo preview - check both logo_url and generate from logo_path if needed
const getLogoUrl = () => {
    if (props.settings?.logo_url) {
        return props.settings.logo_url;
    }
    // Fallback: generate URL from logo_path if logo_url not provided
    if (props.settings?.logo_path) {
        return `/storage/${props.settings.logo_path}`;
    }
    return null;
};

const logoPreview = ref(getLogoUrl());

// Initialize signature preview - check both signature_url and generate from signature_path if needed
const getSignatureUrl = () => {
    if (props.settings?.signature_url) {
        return props.settings.signature_url;
    }
    // Fallback: generate URL from signature_path if signature_url not provided
    if (props.settings?.signature_path) {
        return `/storage/${props.settings.signature_path}`;
    }
    return null;
};

const signaturePreview = ref(getSignatureUrl());

// Debug: Log settings on mount
console.log('Settings on mount:', props.settings);
console.log('Logo URL:', props.settings?.logo_url);
console.log('Logo Path:', props.settings?.logo_path);
console.log('Logo Preview:', logoPreview.value);
console.log('Signature URL:', props.settings?.signature_url);
console.log('Signature Path:', props.settings?.signature_path);
console.log('Signature Preview:', signaturePreview.value);

// Watch for changes to settings prop (e.g., after form submission)
watch(() => props.settings, (newSettings) => {
    const newLogoUrl = newSettings?.logo_url || (newSettings?.logo_path ? `/storage/${newSettings.logo_path}` : null);
    console.log('Settings changed, new logo URL:', newLogoUrl);

    // Only update if:
    // 1. New logo URL exists
    // 2. Current preview is not a blob URL (file selection preview)
    if (newLogoUrl && !logoPreview.value?.startsWith('blob:')) {
        logoPreview.value = newLogoUrl;
        console.log('Updated logo preview to:', logoPreview.value);
    } else if (newLogoUrl && !logoPreview.value) {
        // If no preview exists, set it
        logoPreview.value = newLogoUrl;
        console.log('Set initial logo preview to:', logoPreview.value);
    }

    // Watch for signature changes
    const newSignatureUrl = newSettings?.signature_url || (newSettings?.signature_path ? `/storage/${newSettings.signature_path}` : null);
    if (newSignatureUrl && !signaturePreview.value?.startsWith('blob:')) {
        signaturePreview.value = newSignatureUrl;
    } else if (newSignatureUrl && !signaturePreview.value) {
        signaturePreview.value = newSignatureUrl;
    }
}, { immediate: true, deep: true });

const form = useForm({
    company_name: props.settings.company_name,
    logo_path: null,
    signature_path: null,
    primary_color: props.settings.primary_color,
    secondary_color: props.settings.secondary_color,
    support_whatsapp: props.settings.support_whatsapp,
    support_email: props.settings.support_email,
    footer_text: props.settings.footer_text,
    gst_number: props.settings.gst_number,
    address: props.settings.address,
    invoice_prefix: props.settings.invoice_prefix,
});

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo_path = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const handleSignatureChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.signature_path = file;
        signaturePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.settings.update'), {
        forceFormData: true,
    });
};
</script>

<template>
    <AuthenticatedLayout title="Branding Settings">
        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Page Header -->
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Branding Settings</h1>
                <p class="text-gray-600 mt-1">Customize your platform's branding and appearance</p>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Company Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Company Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="company_name" value="Company Name" />
                            <TextInput
                                id="company_name"
                                v-model="form.company_name"
                                type="text"
                                class="mt-1 block w-full h-12"
                                placeholder="Enter company name"
                            />
                            <InputError class="mt-1" :message="form.errors.company_name" />
                        </div>

                        <div>
                            <InputLabel for="invoice_prefix" value="Invoice Prefix" />
                            <TextInput
                                id="invoice_prefix"
                                v-model="form.invoice_prefix"
                                type="text"
                                class="mt-1 block w-full h-12"
                                placeholder="e.g., INV"
                            />
                            <InputError class="mt-1" :message="form.errors.invoice_prefix" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="address" value="Address" />
                        <textarea
                            id="address"
                            v-model="form.address"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 border shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-3"
                            placeholder="Enter company address"
                        ></textarea>
                        <InputError class="mt-1" :message="form.errors.address" />
                    </div>

                    <div>
                        <InputLabel for="gst_number" value="GST Number" />
                        <TextInput
                            id="gst_number"
                            v-model="form.gst_number"
                            type="text"
                            class="mt-1 block w-full h-12"
                            placeholder="e.g., 27ABCDE1234F1Z5"
                        />
                        <InputError class="mt-1" :message="form.errors.gst_number" />
                    </div>
                </div>

                <!-- Logo Upload -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Logo</h2>

                    <div class="flex items-center gap-6">
                        <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                alt="Logo preview"
                                class="w-full h-full object-contain"
                            />
                            <span v-else class="text-gray-400 text-sm">No logo</span>
                        </div>
                        <div class="flex-1">
                            <label class="block">
                                <span class="sr-only">Choose logo</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleLogoChange"
                                    class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-3 file:px-6
                                        file:rounded-lg file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        cursor-pointer"
                                />
                            </label>
                            <p class="text-sm text-gray-500 mt-2">Upload PNG, JPG, or SVG (max 2MB)</p>
                            <InputError class="mt-1" :message="form.errors.logo_path" />
                        </div>
                    </div>
                </div>

                <!-- Signature Upload -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Signature</h2>

                    <div class="flex items-center gap-6">
                        <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 overflow-hidden">
                            <img
                                v-if="signaturePreview"
                                :src="signaturePreview"
                                alt="Signature preview"
                                class="w-full h-full object-contain"
                            />
                            <span v-else class="text-gray-400 text-sm">No signature</span>
                        </div>
                        <div class="flex-1">
                            <label class="block">
                                <span class="sr-only">Choose signature</span>
                                <input
                                    type="file"
                                    accept="image/*"
                                    @change="handleSignatureChange"
                                    class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-3 file:px-6
                                        file:rounded-lg file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        cursor-pointer"
                                />
                            </label>
                            <p class="text-sm text-gray-500 mt-2">Upload PNG, JPG, or SVG (max 2MB)</p>
                            <InputError class="mt-1" :message="form.errors.signature_path" />
                        </div>
                    </div>
                </div>

                <!-- Colors -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Brand Colors</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="primary_color" value="Primary Color" />
                            <div class="flex items-center gap-3 mt-1">
                                <input
                                    type="color"
                                    v-model="form.primary_color"
                                    class="h-12 w-20 rounded-lg border border-gray-300 cursor-pointer"
                                />
                                <TextInput
                                    v-model="form.primary_color"
                                    type="text"
                                    class="flex-1 h-12"
                                    placeholder="#2563eb"
                                />
                            </div>
                            <InputError class="mt-1" :message="form.errors.primary_color" />
                            <p class="text-sm text-gray-500 mt-1">Used for buttons, links, and accents</p>
                        </div>

                        <div>
                            <InputLabel for="secondary_color" value="Secondary Color" />
                            <div class="flex items-center gap-3 mt-1">
                                <input
                                    type="color"
                                    v-model="form.secondary_color"
                                    class="h-12 w-20 rounded-lg border border-gray-300 cursor-pointer"
                                />
                                <TextInput
                                    v-model="form.secondary_color"
                                    type="text"
                                    class="flex-1 h-12"
                                    placeholder="#1e293b"
                                />
                            </div>
                            <InputError class="mt-1" :message="form.errors.secondary_color" />
                            <p class="text-sm text-gray-500 mt-1">Used for sidebars, headers, and backgrounds</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Contact Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <InputLabel for="support_whatsapp" value="Support WhatsApp" />
                            <TextInput
                                id="support_whatsapp"
                                v-model="form.support_whatsapp"
                                type="text"
                                class="mt-1 block w-full h-12"
                                placeholder="e.g., 919876543210"
                            />
                            <InputError class="mt-1" :message="form.errors.support_whatsapp" />
                            <p class="text-sm text-gray-500 mt-1">Include country code (e.g., 91)</p>
                        </div>

                        <div>
                            <InputLabel for="support_email" value="Support Email" />
                            <TextInput
                                id="support_email"
                                v-model="form.support_email"
                                type="email"
                                class="mt-1 block w-full h-12"
                                placeholder="support@example.com"
                            />
                            <InputError class="mt-1" :message="form.errors.support_email" />
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
                    <h2 class="text-lg font-semibold text-gray-900 border-b pb-2">Footer Settings</h2>

                    <div>
                        <InputLabel for="footer_text" value="Footer Text" />
                        <TextInput
                            id="footer_text"
                            v-model="form.footer_text"
                            type="text"
                            class="mt-1 block w-full h-12"
                            placeholder="© 2024 Your Company. All rights reserved."
                        />
                        <InputError class="mt-1" :message="form.errors.footer_text" />
                        <p class="text-sm text-gray-500 mt-1">If left empty, default footer text will be used</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <PrimaryButton
                        type="submit"
                        class="h-12 px-8 rounded-lg"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Saving...</span>
                        <span v-else>Save Settings</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
