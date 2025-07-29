<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const items = page.props.items || [];
</script>

<template>
  <Head title="Offices Inventory" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">
        Offices Inventory
      </h2>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg p-6">
          <h1 class="text-lg font-bold mb-4">All Office Inventory Items</h1>
          <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="item in items" :key="item.id" class="hover:bg-gray-100">
                <td class="px-4 py-2">{{ item.user?.name || 'N/A' }}</td>
                <td class="px-4 py-2">{{ item.unit }}</td>
                <td class="px-4 py-2">{{ item.description }}</td>
                <td class="px-4 py-2">{{ item.quantity }}</td>
                <td class="px-4 py-2">₱ {{ Number(item.price).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</td>
                
              </tr>
              <tr v-if="items.length === 0">
                <td colspan="6" class="text-center text-gray-500 py-6">No inventory items found.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
