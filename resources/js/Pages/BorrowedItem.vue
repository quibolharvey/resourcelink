<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  borrowedItems: {
    type: Array,
    default: () => []
  }
});

// Sort borrowed items by creation date (newest first)
const sortedBorrowedItems = computed(() => {
  return [...props.borrowedItems].sort((a, b) => {
    const dateA = new Date(a.created_at || a.id);
    const dateB = new Date(b.created_at || b.id);
    return dateB - dateA; // Descending order (newest first)
  });
});

const statusClass = (status) => {
  switch ((status || 'accepted').toLowerCase()) {
    case 'pending':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'accepted':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    case 'rejected':
      return 'bg-rose-50 text-rose-700 border-rose-200';
    case 'returned':
      return 'bg-sky-50 text-sky-700 border-sky-200';
    default:
      return 'bg-rose-50 text-rose-700 border-rose-200';
  }
};

const statusIcon = (status) => {
  switch ((status || 'accepted').toLowerCase()) {
    case 'pending':
      return '🕐';
    case 'accepted':
      return '✅';
    case 'rejected':
      return '❌';
    case 'returned':
      return '🔄';
    default:
      return '❌';
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};
</script>

<template>
  <Head title="Borrowed Items" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10  bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Borrowed Items</h2>
          <!-- <p class="text-sm text-gray-600 mt-1">Manage and track all borrowed inventory items</p> -->
        </div>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-blue-50 rounded-xl">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Items</p>
                <p class="text-2xl font-bold text-gray-900">{{ sortedBorrowedItems.length }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-amber-50 rounded-xl">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pending</p>
                <p class="text-2xl font-bold text-gray-900">{{ sortedBorrowedItems.filter(item => (item.status || 'accepted').toLowerCase() === 'pending').length }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-emerald-50 rounded-xl">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Accepted</p>
                <p class="text-2xl font-bold text-gray-900">{{ sortedBorrowedItems.filter(item => (item.status || 'accepted').toLowerCase() === 'accepted').length }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
              <div class="p-2 bg-sky-50 rounded-xl">
                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Returned</p>
                <p class="text-2xl font-bold text-gray-900">{{ sortedBorrowedItems.filter(item => (item.status || 'accepted').toLowerCase() === 'returned').length }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Table Card -->
        <div class="bg-white shadow-sm rounded-2xl border border-gray-100 overflow-hidden">
          <div class="px-6 py-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900">Items Overview</h3>
              <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                  {{ sortedBorrowedItems.length }} {{ sortedBorrowedItems.length === 1 ? 'item' : 'items' }}
                </span>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200">
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                      <span>Item</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                      </svg>
                      <span>Quantity</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                      </svg>
                      <span>Return Date</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      <span>Borrower</span>
                    </div>
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                      </svg>
                      <span>Status</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr 
                  v-for="borrow in sortedBorrowedItems" 
                  :key="borrow.id" 
                  class="hover:bg-gray-50/50 transition-all duration-200 group"
                >
                  <td class="px-6 py-5">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                      </div>
                      <div>
                        <div class="font-semibold text-gray-900 text-sm">
                          {{ borrow.item?.name || 'N/A' }}
                        </div>
                        <!-- <div class="text-xs text-gray-500 mt-1">
                          ID: #{{ borrow.id }}
                        </div> -->
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <div class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-50 border border-gray-200">
                      <span class="text-sm font-semibold text-gray-700">{{ borrow.quantity }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0V6a2 2 0 012-2h4a2 2 0 012 2v1M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                      </svg>
                      <span class="text-sm font-medium text-gray-700">
                        {{ formatDate(borrow.expected_return_date) }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <div class="flex items-center space-x-3">
                      <div class="w-8 h-8 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-semibold text-indigo-700">
                          {{ (borrow.user?.name || 'N/A').charAt(0).toUpperCase() }}
                        </span>
                      </div>
                      <span class="text-sm font-medium text-gray-700">
                        {{ borrow.user?.name || 'N/A' }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <span
                      class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all duration-200 hover:scale-105"
                      :class="statusClass(borrow.status)"
                    >
                      <span class="mr-2">{{ statusIcon(borrow.status) }}</span>
                      {{ (borrow.status || 'accepted').charAt(0).toUpperCase() + (borrow.status || 'accepted').slice(1) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div
            v-if="sortedBorrowedItems.length === 0"
            class="text-center py-16"
          >
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
              <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No borrowed items found</h3>
            <p class="text-gray-500 text-sm max-w-md mx-auto">
              There are currently no items in the borrowed items list. New borrowed items will appear here once they're added to the system.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>