<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const requests = page.props.requests || [];

// --- Pagination State ---
const currentPage = ref(1);
const itemsPerPage = 10;

// --- Pagination Computed Properties ---
const totalItems = computed(() => requests.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));

const paginatedRequests = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return requests.slice(start, end);
});

// --- Pagination Navigation Functions ---
const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const goToFirstPage = () => goToPage(1);
const goToLastPage = () => goToPage(totalPages.value);
const goToPreviousPage = () => goToPage(currentPage.value - 1);
const goToNextPage = () => goToPage(currentPage.value + 1);

const showModal = ref(false);
const modalLoading = ref(false);
const selectedRequest = ref(null);
const modalError = ref('');

const openModal = async (id) => {
    showModal.value = true;
    modalLoading.value = true;
    modalError.value = '';
    try {
        const response = await fetch(`/api/office-request/${id}`);
        if (response.ok) {
            selectedRequest.value = await response.json();
        } else {
            modalError.value = 'Failed to load request details.';
        }
    } catch (e) {
        modalError.value = 'Failed to load request details.';
    }
    modalLoading.value = false;
};

const closeModal = () => {
    showModal.value = false;
    selectedRequest.value = null;
    modalError.value = '';
};
</script>

<template>
    <Head title="Request History" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight">
                        Request History
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        View and manage all your purchase requests
                    </p>
                </div>
                <div class="hidden sm:flex items-center space-x-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span>{{ totalItems }} total requests</span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Enhanced Card Container -->
                <div class="bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Purchase Requests</h3>
                            <div class="flex items-center space-x-2">
                                <div class="h-2 w-2 bg-green-400 rounded-full"></div>
                                <span class="text-xs text-gray-500 font-medium">Live Data</span>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>Requestor</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span>Purchase Request</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8v2m-4 0V11a1 1 0 011-1h6a1 1 0 011 1v8a1 1 0 01-1 1H7a1 1 0 01-1-1z"></path>
                                            </svg>
                                            <span>Request Date</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 uppercase text-xs tracking-wider">
                                        <div class="flex items-center space-x-1">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Status</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr
                                    v-for="req in paginatedRequests"
                                    :key="req.id"
                                    class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group"
                                >
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center">
                                                <span class="text-white font-semibold text-sm">
                                                    {{ req.user?.name?.charAt(0).toUpperCase() }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ req.user?.name }}</p>
                                                <p class="text-sm text-gray-500">ID: #{{ req.id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <button
                                            @click="openModal(req.id)"
                                            class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 font-medium text-sm group-hover:scale-105"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            <span>View Details</span>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6a2 2 0 012-2z"></path>
                                            </svg>
                                            <span class="text-gray-700 font-medium">
                                                {{ new Date(req.created_at).toLocaleDateString('en-US', { 
                                                    year: 'numeric', 
                                                    month: 'short', 
                                                    day: 'numeric' 
                                                }) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full shadow-sm"
                                            :class="{
                                                'bg-gradient-to-r from-green-100 to-emerald-100 text-green-800 border border-green-200': req.purchase_cart?.status === 'Approved',
                                                'bg-gradient-to-r from-yellow-100 to-amber-100 text-yellow-800 border border-yellow-200': req.purchase_cart?.status === 'Pending',
                                                'bg-gradient-to-r from-red-100 to-rose-100 text-red-800 border border-red-200': req.purchase_cart?.status === 'Rejected',
                                                'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-700 border border-gray-200': !req.purchase_cart?.status
                                            }"
                                        >
                                            <div
                                                class="w-2 h-2 rounded-full mr-2"
                                                :class="{
                                                    'bg-green-500': req.purchase_cart?.status === 'Approved',
                                                    'bg-yellow-500': req.purchase_cart?.status === 'Pending',
                                                    'bg-red-500': req.purchase_cart?.status === 'Rejected',
                                                    'bg-gray-400': !req.purchase_cart?.status
                                                }"
                                            ></div>
                                            {{ req.purchase_cart?.status || 'Unknown' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="totalItems === 0">
                                    <td colspan="4" class="text-center py-12">
                                        <div class="flex flex-col items-center justify-center space-y-4">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="text-center">
                                                <h3 class="text-lg font-medium text-gray-900 mb-1">No requests found</h3>
                                                <p class="text-gray-500">You haven't made any purchase requests yet.</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div v-if="totalPages > 1" class="bg-white px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-700">
                                <span>Page {{ currentPage }} of {{ totalPages }}</span>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <!-- First Page Button -->
                                <button 
                                    @click="goToFirstPage"
                                    :disabled="currentPage === 1"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                                    </svg>
                                </button>
                                
                                <!-- Previous Page Button -->
                                <button 
                                    @click="goToPreviousPage"
                                    :disabled="currentPage === 1"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>
                                
                                <!-- Page Numbers -->
                                <div class="flex items-center space-x-1">
                                    <template v-for="page in Math.min(5, totalPages)" :key="page">
                                        <button 
                                            v-if="page <= totalPages"
                                            @click="goToPage(page)"
                                            :class="[
                                                'inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                                                page === currentPage 
                                                    ? 'bg-blue-600 text-white border border-blue-600' 
                                                    : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                                            ]"
                                        >
                                            {{ page }}
                                        </button>
                                    </template>
                                    
                                    <!-- Ellipsis for more pages -->
                                    <span v-if="totalPages > 5" class="px-2 text-gray-500">...</span>
                                    
                                    <!-- Last page if not in the first 5 -->
                                    <button 
                                        v-if="totalPages > 5 && currentPage < totalPages - 2"
                                        @click="goToPage(totalPages)"
                                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-150"
                                    >
                                        {{ totalPages }}
                                    </button>
                                </div>
                                
                                <!-- Next Page Button -->
                                <button 
                                    @click="goToNextPage"
                                    :disabled="currentPage === totalPages"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                                
                                <!-- Last Page Button -->
                                <button 
                                    @click="goToLastPage"
                                    :disabled="currentPage === totalPages"
                                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-150"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="closeModal"
        >
            <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl transform transition-all duration-300 scale-100">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white">Purchase Request Details</h3>
                    </div>
                    <button
                        @click="closeModal"
                        class="text-white hover:text-gray-200 transition-colors p-2 hover:bg-white hover:bg-opacity-20 rounded-lg"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
                    <!-- Loading State -->
                    <div v-if="modalLoading" class="flex flex-col items-center justify-center py-12 space-y-4">
                        <div class="animate-spin w-12 h-12 border-4 border-blue-200 border-t-blue-600 rounded-full"></div>
                        <p class="text-gray-600 font-medium">Loading request details...</p>
                    </div>

                    <!-- Error State -->
                    <div v-else-if="modalError" class="flex flex-col items-center justify-center py-12 space-y-4">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="text-center">
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Failed to Load</h3>
                            <p class="text-red-600">{{ modalError }}</p>
                        </div>
                    </div>

                    <!-- Content -->
                    <div v-else-if="selectedRequest" class="space-y-6">
                        <!-- Request Info Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-4 border border-blue-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-blue-600">Requested by</p>
                                        <p class="font-semibold text-gray-900">{{ selectedRequest.user?.name }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-4 border border-green-100">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8v2m-6-4h12a2 2 0 012 2v6a2 2 0 01-2-2v-6a2 2 0 012-2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-green-600">Request Date</p>
                                        <p class="font-semibold text-gray-900">
                                            {{ new Date(selectedRequest.created_at).toLocaleDateString('en-US', { 
                                                year: 'numeric', 
                                                month: 'long', 
                                                day: 'numeric' 
                                            }) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Section -->
                        <div class="bg-gray-50 rounded-xl p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900 flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                    </svg>
                                    <span>Requested Items</span>
                                </h4>
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
                                    {{ selectedRequest.purchase_cart.items.length }} item(s)
                                </span>
                            </div>
                            
                            <div class="bg-white rounded-lg overflow-hidden shadow-sm border border-gray-200">
                                <table class="w-full">
                                    <thead class="bg-gradient-to-r from-gray-100 to-gray-50">
                                        <tr>
                                            <th class="py-3 px-4 text-left font-semibold text-gray-700 text-sm">Unit</th>
                                            <th class="py-3 px-4 text-left font-semibold text-gray-700 text-sm">Description</th>
                                            <th class="py-3 px-4 text-left font-semibold text-gray-700 text-sm">Quantity</th>
                                            <th class="py-3 px-4 text-left font-semibold text-gray-700 text-sm">Unit Price</th>
                                            <th class="py-3 px-4 text-left font-semibold text-gray-700 text-sm">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr
                                            v-for="item in selectedRequest.purchase_cart.items"
                                            :key="item.id"
                                            class="hover:bg-gray-50 transition-colors"
                                        >
                                            <td class="py-3 px-4 font-medium text-gray-900">{{ item.unit }}</td>
                                            <td class="py-3 px-4 text-gray-700">{{ item.description }}</td>
                                            <td class="py-3 px-4 text-center font-medium text-gray-900">{{ item.quantity }}</td>
                                            <td class="py-3 px-4 font-medium text-gray-900">₱{{ Number(item.price).toLocaleString() }}</td>
                                            <td class="py-3 px-4 font-bold text-blue-600">₱{{ (item.quantity * item.price).toLocaleString() }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Total Summary -->
                            <div class="mt-4 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg p-4">
                                <div class="flex justify-between items-center text-white">
                                    <span class="text-lg font-semibold">Total Amount:</span>
                                    <span class="text-2xl font-bold">
                                        ₱{{ selectedRequest.purchase_cart.items.reduce((sum, item) => sum + (item.quantity * item.price), 0).toLocaleString() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
/* Additional custom styles for enhanced animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

/* Smooth scrollbar for modal content */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>