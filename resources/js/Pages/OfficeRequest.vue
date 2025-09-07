<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const requests = ref(page.props.requests || []);

// Show requests that are NOT approved (treat anything else as pending/active)
const pendingRequests = computed(() =>
    (requests.value || []).filter((r) => r.purchase_cart?.status !== 'approved')
);

const showModal = ref(false);
const modalLoading = ref(false);
const selectedRequest = ref(null);
const modalError = ref('');

const showHistoryModal = ref(false);
const historyLoading = ref(false);
const historyRequests = ref([]);
const historyError = ref('');

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
    } catch {
        modalError.value = 'Failed to load request details.';
    }
    modalLoading.value = false;
};

const closeModal = () => {
    showModal.value = false;
    selectedRequest.value = null;
    modalError.value = '';
};

const approveRequest = async (req) => {
    try {
        const response = await fetch(`/api/office-request/${req.id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        if (response.ok) {
            req.purchase_cart.status = 'approved';
            // No need to remove from requests; the view uses pendingRequests which filters by status
            // Close modal if the approved request is currently open
            if (selectedRequest.value && selectedRequest.value.id === req.id) {
                closeModal();
            }
            // If history modal is open, refresh history list
            if (showHistoryModal.value) {
                try {
                    const hres = await fetch('/api/office-request-history');
                    if (hres.ok) {
                        historyRequests.value = await hres.json();
                    }
                } catch {}
            }
        }
    } catch {}
};

const openHistoryModal = async () => {
    showHistoryModal.value = true;
    historyLoading.value = true;
    historyError.value = '';
    try {
        const response = await fetch('/api/office-request-history');
        if (response.ok) {
            historyRequests.value = await response.json();
        } else {
            historyError.value = 'Failed to load history.';
        }
    } catch {
        historyError.value = 'Failed to load history.';
    }
    historyLoading.value = false;
};

const closeHistoryModal = () => {
    showHistoryModal.value = false;
    historyRequests.value = [];
    historyError.value = '';
};

const printRequest = () => {
    try {
        if (!selectedRequest.value) {
            console.error('No request selected for printing');
            return;
        }
        
        if (!selectedRequest.value.purchase_cart || !selectedRequest.value.purchase_cart.items) {
            console.error('No purchase cart items found');
            return;
        }
        
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        if (!printWindow) {
            alert('Please allow pop-ups to print the request');
            return;
        }
        
        const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Purchase Request - ${selectedRequest.value.user?.name || 'Unknown'}</title>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 20px; 
                        line-height: 1.6;
                    }
                    .header { 
                        text-align: center; 
                        margin-bottom: 30px; 
                        border-bottom: 2px solid #1f2937;
                        padding-bottom: 20px;
                    }
                    .header h1 { 
                        color: #1f2937; 
                        margin-bottom: 10px; 
                        font-size: 24px;
                    }
                    .info { 
                        margin-bottom: 20px; 
                        background-color: #f9fafb;
                        padding: 15px;
                        border-radius: 5px;
                    }
                    .info span { 
                        font-weight: bold; 
                        color: #374151;
                    }
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-bottom: 20px; 
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    }
                    th, td { 
                        border: 1px solid #d1d5db; 
                        padding: 12px 8px; 
                        text-align: left; 
                    }
                    th { 
                        background-color: #f3f4f6; 
                        font-weight: bold; 
                        color: #374151;
                    }
                    .total { 
                        font-weight: bold; 
                        background-color: #f3f4f6; 
                        color: #1f2937;
                    }
                    .footer { 
                        margin-top: 30px; 
                        text-align: center; 
                        font-size: 12px; 
                        color: #6b7280; 
                        border-top: 1px solid #e5e7eb;
                        padding-top: 15px;
                    }
                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                        table { box-shadow: none; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Purchase Request</h1>
                    <p><strong>Resource Link Management System</strong></p>
                </div>
                
                <div class="info">
                    <p><span>Requested by:</span> ${selectedRequest.value.user?.name || 'Unknown'}</p>
                    <p><span>Request Date:</span> ${new Date(selectedRequest.value.created_at).toLocaleDateString()}</p>
                    <p><span>Status:</span> ${selectedRequest.value.purchase_cart?.status || 'Pending'}</p>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Unit</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${selectedRequest.value.purchase_cart.items.map(item => `
                            <tr>
                                <td>${item.unit || ''}</td>
                                <td>${item.description || ''}</td>
                                <td>${item.quantity || 0}</td>
                                <td>₱ ${parseFloat(item.price || 0).toFixed(2)}</td>
                                <td>₱ ${(parseFloat(item.price || 0) * parseFloat(item.quantity || 0)).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                        <tr class="total">
                            <td colspan="4" style="text-align: right;"><strong>Grand Total:</strong></td>
                            <td><strong>₱ ${selectedRequest.value.purchase_cart.items.reduce((sum, item) => sum + (parseFloat(item.price || 0) * parseFloat(item.quantity || 0)), 0).toFixed(2)}</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="footer">
                    <p>Generated on ${new Date().toLocaleString()}</p>
                    <p>Resource Link Management System - Purchase Request</p>
                </div>
            </body>
            </html>
        `;
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        
        // Wait for content to load before printing
        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            // Close window after printing (with a small delay)
            setTimeout(() => {
                printWindow.close();
            }, 1000);
        };
        
        // Fallback if onload doesn't work
        setTimeout(() => {
            if (!printWindow.closed) {
                printWindow.focus();
                printWindow.print();
                setTimeout(() => {
                    printWindow.close();
                }, 1000);
            }
        }, 500);
        
    } catch (error) {
        console.error('Error printing request:', error);
        alert('Error printing request. Please try again.');
    }
};
</script>

<template>
    <Head title="Office Request" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                            Office Requests
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">Manage and approve purchase requests</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="px-3 py-1 bg-blue-50 border border-blue-200 rounded-full">
                        <span class="text-sm font-medium text-blue-700">{{ pendingRequests.length }} Pending</span>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header Actions -->
                <div class="mb-8 flex justify-between items-center">
                    <div class="flex space-x-4">
                        <div class="flex items-center space-x-2 text-sm text-gray-600">
                            <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                            <span>Live updates enabled</span>
                        </div>
                    </div>
                    <button
                        @click="openHistoryModal"
                        class="group relative overflow-hidden bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white px-6 py-3 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
                    >
                        <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                        <div class="relative flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="font-medium">View History</span>
                        </div>
                    </button>
                </div>

                <!-- Main Table Card -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800">Pending Requests</h3>
                        <p class="text-sm text-gray-600 mt-1">Review and approve office purchase requests</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <tr>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>Requestor</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span>Purchase Request</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                                            </svg>
                                            <span>Request Date</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            <span>Actions</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr
                                    v-for="(req, index) in pendingRequests"
                                    :key="req.id"
                                    class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-indigo-50/50 transition-all duration-300"
                                    :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
                                >
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                                                {{ req.user?.name?.charAt(0) || '?' }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ req.user?.name }}</p>
                                                <p class="text-sm text-gray-500">Staff Member</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <button
                                            @click="openModal(req.id)"
                                            class="group relative overflow-hidden bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 hover:shadow-lg"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500"></div>
                                            <div class="relative flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span class="font-medium">View Details</span>
                                            </div>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                            <span class="text-gray-700 font-medium">
                                                {{ new Date(req.created_at).toLocaleDateString() }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <button
                                            @click="approveRequest(req)"
                                            :disabled="req.purchase_cart?.status === 'approved'"
                                            class="relative px-5 py-2 rounded-lg font-medium shadow-md transition-all duration-300 transform hover:scale-105"
                                            :class="req.purchase_cart?.status === 'approved'
                                                ? 'bg-gray-100 text-gray-500 cursor-not-allowed border border-gray-200'
                                                : 'bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white shadow-lg hover:shadow-xl'"
                                        >
                                            <div v-if="req.purchase_cart?.status === 'approved'" class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                <span>Approved</span>
                                            </div>
                                            <div v-else class="flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>Approve</span>
                                            </div>
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="pendingRequests.length === 0">
                                    <td colspan="4" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-4">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-lg font-medium text-gray-900">No pending requests</p>
                                                <p class="text-gray-500">All requests have been processed</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden border border-gray-100 transform animate-in zoom-in-95 duration-300">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Purchase Request Details</h3>
                                <p class="text-gray-600 mt-1">Complete request information and items</p>
                            </div>
                        </div>
                        <button
                            @click="closeModal"
                            class="p-2 hover:bg-white/80 rounded-xl transition-colors duration-200 group"
                        >
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Content -->
                <div class="p-8 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div v-if="modalLoading" class="flex items-center justify-center py-12">
                        <div class="flex items-center space-x-3">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                            <span class="text-gray-600 font-medium">Loading request details...</span>
                        </div>
                    </div>
                    
                    <div v-else-if="modalError" class="text-center py-12">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-red-600 font-medium">{{ modalError }}</p>
                    </div>
                    
                    <div v-else-if="selectedRequest" class="space-y-8">
                        <!-- Request Info -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 border border-blue-200">
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="p-2 bg-blue-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Requested by</h4>
                                </div>
                                <p class="text-lg font-bold text-blue-700">{{ selectedRequest.user?.name }}</p>
                            </div>
                            
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 border border-green-200">
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="p-2 bg-green-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Request Date</h4>
                                </div>
                                <p class="text-lg font-bold text-green-700">
                                    {{ new Date(selectedRequest.created_at).toLocaleDateString() }}
                                </p>
                            </div>
                            
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 border border-purple-200">
                                <div class="flex items-center space-x-3 mb-3">
                                    <div class="p-2 bg-purple-500 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">Status</h4>
                                </div>
                                <p class="text-lg font-bold text-purple-700 capitalize">
                                    {{ selectedRequest.purchase_cart?.status || 'Pending' }}
                                </p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200">
                            <div class="flex items-center justify-between mb-6">
                                <h4 class="text-xl font-bold text-gray-900">Requested Items</h4>
                                <div class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                                    {{ selectedRequest.purchase_cart.items.length }} Items
                                </div>
                            </div>
                            
                            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                                <table class="w-full bg-white">
                                    <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                        <tr>
                                            <th class="py-4 px-4 text-left font-semibold text-gray-700 text-sm">Unit</th>
                                            <th class="py-4 px-4 text-left font-semibold text-gray-700 text-sm">Description</th>
                                            <th class="py-4 px-4 text-left font-semibold text-gray-700 text-sm">Quantity</th>
                                            <th class="py-4 px-4 text-left font-semibold text-gray-700 text-sm">Unit Price</th>
                                            <th class="py-4 px-4 text-left font-semibold text-gray-700 text-sm">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <tr
                                            v-for="(item, index) in selectedRequest.purchase_cart.items"
                                            :key="item.id"
                                            class="hover:bg-blue-50/50 transition-colors duration-200"
                                            :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/50'"
                                        >
                                            <td class="py-4 px-4 font-medium text-gray-900">{{ item.unit }}</td>
                                            <td class="py-4 px-4 text-gray-700">{{ item.description }}</td>
                                            <td class="py-4 px-4">
                                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-lg font-medium">
                                                    {{ item.quantity }}
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 font-mono text-green-600 font-semibold">₱ {{ item.price }}</td>
                                            <td class="py-4 px-4 font-mono text-green-600 font-bold">₱ {{ (item.price * item.quantity).toFixed(2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-200">
                                            <td colspan="4" class="py-4 px-4 text-right font-bold text-gray-900 text-lg">Grand Total:</td>
                                            <td class="py-4 px-4 font-mono text-2xl font-bold text-green-600">
                                                ₱ {{
                                                    selectedRequest.purchase_cart.items
                                                        .reduce((sum, item) => sum + (item.price * item.quantity), 0)
                                                        .toFixed(2)
                                                }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Print Button -->
                        <div class="flex justify-end pt-6 border-t border-gray-200">
                            <button
                                @click="printRequest"
                                class="group relative overflow-hidden bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl shadow-lg transition-all duration-300 transform hover:scale-105 hover:shadow-xl"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                                <div class="relative flex items-center space-x-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    <span class="font-semibold">Print Request</span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced History Modal -->
        <div
            v-if="showHistoryModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 animate-in fade-in duration-200"
        >
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] overflow-hidden border border-gray-100 transform animate-in zoom-in-95 duration-300">
                <!-- History Modal Header -->
                <div class="bg-gradient-to-r from-slate-50 to-gray-50 px-8 py-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Request History</h3>
                                <p class="text-gray-600 mt-1">Complete history of all office requests</p>
                            </div>
                        </div>
                        <button
                            @click="closeHistoryModal"
                            class="p-2 hover:bg-white/80 rounded-xl transition-colors duration-200 group"
                        >
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- History Modal Content -->
                <div class="p-8 overflow-y-auto max-h-[calc(90vh-140px)]">
                    <div v-if="historyLoading" class="flex items-center justify-center py-12">
                        <div class="flex items-center space-x-3">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-500"></div>
                            <span class="text-gray-600 font-medium">Loading request history...</span>
                        </div>
                    </div>
                    
                    <div v-else-if="historyError" class="text-center py-12">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-red-600 font-medium">{{ historyError }}</p>
                    </div>
                    
                    <div v-else class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm">
                        <table class="w-full bg-white">
                            <thead class="bg-gradient-to-r from-slate-50 to-gray-100">
                                <tr>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            <span>Requestor</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span>Purchase Request</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                                            </svg>
                                            <span>Request Date</span>
                                        </div>
                                    </th>
                                    <th class="py-4 px-6 text-left font-semibold text-gray-700 tracking-wider uppercase text-xs">
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Status</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr
                                    v-for="(req, index) in historyRequests"
                                    :key="req.id"
                                    class="group hover:bg-gradient-to-r hover:from-slate-50/50 hover:to-gray-50/50 transition-all duration-300"
                                    :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50/30'"
                                >
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-br from-slate-500 to-slate-600 rounded-full flex items-center justify-center text-white font-semibold text-sm shadow-lg">
                                                {{ req.user?.name?.charAt(0) || '?' }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ req.user?.name }}</p>
                                                <p class="text-sm text-gray-500">Staff Member</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <button
                                            @click="openModal(req.id)"
                                            class="group relative overflow-hidden bg-gradient-to-r from-slate-500 to-slate-600 hover:from-slate-600 hover:to-slate-700 text-white px-4 py-2 rounded-lg shadow-md transition-all duration-300 transform hover:scale-105 hover:shadow-lg"
                                        >
                                            <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500"></div>
                                            <div class="relative flex items-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span class="font-medium">View</span>
                                            </div>
                                        </button>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-2 h-2 bg-slate-400 rounded-full"></div>
                                            <span class="text-gray-700 font-medium">
                                                {{ new Date(req.created_at).toLocaleDateString() }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium shadow-sm"
                                             :class="{
                                                'bg-green-100 text-green-800 border border-green-200': req.purchase_cart?.status === 'approved',
                                                'bg-yellow-100 text-yellow-800 border border-yellow-200': req.purchase_cart?.status === 'pending',
                                                'bg-red-100 text-red-800 border border-red-200': req.purchase_cart?.status === 'rejected',
                                                'bg-gray-100 text-gray-800 border border-gray-200': !req.purchase_cart?.status
                                             }"
                                        >
                                            <div class="w-2 h-2 rounded-full mr-2"
                                                 :class="{
                                                    'bg-green-500': req.purchase_cart?.status === 'approved',
                                                    'bg-yellow-500': req.purchase_cart?.status === 'pending',
                                                    'bg-red-500': req.purchase_cart?.status === 'rejected',
                                                    'bg-gray-500': !req.purchase_cart?.status
                                                 }"
                                            ></div>
                                            <span class="capitalize">{{ req.purchase_cart?.status || 'Unknown' }}</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="historyRequests.length === 0">
                                    <td colspan="4" class="text-center py-12">
                                        <div class="flex flex-col items-center space-y-4">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-lg font-medium text-gray-900">No history found</p>
                                                <p class="text-gray-500">No requests have been processed yet</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>