<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
customer:Object
});
</script>

<template>
<AdminLayout>

<h2 class="text-xl font-semibold mb-4">Customer Details</h2>

<div class="border p-4 space-y-2">

<p><b>ID:</b> {{ customer.id }}</p>
<p><b>Name:</b> {{ customer.name }}</p>
<p><b>Email:</b> {{ customer.email }}</p>
<p><b>Phone:</b> {{ customer.phone }}</p>
<p><b>Status:</b> {{ customer.status }}</p>

</div>

</AdminLayout>
</template>