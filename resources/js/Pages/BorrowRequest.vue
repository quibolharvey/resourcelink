<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
  borrowedItems: {
    type: Array,
    default: () => []
  }
});

const updateStatus = (id, status) => {
  router.patch(route('borrowrequest.update', id), { status });
};
</script>

<template>
  <Head title="Borrowed Requests" />

  <AuthenticatedLayout> 
    <template #header>
      <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
        📋 Borrowed Requests
      </h2>
    </template>

    <div class="py-10">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg sm:rounded-xl overflow-hidden">
          <div class="p-6">
            <div class="overflow-x-auto">
              <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                  <tr>
                    <th class="px-4 py-3 text-left">Item</th>
                    <th class="px-4 py-3 text-left">Qty</th>
                    <th class="px-4 py-3 text-left">Return Date</th>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Address</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Action</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr 
                    v-for="borrow in borrowedItems" 
                    :key="borrow.id" 
                    class="hover:bg-gray-50 transition"
                  >
                    <td class="px-4 py-3 font-medium">{{ borrow.item?.name || 'N/A' }}</td>
                    <td class="px-4 py-3">{{ borrow.quantity }}</td>
                    <td class="px-4 py-3">{{ borrow.expected_return }}</td>
                    <td class="px-4 py-3">{{ borrow.user?.name || 'N/A' }}</td>
                    <td class="px-4 py-3">{{ borrow.user?.email || 'N/A' }}</td>
                    <td class="px-4 py-3">{{ borrow.user?.phone_number || 'N/A' }}</td>
                    <td class="px-4 py-3">{{ borrow.user?.address || 'N/A' }}</td>
                    <td class="px-4 py-3">
                      <span 
                        :class="{
                          'px-2 py-1 rounded-full text-xs font-semibold': true,
                          'bg-yellow-100 text-yellow-800': borrow.status === 'pending',
                          'bg-green-100 text-green-800': borrow.status === 'accepted',
                          'bg-red-100 text-red-800': borrow.status === 'rejected'
                        }"
                      >
                        {{ borrow.status }}
                      </span>
                    </td>
                    <td class="px-4 py-3 flex justify-center gap-2">
                      <button
                        @click="updateStatus(borrow.id, 'accepted')"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg shadow-sm transition"
                      >
                        Accept
                      </button>
                      <button
                        @click="updateStatus(borrow.id, 'rejected')"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg shadow-sm transition"
                      >
                        Decline
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="borrowedItems.length === 0" class="text-gray-500 text-center py-6">
              No borrow requests found.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
