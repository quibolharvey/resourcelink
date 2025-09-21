<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    borrowedHistories: {
        type: Array,
        default: () => []
    }
});

const statusOptions = ['accepted', 'returned', 'overdue'];

// Pagination state
const currentPage = ref(1);
const itemsPerPage = 10;

const updateStatus = (id, status) => {
    router.patch(route('borrowedhistory.update', id), { status });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'accepted':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'returned':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'overdue':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Sort borrowed histories with newest records at the top
const sortedBorrowedHistories = computed(() => {
    return [...props.borrowedHistories].sort((a, b) => {
        // Sort by creation date (newest first)
        const dateA = new Date(a.created_at || a.id);
        const dateB = new Date(b.created_at || b.id);
        return dateB - dateA; // Descending order (newest first)
    });
});

// Pagination computed properties
const totalPages = computed(() => {
    return Math.ceil(sortedBorrowedHistories.value.length / itemsPerPage);
});

const paginatedHistories = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return sortedBorrowedHistories.value.slice(start, end);
});

// Pagination methods
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const goToPreviousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const goToNextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};
</script>

<template>
    <Head title="Borrowed History" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                            Borrowed History
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Manage and track all borrowed items</p>
                    </div>
                </div>
                <div class="text-sm text-gray-500 bg-white px-4 py-2 rounded-full border border-gray-200 shadow-sm">
                    {{ sortedBorrowedHistories.length }} {{ sortedBorrowedHistories.length === 1 ? 'record' : 'records' }}
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-2xl border border-blue-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-blue-600 text-sm font-medium">Accepted</p>
                                <p class="text-2xl font-bold text-blue-900">
                                    {{ sortedBorrowedHistories.filter(h => h.status === 'accepted').length }}
                                </p>
                            </div>
                            <div class="p-3 bg-blue-200 rounded-xl">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-6 rounded-2xl border border-green-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-600 text-sm font-medium">Returned</p>
                                <p class="text-2xl font-bold text-green-900">
                                    {{ sortedBorrowedHistories.filter(h => h.status === 'returned').length }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-200 rounded-xl">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-red-50 to-red-100 p-6 rounded-2xl border border-red-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-red-600 text-sm font-medium">Overdue</p>
                                <p class="text-2xl font-bold text-red-900">
                                    {{ sortedBorrowedHistories.filter(h => h.status === 'overdue').length }}
                                </p>
                            </div>
                            <div class="p-3 bg-red-200 rounded-xl">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Table Card -->
                <div class="bg-white shadow-xl rounded-3xl border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <h3 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v1H8V5z"></path>
                            </svg>
                            Borrowed Items Records
                        </h3>
                        <p class="text-gray-500 mt-1">Complete history of all borrowed items and their current status</p>
                    </div>
                    
                    <div class="p-8">
                        <div v-if="sortedBorrowedHistories.length === 0" class="text-center py-16">
                            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No borrowed history found</h3>
                            <p class="text-gray-500">There are no borrowed items to display at the moment.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 rounded-tl-xl">
                                            Item Details
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50">
                                            Quantity
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50">
                                            Return Date
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50">
                                            Borrower Info
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50">
                                            Contact
                                        </th>
                                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider bg-gray-50 rounded-tr-xl">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="history in paginatedHistories" :key="history.id"
                                        class="hover:bg-gray-50 transition-all duration-200 group">
                                        <!-- Item Details -->
                                        <td class="px-6 py-6">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-100 to-purple-100 rounded-xl flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"></path>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                                        {{ history.item?.name || 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Quantity -->
                                        <td class="px-6 py-6">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                                {{ history.quantity }}
                                            </span>
                                        </td>
                                        
                                        <!-- Return Date -->
                                        <td class="px-6 py-6">
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="text-gray-700 font-medium">{{ formatDate(history.expected_return_date) }}</span>
                                            </div>
                                        </td>
                                        
                                        <!-- Borrower Info -->
                                        <td class="px-6 py-6">
                                            <div class="space-y-1">
                                                <div class="font-semibold text-gray-900">{{ history.user?.name || 'N/A' }}</div>
                                                <div class="text-sm text-gray-500 truncate max-w-48">{{ history.user?.address || 'No address' }}</div>
                                            </div>
                                        </td>
                                        
                                        <!-- Contact -->
                                        <td class="px-6 py-6">
                                            <div class="space-y-1">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="text-sm text-gray-600 truncate max-w-32">{{ history.user?.email || 'N/A' }}</span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                    </svg>
                                                    <span class="text-sm text-gray-600">{{ history.user?.phone_number || 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <!-- Status -->
                                        <td class="px-6 py-6">
                                            <div class="flex items-center space-x-3">
                                                <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border', getStatusColor(history.status)]">
                                                    {{ history.status.charAt(0).toUpperCase() + history.status.slice(1) }}
                                                </span>
                                                <div class="flex items-center space-x-2">
                                                    <select v-model="history.status"
                                                        @change="updateStatus(history.id, history.status)"
                                                        :disabled="history.status === 'returned'"
                                                        :class="[
                                                            'border-2 rounded-xl px-4 py-2 text-sm font-medium focus:ring-2 focus:ring-blue-400 focus:border-blue-400 focus:outline-none transition-all duration-200',
                                                            history.status === 'returned' 
                                                                ? 'border-gray-300 bg-gray-100 text-gray-500 cursor-not-allowed' 
                                                                : 'border-gray-200 hover:border-gray-300 bg-white'
                                                        ]">
                                                        <option v-for="option in statusOptions" :key="option" :value="option">
                                                            {{ option.charAt(0).toUpperCase() + option.slice(1) }}
                                                        </option>
                                                    </select>
                                                    <span v-if="history.status === 'returned'" 
                                                        class="text-xs text-gray-500 flex items-center gap-1"
                                                        title="This item has been returned and cannot be changed">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                        </svg>
                                                        Locked
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div v-if="totalPages > 1" class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
                            <div class="flex items-center text-sm text-gray-700">
                                <span class="mr-2">Showing</span>
                                <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                                <span class="mx-1">to</span>
                                <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, sortedBorrowedHistories.length) }}</span>
                                <span class="ml-1">of</span>
                                <span class="font-medium ml-1">{{ sortedBorrowedHistories.length }}</span>
                                <span class="ml-1">results</span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <!-- Previous Button -->
                                <button
                                    @click="goToPreviousPage"
                                    :disabled="currentPage === 1"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-lg border transition-colors duration-200',
                                        currentPage === 1
                                            ? 'border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50'
                                            : 'border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400'
                                    ]"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>

                                <!-- Page Numbers -->
                                <div class="flex items-center space-x-1">
                                    <button
                                        v-for="page in totalPages"
                                        :key="page"
                                        @click="goToPage(page)"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-lg border transition-colors duration-200',
                                            currentPage === page
                                                ? 'bg-blue-600 text-white border-blue-600'
                                                : 'border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                </div>

                                <!-- Next Button -->
                                <button
                                    @click="goToNextPage"
                                    :disabled="currentPage === totalPages"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-lg border transition-colors duration-200',
                                        currentPage === totalPages
                                            ? 'border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50'
                                            : 'border-gray-300 text-gray-700 hover:bg-gray-50 hover:border-gray-400'
                                    ]"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>