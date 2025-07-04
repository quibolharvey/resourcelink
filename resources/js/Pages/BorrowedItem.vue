<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  borrowedItems: {
    type: Array,
    default: () => []
  }
});
</script>

<template>
    <Head title="Items" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Borrowed Items
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-gray-900">
                        <table class="w-full text-sm border border-gray-300">
                          <thead>
                            <tr class="bg-gray-100 text-left">
                              <th class="px-4 py-2">Item</th>
                              <th class="px-4 py-2">Qty</th>
                              <th class="px-4 py-2">Return Date</th>
                              <th class="px-4 py-2">Name</th>
                              <th class="px-4 py-2">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="borrow in borrowedItems" :key="borrow.id">
                              <td class="px-4 py-2">{{ borrow.item?.name || 'N/A' }}</td>
                              <td class="px-4 py-2">{{ borrow.quantity }}</td>
                              <td class="px-4 py-2">{{ borrow.expected_return_date }}</td>
                              <td class="px-4 py-2">{{ borrow.user?.name || 'N/A' }}</td>
                              <td class="px-4 py-2">{{ borrow.status || 'accepted' }}</td>
                            </tr>
                          </tbody>
                        </table>
                        <div v-if="borrowedItems.length === 0" class="text-gray-500 mt-4">
                          No borrowed items found.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
