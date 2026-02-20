<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    supervisors: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    client_name: '',
    phone: '',
    location: '',
    status: 'NEW',
    supervisor_id: null,
    home_visit_date: '',
    home_visit_time: '',
    home_visit_supervisors: [],
});

const selectedHomeVisitSupervisors = ref([]);

const submit = () => {
    form.home_visit_supervisors = selectedHomeVisitSupervisors.value;
    form.post(route('admin.projects.store'));
};
</script>

<template>
    <AdminLayout class="container mx-auto max-w-4xl">
        <div class="mb-6">
            <Link
                :href="route('admin.projects.index')"
                class="text-sm text-blue-600 hover:text-blue-800"
            >
                ← Back to Projects
            </Link>
        </div>

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold text-gray-800">Create New Project</h1>
            <p class="text-sm text-gray-500">Create a new home visit project.</p>
        </div>

        <div class="max-w-2xl overflow-hidden rounded-lg bg-white shadow mx-auto">
            <div class="px-6 py-8">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Client Name *</label>
                        <input
                            v-model="form.client_name"
                            type="text"
                            required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Enter client name"
                        />
                        <p v-if="form.errors.client_name" class="mt-1 text-sm text-red-600">{{ form.errors.client_name }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Phone *</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Enter phone number"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Location *</label>
                        <input
                            v-model="form.location"
                            type="text"
                            required
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="Enter location"
                        />
                        <p v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="NEW">New</option>
                            <option value="PENDING">Pending</option>
                            <option value="IN_PROGRESS">In Progress</option>
                            <option value="ACCEPTED">Accepted</option>
                            <option value="REJECTED">Rejected</option>
                            <option value="COMPLETED">Completed</option>
                        </select>
                    </div>

                    <!-- Assign Supervisor -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Assign Supervisor</label>
                        <select
                            v-model="form.supervisor_id"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option :value="null">Select Supervisor...</option>
                            <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">
                                {{ supervisor.name }} ({{ supervisor.email }})
                            </option>
                        </select>
                        <p v-if="form.errors.supervisor_id" class="mt-1 text-sm text-red-600">{{ form.errors.supervisor_id }}</p>
                    </div>

                    <!-- Home Visits Section -->
                    <div class="border-t pt-4 mt-4">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Home Visits</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Visit Date</label>
                                <input
                                    v-model="form.home_visit_date"
                                    type="date"
                                    class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.home_visit_date" class="mt-1 text-sm text-red-600">{{ form.errors.home_visit_date }}</p>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-gray-700">Visit Time</label>
                                <input
                                    v-model="form.home_visit_time"
                                    type="time"
                                    class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                                <p v-if="form.errors.home_visit_time" class="mt-1 text-sm text-red-600">{{ form.errors.home_visit_time }}</p>
                            </div>
                        </div>

                        <!-- <div class="mt-4">
                            <label class="mb-2 block text-sm font-medium text-gray-700">Select Supervisors for Visit</label>
                            <select
                                v-model="selectedHomeVisitSupervisors"
                                multiple
                                class="w-full rounded border border-gray-300 px-3 py-2 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 min-h-[100px]"
                            >
                                <option v-for="supervisor in supervisors" :key="supervisor.id" :value="supervisor.id">
                                    {{ supervisor.name }} ({{ supervisor.email }})
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple supervisors</p>
                            <p v-if="form.errors.home_visit_supervisors" class="mt-1 text-sm text-red-600">{{ form.errors.home_visit_supervisors }}</p>
                        </div> -->
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Project' }}
                        </button>

                        <Link
                            :href="route('admin.projects.index')"
                            class="rounded bg-gray-100 px-4 py-2 text-gray-700 hover:bg-gray-200"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
