<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

// Define props safely with a default array
const props = defineProps({
  borrowedItems: {
    type: Array,
    default: () => []
  }
});

// Patch request to update borrow status
const updateStatus = (id, status) => {
  router.patch(route('borrowrequest.update', id), { status });
};
</script>

<template>
  <Head title="Borrowed Requests" />
  <AuthenticatedLayout> 
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Borrowed Requests</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow sm:rounded-lg">
          <div class="p-6">
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
                  <th class="px-4 py-2">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="borrow in borrowedItems" :key="borrow.id">
                  <td class="px-4 py-2">{{ borrow.item?.name || 'N/A' }}</td>
                  <td class="px-4 py-2">{{ borrow.quantity }}</td>
                  <td class="px-4 py-2">{{ borrow.expected_return }}</td>
                  <td class="px-4 py-2">{{ borrow.user?.name || 'N/A' }}</td>
                  <td class="px-4 py-2">{{ borrow.user?.email || 'N/A' }}</td>
                  <td class="px-4 py-2">{{ borrow.user?.phone_number || 'N/A' }}</td>
                  <td class="px-4 py-2">{{ borrow.user?.address || 'N/A' }}</td>
                  <td class="px-4 py-2">{{ borrow.status }}</td>
                  <td class="px-4 py-2 space-x-2">
                    <button
                      @click="updateStatus(borrow.id, 'accepted')"
                      class="bg-green-600 text-white px-2 py-1 rounded"
                    >
                      Accept
                    </button>
                    <button
                      @click="updateStatus(borrow.id, 'rejected')"
                      class="bg-red-600 text-white px-2 py-1 rounded"
                    >
                      Decline
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="borrowedItems.length === 0" class="text-gray-500 mt-4">
              No borrow requests found.
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
