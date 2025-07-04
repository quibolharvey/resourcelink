<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
  borrowedHistories: {
    type: Array,
    default: () => []
  }
});

const statusOptions = ['accepted', 'returned', 'overdue'];

const updateStatus = (id, status) => {
  router.patch(route('borrowedhistory.update', id), { status });
};
</script>

<template>
    <Head title="Items" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Borrowed History
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
                              <th class="px-4 py-2">Email</th>
                              <th class="px-4 py-2">Phone</th>
                              <th class="px-4 py-2">Address</th>
                              <th class="px-4 py-2">Status</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="history in borrowedHistories" :key="history.id">
                              <td class="px-4 py-2">{{ history.item?.name || 'N/A' }}</td>
                              <td class="px-4 py-2">{{ history.quantity }}</td>
                              <td class="px-4 py-2">{{ history.expected_return_date }}</td>
                              <td class="px-4 py-2">{{ history.user?.name || 'N/A' }}</td>
                              <td class="px-4 py-2">{{ history.user?.email || 'N/A' }}</td>
                              <td class="px-4 py-2">{{ history.user?.phone_number || 'N/A' }}</td>
                              <td class="px-4 py-2">{{ history.user?.address || 'N/A' }}</td>
                              <td class="px-4 py-2">
                                <select
                                  v-model="history.status"
                                  @change="updateStatus(history.id, history.status)"
                                  class="border rounded px-2 py-1"
                                >
                                  <option v-for="option in statusOptions" :key="option" :value="option">
                                    {{ option.charAt(0).toUpperCase() + option.slice(1) }}
                                  </option>
                                </select>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                        <div v-if="borrowedHistories.length === 0" class="text-gray-500 mt-4">
                          No borrow history found.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
