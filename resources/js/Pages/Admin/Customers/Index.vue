<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    customers: Object
});

const deleteCustomer = (id) => {
    if (confirm('Delete this customer?')) {
        router.delete(`/admin/customers/${id}`);
    }
};
</script>

<template>
<AdminLayout>

<div class="flex justify-between mb-4">
<h2 class="text-xl font-semibold">Customers</h2>

<button
class="bg-green-600 text-white px-4 py-2 rounded"
@click="router.get('/admin/customers/create')">
Add Customer
</button>
</div>

<table class="w-full border">
<thead>
<tr class="bg-gray-100">
<th class="p-2 border">Sr No</th>
<th class="p-2 border">Name</th>
<th class="p-2 border">Email</th>
<th class="p-2 border">Phone</th>
<th class="p-2 border">Status</th>
<th class="p-2 border">Actions</th>
</tr>
</thead>

<tbody>

<tr v-for="(customer, index) in customers.data" :key="customer.id">

<td class="p-2 border">
{{ (customers.current_page - 1) * customers.per_page + index + 1 }}
</td>

<td class="p-2 border">{{ customer.name }}</td>
<td class="p-2 border">{{ customer.email }}</td>
<td class="p-2 border">{{ customer.phone }}</td>
<td class="p-2 border">{{ customer.status }}</td>

<td class="p-2 border space-x-2">

<button
class="bg-blue-500 text-white px-2 py-1 rounded"
@click="router.get(`/admin/customers/${customer.id}`)">
View
</button>

<button
class="bg-yellow-500 text-white px-2 py-1 rounded"
@click="router.get(`/admin/customers/${customer.id}/edit`)">
Edit
</button>

<button
class="bg-red-600 text-white px-2 py-1 rounded"
@click="deleteCustomer(customer.id)">
Delete
</button>

</td>

</tr>

<tr v-if="customers.data.length === 0">
<td colspan="6" class="text-center p-4">No customers found</td>
</tr>

</tbody>
</table>

<!-- Pagination -->

<div class="mt-6 flex gap-2">

<button
v-for="link in customers.links"
:key="link.label"
v-html="link.label"
:disabled="!link.url"
@click="router.get(link.url)"
class="px-3 py-1 border rounded"
:class="{ 'bg-gray-200': link.active }"
/>

</div>

</AdminLayout>
</template>