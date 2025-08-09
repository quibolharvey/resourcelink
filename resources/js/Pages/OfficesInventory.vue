<script setup>
import { ref, watchEffect, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const items = ref(page.props.items || []);
const searchTerm = ref('');

// Keep items in sync with backend props
watchEffect(() => {
    if (page.props.items) {
        items.value = page.props.items;
    }
});

const filteredItems = computed(() => {
    if (!searchTerm.value) return items.value;
    return items.value.filter(item =>
        (item.user?.name || '').toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        item.unit.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        item.description.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
});
</script>

<template>
  <Head title="Offices Inventory" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
        🗂 Offices Inventory
      </h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Search Section -->
        <div class="mb-6">
          <div class="w-full sm:w-64">
            <input 
              type="text" 
              placeholder="Search by name, unit, or desc."
              class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              v-model="searchTerm" 
            />
          </div>
        </div>

        <div class="overflow-hidden bg-white shadow-lg sm:rounded-xl">
          <div class="p-6">
            <h1 class="text-lg font-bold mb-4">All Office Inventory Items</h1>

            <div class="overflow-x-auto">
              <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                  <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Unit</th>
                    <th class="px-4 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-left">Quantity</th>
                    <th class="px-4 py-3 text-left">Price</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr
                    v-for="item in filteredItems"
                    :key="item.id"
                    class="hover:bg-gray-50 transition"
                  >
                    <td class="px-4 py-3 font-medium">{{ item.user?.name || 'N/A' }}</td>
                    <td class="px-4 py-3">{{ item.unit }}</td>
                    <td class="px-4 py-3">{{ item.description }}</td>
                    <td class="px-4 py-3">{{ item.quantity }}</td>
                    <td class="px-4 py-3">₱ {{ Number(item.price).toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</td>
                  </tr>

                  <tr v-if="filteredItems.length === 0">
                    <td colspan="5" class="text-center text-gray-500 py-6">
                      No inventory items found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
