<script setup>
import { ref, computed } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    project: Object,
    paintTotal: Number,
    serviceTotal: {
        type: Number,
        default: 0,
    },
    subtotal: Number,
    flash: Object,
    initialNotes: {
        type: String,
        default: '',
    },
});

const couponForm = useForm({
    coupon_code: '',
});

const form = useForm({
    discount_amount: 0,
    notes: props.initialNotes || '',
});

// Calculate effective discount (from coupon or manual)
const effectiveDiscount = computed(() => {
    if (props.project.coupon_code) {
        // Convert to number to handle string values from database
        const discount = parseFloat(props.project.discount_amount) || 0;
        return isNaN(discount) ? 0 : discount;
    }
    return parseFloat(form.discount_amount) || 0;
});

// Calculate subtotal (before discount)
const subtotalBeforeDiscount = computed(() => props.subtotal);

// Calculate Total After Discount - Single Source of Truth
const totalAfterDiscount = computed(() => {
    return Math.max(0, subtotalBeforeDiscount.value - effectiveDiscount.value);
});

// Final amount customer pays (no GST)
const finalTotal = computed(() => totalAfterDiscount.value);

const formatCurrency = (value) => {
    const safeValue = value || 0;
    if (!Number.isFinite(safeValue) || Number.isNaN(safeValue)) {
        return new Intl.NumberFormat('en-IN', {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 0,
        }).format(0);
    }
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 0,
    }).format(safeValue);
};

const saveNotes = () => {
    form.post(route('supervisor.summary.save-notes', props.project.id), {
        data: {
            notes: form.notes,
        },
        onSuccess: () => {
            // Notes saved successfully
        },
    });
};

const submit = () => {
    form.post(route('supervisor.finalize', props.project.id), {
        data: form.data(),
        onSuccess: () => {
            // Quote saved, user can now download PDF
        },
    });
};

const downloadPdf = () => {
    window.open(route('supervisor.pdf', props.project.id), '_blank');
};

