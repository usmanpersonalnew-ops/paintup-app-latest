<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const page = usePage();
const settings = page.props.settings || {};
const props = defineProps({
    phone: {
        type: String,
        default: '',
    },
    redirect_to: {
        type: String,
        default: null,
    },
});

const step = ref('phone'); // 'phone' or 'otp'
const phone = ref(props.phone || '');
const otpDigits = ref(['', '', '', '', '', '']);
const loading = ref(false);
const error = ref('');
const debugOtp = ref(null);

const formattedPhone = computed(() => {
    const cleaned = phone.value.replace(/\D/g, '');
    return cleaned.startsWith('91') ? cleaned : '91' + cleaned;
});

const canSendOtp = computed(() => {
    return phone.value.length >= 10;
});

const canVerifyOtp = computed(() => {
    return otpDigits.value.every(d => d !== '');
});

const sendOtp = async () => {
    if (!canSendOtp.value) return;
    
    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post('/customer/auth/send-otp', { phone: formattedPhone.value });
        
        const data = response.data;
        
        if (data.success) {
            step.value = 'otp';
            debugOtp.value = data.debug_otp; // Remove in production
        } else {
            error.value = data.message || 'Failed to send OTP';
        }
    } catch (e) {
        // Network errors - show user-friendly message
        error.value = 'Unable to connect. Please check your internet and try again.';
        console.error('OTP send error:', e);
    } finally {
        loading.value = false;
    }
};

const verifyOtp = async () => {
    if (!canVerifyOtp.value) return;
    
    loading.value = true;
    error.value = '';
    
    try {
        const response = await axios.post('/customer/auth/verify-otp', {
            phone: formattedPhone.value,
            otp: otpDigits.value.join(''),
            redirect_to: props.redirect_to,
        });
        
        const data = response.data;
        
        if (data.success) {
            // Redirect to dashboard or specified redirect
            window.location.href = data.redirect_to || '/customer/dashboard';
        } else {
            error.value = data.message || 'Invalid OTP';
        }
    } catch (e) {
        // Network errors - show user-friendly message
        error.value = 'Unable to connect. Please check your internet and try again.';
        console.error('OTP verify error:', e);
    } finally {
        loading.value = false;
    }
};

const onOtpInput = (index, event) => {
    const value = event.target.value;
    
    if (value.length === 1 && index < 5) {
        const nextInput = document.querySelector(`#otp-${index + 2}`);
        if (nextInput) nextInput.focus();
    }
    
    if (value.length === 0 && index > 0) {
        const prevInput = document.querySelector(`#otp-${index}`);
        if (prevInput) prevInput.focus();
    }
    
    otpDigits.value[index] = value;
};

const onKeydown = (index, event) => {
    if (event.key === 'Backspace' && otpDigits.value[index] === '' && index > 0) {
        const prevInput = document.querySelector(`#otp-${index}`);
        if (prevInput) prevInput.focus();
    }
};

const resendOtp = () => {
    otpDigits.value = ['', '', '', '', '', ''];
    step.value = 'phone';
    debugOtp.value = null;
};
</script>

<template>
    <GuestLayout>
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
            <p class="text-gray-600 mt-1">Login with your phone number</p>
        </div>

        <!-- Phone Input Step -->
        <div v-if="step === 'phone'" class="space-y-6">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                </label>
                <input
                    id="phone"
                    v-model="phone"
                    type="tel"
                    placeholder="Enter 10-digit mobile number"
                    class="w-full h-12 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)] text-center text-lg"
                    @keyup.enter="sendOtp"
                />
                <p class="mt-2 text-sm text-gray-500 text-center">
                    Example: 9876543210
                </p>
            </div>

            <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm text-center">
                {{ error }}
            </div>

            <button
                :disabled="!canSendOtp || loading"
                @click="sendOtp"
                class="w-full h-12 rounded-lg text-white font-medium transition-all duration-200 flex items-center justify-center gap-2"
                :class="[canSendOtp && !loading ? 'bg-[var(--primary-color)] hover:opacity-90' : 'bg-gray-300 cursor-not-allowed']"
            >
                <span v-if="loading" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                    Sending OTP...
                </span>
                <span v-else>Send OTP via WhatsApp</span>
            </button>
        </div>

        <!-- OTP Verification Step -->
        <div v-else class="space-y-6">
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Enter the 6-digit OTP sent to
                </p>
                <p class="font-medium text-gray-900 mt-1">{{ formattedPhone }}</p>
                <p v-if="debugOtp" class="text-xs text-yellow-600 mt-2 px-3 py-1 bg-yellow-50 rounded-lg inline-block">
                    Development Mode - OTP: {{ debugOtp }}
                </p>
            </div>

            <div class="flex justify-center gap-2">
                <input
                    v-for="(_, index) in 6"
                    :key="index"
                    :id="`otp-${index + 1}`"
                    type="text"
                    maxlength="1"
                    :value="otpDigits[index]"
                    class="w-12 h-12 text-center text-xl border border-gray-300 rounded-lg focus:ring-2 focus:ring-[var(--primary-color)] focus:border-[var(--primary-color)]"
                    @input="onOtpInput(index, $event)"
                    @keydown="onKeydown(index, $event)"
                />
            </div>

            <div v-if="error" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm text-center">
                {{ error }}
            </div>

            <div class="space-y-4">
                <button
                    :disabled="!canVerifyOtp || loading"
                    @click="verifyOtp"
                    class="w-full h-12 rounded-lg text-white font-medium transition-all duration-200 flex items-center justify-center gap-2"
                    :class="[canVerifyOtp && !loading ? 'bg-[var(--primary-color)] hover:opacity-90' : 'bg-gray-300 cursor-not-allowed']"
                >
                    <span v-if="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                        </svg>
                        Verifying...
                    </span>
                    <span v-else>Verify OTP</span>
                </button>

                <button
                    @click="resendOtp"
                    class="w-full text-sm text-gray-600 hover:text-[var(--primary-color)] transition-colors"
                >
                    Didn't receive OTP? Resend
                </button>
            </div>
        </div>

        <!-- Support Info -->
        <div v-if="settings.support_whatsapp || settings.support_email" class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-sm text-gray-600 text-center">
                Need help? 
                <span v-if="settings.support_whatsapp">
                    <a :href="`https://wa.me/${settings.support_whatsapp}`" target="_blank" class="text-green-600 hover:underline">WhatsApp us</a>
                </span>
                <span v-if="settings.support_email">
                    or <a :href="`mailto:${settings.support_email}`" class="text-[var(--primary-color)] hover:underline">email us</a>
                </span>
            </p>
        </div>
    </GuestLayout>
</template>