<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  borrowedItems: {
    type: Array,
    default: () => []
  }
});

const searchTerm = ref('');

// Filter borrow requests based on search term
const filteredBorrowedItems = computed(() => {
  if (!searchTerm.value.trim()) {
    return props.borrowedItems;
  }
  
  const term = searchTerm.value.toLowerCase().trim();
  return props.borrowedItems.filter(borrow => 
    borrow.item?.name?.toLowerCase().includes(term) ||
    borrow.user?.name?.toLowerCase().includes(term) ||
    borrow.user?.email?.toLowerCase().includes(term) ||
    borrow.status?.toLowerCase().includes(term) ||
    borrow.user?.address?.toLowerCase().includes(term)
  );
});

// Calculate available stock for each item
const getAvailableStock = (itemId) => {
  const item = props.borrowedItems.find(borrow => borrow.item?.id === itemId)?.item;
  if (!item) return 0;
  
  // Get total quantity already accepted for this item
  const acceptedQuantity = props.borrowedItems
    .filter(borrow => borrow.item?.id === itemId && borrow.status === 'accepted')
    .reduce((total, borrow) => total + (borrow.quantity || 0), 0);
  
  return item.quantity - acceptedQuantity;
};

// Check if a borrow request can be accepted
const canAcceptRequest = (borrow) => {
  if (borrow.status === 'accepted') return false;
  
  const availableStock = getAvailableStock(borrow.item?.id);
  return availableStock >= borrow.quantity;
};

const updateStatus = (id, status) => {
  router.patch(route('borrowrequest.update', id), { status });
};

