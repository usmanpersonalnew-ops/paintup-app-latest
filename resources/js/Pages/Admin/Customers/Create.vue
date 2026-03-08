<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
});
</script>

<template>
    <AdminLayout>

        <h2 class="text-xl font-semibold mb-4">Create Customer</h2>

        <form @submit.prevent="form.post('/admin/customers')">

            <div class="mb-3">
                <label>Name</label>
                <input v-model="form.name" class="border w-full p-2">
            </div>

            <div class="mb-3">
                <label>Email</label>
                <input v-model="form.email" class="border w-full p-2">
            </div>

            <div class="mb-3">
                <label>Phone</label>
                <input v-model="form.phone" class="border w-full p-2">
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" v-model="form.password" class="border w-full p-2">
            </div>

            <button class="bg-green-600 text-white px-4 py-2 rounded">
                Save
            </button>

        </form>

    </AdminLayout>
</template>