<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
  borrowedItems: {
    type: Array,
    default: () => []
  }
});

const statusClass = (status) => {
  switch ((status || 'accepted').toLowerCase()) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'accepted':
      return 'bg-green-100 text-green-800';
    case 'rejected':
      return 'bg-red-100 text-red-800';
    case 'returned':
      return 'bg-blue-100 text-blue-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
};
</script>

<template>
  <Head title="Borrowed Items" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800">Borrowed Items</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-xl overflow-hidden">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="w-full text-sm border-collapse">
                <thead>
                  <tr class="bg-gray-50 text-gray-700 uppercase text-xs tracking-wider">
                    <th class="px-6 py-3 text-left">Item</th>
                    <th class="px-6 py-3 text-left">Qty</th>
                    <th class="px-6 py-3 text-left">Return Date</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Status</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr 
                    v-for="borrow in borrowedItems" 
                    :key="borrow.id" 
                    class="hover:bg-gray-50 transition"
                  >
                    <td class="px-6 py-3 font-medium text-gray-800">
                      {{ borrow.item?.name || 'N/A' }}
                    </td>
                    <td class="px-6 py-3">{{ borrow.quantity }}</td>
                    <td class="px-6 py-3">{{ borrow.expected_return_date }}</td>
                    <td class="px-6 py-3">{{ borrow.user?.name || 'N/A' }}</td>
                    <td class="px-6 py-3">
                      <span
                        class="px-3 py-1 rounded-full text-xs font-medium"
                        :class="statusClass(borrow.status)"
                      >
                        {{ borrow.status || 'accepted' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div
              v-if="borrowedItems.length === 0"
              class="text-center py-8 text-gray-500"
            >
              No borrowed items found.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