const sendWhatsApp = () => {
    if (!confirm('Send WhatsApp message to customer with login link?')) return;

    fetch(route('supervisor.summary.send-whatsapp', props.project.id), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('WhatsApp message sent successfully!');
        } else {
            alert(data.message || 'Failed to send WhatsApp message');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
};

const applyCoupon = () => {
    if (!couponForm.coupon_code.trim()) {
        alert('Please enter a coupon code');
        return;
    }

    couponForm.post(route('supervisor.projects.apply-coupon', props.project.id), {
        onSuccess: () => {
            couponForm.reset();
        },
        onError: () => {
            // Errors are shown inline
        },
    });
};

const removeCoupon = () => {
    if (!confirm('Are you sure you want to remove this coupon?')) return;

    couponForm.post(route('supervisor.projects.remove-coupon', props.project.id), {
        onSuccess: () => {
            // Coupon removed
        },
    });
};

// Check if payment exists (coupon cannot be modified)
const hasPayment = computed(() => {
    return props.project.booking_status === 'PAID' ||
           props.project.mid_status === 'PAID' ||
           props.project.final_status === 'PAID';
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 pb-24">
        <!-- Header - Screen E Wireframe -->
        <div class="bg-white border-b p-4 shadow-sm sticky top-0 z-10">
            <Link :href="route('supervisor.projects.show', project.id)" class="text-blue-600 text-sm font-medium block mb-1">← Back</Link>
            <h1 class="text-xl font-bold text-gray-900">QUOTE SUMMARY</h1>
        </div>

        <div class="max-w-lg mx-auto px-4 py-4 space-y-4">
            <!-- Success/Error Messages -->
            <div v-if="flash?.success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ flash.success }}
            </div>
            <div v-if="flash?.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ flash.error }}
            </div>

            <!-- Zone Breakdowns - Screen E Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div v-for="zone in project.zones" :key="zone.id" class="mb-4 pb-4 border-b last:border-0 last:mb-0 last:pb-0">
                    <h3 class="font-bold text-gray-800 mb-2">ZONE {{ zone.id }}: {{ zone.name?.toUpperCase() }}</h3>
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Painting Work:</span>
                            <span class="font-medium">{{ formatCurrency((zone.items || []).reduce((sum, i) => sum + (Number(i.amount) || 0), 0)) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Additional Services:</span>
                            <span class="font-medium">{{ formatCurrency((zone.services || []).reduce((sum, s) => sum + (Number(s.amount) || 0), 0)) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtotal - Screen E Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex justify-between text-lg font-medium">
                    <span>SUBTOTAL:</span>
                    <span>{{ formatCurrency(subtotal) }}</span>
                </div>
            </div>

            <!-- Coupon Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3">COUPON CODE</h2>

                <!-- Applied Coupon Display -->
                <div v-if="project.coupon_code" class="mb-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-sm text-green-600 font-medium">Coupon Applied:</span>
                                <span class="ml-2 font-bold text-green-700">{{ project.coupon_code }}</span>
                            </div>
                            <button
                                v-if="!hasPayment"
                                @click="removeCoupon"
                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                            >
                                Remove
                            </button>
                        </div>
                        <div class="mt-2 text-green-700 font-medium">
                            Discount: -{{ formatCurrency(effectiveDiscount) }}
                        </div>
                    </div>
                </div>

                <!-- Apply Coupon Form -->
                <div v-if="!project.coupon_code && !hasPayment">
                    <div class="flex space-x-2">
                        <TextInput
                            v-model="couponForm.coupon_code"
                            type="text"
                            class="h-12 flex-1 uppercase"
                            placeholder="Enter coupon code"
                        />
                        <button
                            @click="applyCoupon"
                            :disabled="couponForm.processing"
                            class="bg-blue-600 text-white px-4 py-3 rounded-lg font-medium h-12"
                        >
                            {{ couponForm.processing ? '...' : 'Apply' }}
                        </button>
                    </div>
                    <p v-if="couponForm.errors.coupon_code" class="text-red-500 text-sm mt-1">
                        {{ couponForm.errors.coupon_code }}
                    </p>
                    <p v-if="couponForm.errors.error" class="text-red-500 text-sm mt-1">
                        {{ couponForm.errors.error }}
                    </p>
                </div>

                <!-- Locked Message -->
                <div v-if="hasPayment && !project.coupon_code" class="text-gray-500 text-sm">
                    Coupon cannot be applied after payment is made
                </div>
            </div>

            <!-- Total After Discount -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex justify-between text-lg font-medium">
                    <span>SUBTOTAL:</span>
                    <span>{{ formatCurrency(subtotal) }}</span>
                </div>
                <div v-if="effectiveDiscount > 0" class="flex justify-between text-sm mt-2">
                    <span class="text-green-600">Discount:</span>
                    <span class="text-green-600">-{{ formatCurrency(effectiveDiscount) }}</span>
                </div>
                <div class="flex justify-between text-xl font-bold mt-2 pt-2 border-t">
                    <span>TOTAL AMOUNT:</span>
                    <span class="text-blue-600">{{ formatCurrency(finalTotal) }}</span>
                </div>
            </div>

            <!-- Global Discount (only if no coupon) -->
            <div v-if="!project.coupon_code" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3">ADDITIONAL DISCOUNT (Flat ₹)</h2>
                <TextInput
                    v-model="form.discount_amount"
                    type="number"
                    class="h-12 border-gray-300"
                    placeholder="0"
                    :disabled="hasPayment"
                />
            </div>

            <!-- Notes / Exclusions - Screen E Wireframe -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h2 class="text-base font-bold text-gray-800 mb-3">NOTES / EXCLUSIONS</h2>
                <textarea
                    v-model="form.notes"
                    class="w-full p-3 border border-gray-300 rounded-lg h-24"
                    placeholder="Deep cleaning not included..."
                    :disabled="hasPayment"
                ></textarea>
                <button
                    v-if="!hasPayment"
                    @click="saveNotes"
                    :disabled="form.processing"
                    class="mt-3 w-full bg-blue-600 text-white py-2 rounded-lg font-medium disabled:opacity-50"
                >
                    {{ form.processing ? 'Saving...' : '💾 SAVE NOTES' }}
                </button>
                <p v-if="form.processing" class="text-sm text-gray-500 mt-2 text-center">Saving notes...</p>
            </div>

            <!-- Generate PDF Button - Screen E Wireframe -->
            <div class="fixed bottom-0 left-0 right-0 p-4 bg-white border-t shadow-lg space-y-2">
                <Link
                    :href="route('supervisor.photos.index', project.id)"
                    class="block w-full bg-orange-500 text-white py-3 rounded-lg font-bold text-base text-center"
                >
                    📷 SITE PHOTOS
                </Link>
                <button
                    @click="sendWhatsApp"
                    class="w-full bg-green-600 text-white py-3 rounded-lg font-bold text-base"
                >
                    SEND WHATSAPP MESSAGE
                </button>
                <!-- PDF download temporarily disabled – will be enabled after GST invoice rollout -->
                <button
                    v-if="false"
                    @click="downloadPdf"
                    class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg"
                >
                    DOWNLOAD PDF
                </button>
            </div>
        </div>
    </div>
</template>