const getStatusIcon = (status) => {
  switch (status) {
    case 'pending': return '⏳';
    case 'accepted': return '✅';
    case 'rejected': return '❌';
    default: return '📄';
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <Head title="Borrowed Requests" />

  <AuthenticatedLayout> 
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Borrow Requests</h2>
          <p class="text-sm text-gray-600 mt-1">Manage and review all borrowing requests</p>
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Enhanced Header Section -->
        <!-- <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 mb-8 border border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6"> -->
            <!-- Total Requests Card -->
            <!-- <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
              <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-gray-500">Total Requests</div>
                  <div class="text-2xl font-bold text-gray-900">{{ borrowedItems.length }}</div>
                </div>
              </div>
            </div> -->

            <!-- Pending Card -->
            <!-- <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
              <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-gray-500">Pending</div>
                  <div class="text-2xl font-bold text-yellow-600">{{ borrowedItems.filter(item => item.status === 'pending').length }}</div>
                </div>
              </div>
            </div> -->

            <!-- Accepted Card -->
            <!-- <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
              <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                  <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-gray-500">Accepted</div>
                  <div class="text-2xl font-bold text-green-600">{{ borrowedItems.filter(item => item.status === 'accepted').length }}</div>
                </div>
              </div>
            </div> -->

            <!-- Rejected Card -->
            <!-- <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-100">
              <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                  <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <div class="text-sm font-medium text-gray-500">Rejected</div>
                  <div class="text-2xl font-bold text-red-600">{{ borrowedItems.filter(item => item.status === 'rejected').length }}</div>
                </div>
              </div>
            </div>
          </div> -->
        </div>

        <!-- Search Bar -->
        <div class="bg-white/70 backdrop-blur-md rounded-2xl shadow-sm border border-white/20 p-6 mb-8">
          <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative flex-1 max-w-md">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input 
                type="text" 
                v-model="searchTerm"
                placeholder="Search requests." 
                class="w-full pl-10 pr-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-500"
              />
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <span class="px-3 py-1.5 bg-blue-100 text-blue-700 rounded-full font-medium">
                {{ filteredBorrowedItems.length }} {{ searchTerm ? 'requests found' : 'total requests' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Enhanced Table -->
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
          <!-- Table Header -->
          <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Borrow Requests</h3>
              <div class="text-sm text-gray-600">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mr-2">
                  {{ filteredBorrowedItems.filter(item => item.status === 'pending').length }} Pending
                </span>
                Review required
              </div>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <div v-if="filteredBorrowedItems.length === 0" class="text-center py-12">
              <div class="flex flex-col items-center">
                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-lg font-medium text-gray-900">
                  {{ searchTerm ? 'No requests found' : 'No requests available' }}
                </p>
                <p class="text-gray-500 mt-1">
                  {{ searchTerm ? `No requests match "${searchTerm}". Try a different search term.` : 'New borrow requests will appear here when submitted' }}
                </p>
                <button 
                  v-if="searchTerm"
                  @click="searchTerm = ''"
                  class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
                >
                  Clear Search
                </button>
              </div>
            </div>

            <table v-else class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Item Details
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Quantity
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Available Stock
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Borrowed Date
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Return Date
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Borrower Info
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Contact
                  </th>
                  <!-- <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Status
                  </th> -->
                  <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="borrow in filteredBorrowedItems" :key="borrow.id" class="hover:bg-gray-50 transition-colors duration-150">
                  <!-- Item Details -->
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ borrow.item?.name || 'N/A' }}</div>
                        <div class="text-sm text-gray-500">Item Request</div>
                      </div>
                    </div>
                  </td>

                  <!-- Quantity -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ borrow.quantity }}
                    </span>
                  </td>

                  <!-- Available Stock -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center space-x-2">
                      <span 
                        :class="[
                          'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                          getAvailableStock(borrow.item?.id) >= borrow.quantity 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-red-100 text-red-800'
                        ]"
                      >
                        {{ getAvailableStock(borrow.item?.id) }}
                      </span>
                      <span class="text-xs text-gray-500">
                        / {{ borrow.item?.quantity || 0 }}
                      </span>
                    </div>
                    <div v-if="!canAcceptRequest(borrow) && borrow.status === 'pending'" class="text-xs text-red-600 mt-1">
                      Insufficient stock
                    </div>
                  </td>

                  <!-- Borrowed Date -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ borrow.borrow_date ? formatDate(borrow.borrow_date) : 'N/A' }}</div>
                    <div class="text-sm text-gray-500">Date to Borrow</div>
                  </td>

                  <!-- Return Date -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ formatDate(borrow.expected_return) }}</div>
                    <div class="text-sm text-gray-500">Expected Return</div>
                  </td>

                  <!-- Borrower Info -->
                  <td class="px-6 py-4">
                    <div class="text-sm font-medium text-gray-900">{{ borrow.user?.name || 'N/A' }}</div>
                    <div class="text-sm text-gray-500 max-w-xs truncate">
                      <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      {{ borrow.user?.address || 'No address' }}
                    </div>
                  </td>

                  <!-- Contact -->
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900 flex items-center">
                      <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                      </svg>
                      {{ borrow.user?.email || 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-500 flex items-center mt-1">
                      <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                      </svg>
                      {{ borrow.user?.phone_number || 'No phone' }}
                    </div>
                  </td>

                  <!-- Status -->
                  <!-- <td class="px-6 py-4 whitespace-nowrap">
                    <span 
                      :class="{
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium': true,
                        'bg-yellow-100 text-yellow-800': borrow.status === 'pending',
                        'bg-green-100 text-green-800': borrow.status === 'accepted',
                        'bg-red-100 text-red-800': borrow.status === 'rejected'
                      }"
                    >
                      <svg 
                        :class="{
                          'w-3 h-3 mr-1': true,
                          'text-yellow-600': borrow.status === 'pending',
                          'text-green-600': borrow.status === 'accepted',
                          'text-red-600': borrow.status === 'rejected'
                        }" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                      >
                        <path v-if="borrow.status === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path v-if="borrow.status === 'accepted'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        <path v-if="borrow.status === 'rejected'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                      {{ borrow.status.charAt(0).toUpperCase() + borrow.status.slice(1) }}
                    </span>
                  </td> -->

                  <!-- Actions -->
                  <td class="px-6 py-4 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center space-x-2">
                      <button
                        @click="updateStatus(borrow.id, 'accepted')"
                        :disabled="borrow.status === 'accepted' || !canAcceptRequest(borrow)"
                        :class="[
                          'inline-flex items-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-150',
                          borrow.status === 'accepted' || !canAcceptRequest(borrow)
                            ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            : 'bg-green-50 text-green-700 hover:bg-green-100'
                        ]"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ !canAcceptRequest(borrow) && borrow.status === 'pending' ? 'No Stock' : 'Accept' }}
                      </button>
                      <button
                        @click="updateStatus(borrow.id, 'rejected')"
                        :disabled="borrow.status === 'rejected'"
                        class="inline-flex items-center px-3 py-1.5 bg-red-50 text-red-700 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Decline
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    <!-- </div> -->
  </AuthenticatedLayout>
</template>