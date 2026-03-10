<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
customer:Object
});

const form = useForm({
name:props.customer.name,
email:props.customer.email,
phone:props.customer.phone,
status:props.customer.status
});
</script>

<template>
<AdminLayout>

<h2 class="text-xl font-semibold mb-4">Edit Customer</h2>

<form @submit.prevent="form.put(`/admin/customers/${props.customer.id}`)">

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
<label>Status</label>
<select v-model="form.status" class="border w-full p-2">
<option value="ACTIVE">ACTIVE</option>
<option value="INACTIVE">INACTIVE</option>
</select>
</div>

<button class="bg-blue-600 text-white px-4 py-2 rounded">
Update
</button>

</form>

</AdminLayout>
</template>