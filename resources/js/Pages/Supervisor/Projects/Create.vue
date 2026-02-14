<script setup>
import SupervisorLayout from '@/Layouts/SupervisorLayout.vue';
import { useForm } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineOptions({
    layout: SupervisorLayout,
});

const form = useForm({
    client_name: '',
    location: '',
    phone: '',
    status: 'LEAD',
});

const statusOptions = [
    { value: 'LEAD', label: 'Lead' },
    { value: 'ONGOING', label: 'Ongoing' },
    { value: 'COMPLETED', label: 'Completed' },
];
</script>

<template>
    <div class="p-4">
        <h1 class="text-xl font-bold text-gray-800 mb-4">New Project</h1>

        <form @submit.prevent="form.post(route('supervisor.projects.store'))" class="space-y-4">
            <div>
                <InputLabel for="client_name" value="Client Name" />
                <TextInput
                    id="client_name"
                    v-model="form.client_name"
                    type="text"
                    class="mt-1 block w-full h-12"
                    placeholder="Enter client name"
                    required
                />
                <InputError class="mt-1" :message="form.errors.client_name" />
            </div>

            <div>
                <InputLabel for="location" value="Location" />
                <TextInput
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="mt-1 block w-full h-12"
                    placeholder="Enter location"
                    required
                />
                <InputError class="mt-1" :message="form.errors.location" />
            </div>

            <div>
                <InputLabel for="phone" value="Phone" />
                <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="mt-1 block w-full h-12"
                    placeholder="Enter phone number"
                    required
                />
                <InputError class="mt-1" :message="form.errors.phone" />
            </div>

            <div>
                <InputLabel for="status" value="Status" />
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full h-12 px-3 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
                <InputError class="mt-1" :message="form.errors.status" />
            </div>

            <div class="pt-4">
                <PrimaryButton class="w-full h-12 text-base" :disabled="form.processing">
                    {{ form.processing ? 'Creating...' : 'Create Project' }}
                </PrimaryButton>
            </div>
        </form>
    </div>
</template>