<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const requests = ref(page.props.requests || []);

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
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
              📋 Office Request
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-4">
                <div class="flex justify-end">
                    <button
                        @click="openHistoryModal"
                        class="bg-gray-600 hover:bg-gray-700 transition-colors text-white px-5 py-2 rounded-lg shadow-sm"
                    >
                        📜 History
                    </button>
                </div>

                <div class="overflow-hidden bg-white shadow-md sm:rounded-lg border border-gray-200">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-gray-700">NAME</th>
                                <th class="py-3 px-4 font-semibold text-gray-700">PURCHASE REQUEST</th>
                                <th class="py-3 px-4 font-semibold text-gray-700">REQUEST DATE</th>
                                <th class="py-3 px-4 font-semibold text-gray-700">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="req in requests"
                                :key="req.id"
                                class="hover:bg-gray-50 transition-colors"
                            >
                                <td class="py-3 px-4 italic">{{ req.user?.name }}</td>
                                <td class="py-3 px-4">
                                    <button
                                        @click="openModal(req.id)"
                                        class="bg-blue-500 hover:bg-blue-600 transition-colors text-white px-4 py-1 rounded-lg shadow-sm"
                                    >
                                        View
                                    </button>
                                </td>
                                <td class="py-3 px-4">{{ new Date(req.created_at).toLocaleDateString() }}</td>
                                <td class="py-3 px-4">
                                    <button
                                        @click="approveRequest(req)"
                                        :disabled="req.purchase_cart?.status === 'approved'"
                                        class="px-4 py-1 rounded-lg text-white shadow-sm transition-colors"
                                        :class="req.purchase_cart?.status === 'approved'
                                            ? 'bg-gray-400 cursor-not-allowed'
                                            : 'bg-green-500 hover:bg-green-600'"
                                    >
                                        {{ req.purchase_cart?.status === 'approved' ? 'Approved' : 'Approve' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0">
                                <td colspan="4" class="text-center py-6 text-gray-500">
                                    No requests found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl p-6 w-full max-w-lg shadow-lg relative">
                <button
                    @click="closeModal"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl"
                >
                    &times;
                </button>
                <h3 class="text-xl font-bold mb-4 text-gray-800">Purchase Request Details</h3>
                <div v-if="modalLoading" class="text-gray-500">Loading...</div>
                <div v-else-if="modalError" class="text-red-500">{{ modalError }}</div>
                <div v-else-if="selectedRequest">
                    <div class="mb-3">
                        <span class="font-semibold">Requested by:</span> {{ selectedRequest.user?.name }}
                    </div>
                    <div class="mb-3">
                        <span class="font-semibold">Request Date:</span>
                        {{ new Date(selectedRequest.created_at).toLocaleDateString() }}
                    </div>
                    <div class="mb-3 font-semibold">Items:</div>
                    <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-2 px-3 font-semibold">Unit</th>
                                <th class="py-2 px-3 font-semibold">Description</th>
                                <th class="py-2 px-3 font-semibold">Quantity</th>
                                <th class="py-2 px-3 font-semibold">Unit Price</th>
                                <th class="py-2 px-3 font-semibold">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in selectedRequest.purchase_cart.items"
                                :key="item.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="py-2 px-3">{{ item.unit }}</td>
                                <td class="py-2 px-3">{{ item.description }}</td>
                                <td class="py-2 px-3">{{ item.quantity }}</td>
                                <td class="py-2 px-3">₱ {{ item.price }}</td>
                                <td class="py-2 px-3">₱ {{ (item.price * item.quantity).toFixed(2) }}</td>
                            </tr>
                            <tr class="bg-gray-100 font-bold">
                                <td colspan="4" class="text-right py-2 px-3">Grand Total:</td>
                                <td class="py-2 px-3">
                                    ₱ {{
                                        selectedRequest.purchase_cart.items
                                            .reduce((sum, item) => sum + (item.price * item.quantity), 0)
                                            .toFixed(2)
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <!-- Print Button -->
                    <div class="flex justify-end mt-6">
                        <button
                            @click="printRequest"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-sm transition-colors flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Print Request
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Modal -->
        <div
            v-if="showHistoryModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        >
            <div class="bg-white rounded-xl p-6 w-full max-w-4xl shadow-lg relative">
                <button
                    @click="closeHistoryModal"
                    class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl"
                >
                    &times;
                </button>
                <h3 class="text-xl font-bold mb-4 text-gray-800">Request History</h3>
                <div v-if="historyLoading" class="text-gray-500">Loading...</div>
                <div v-else-if="historyError" class="text-red-500">{{ historyError }}</div>
                <div v-else>
                    <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="py-3 px-4 font-semibold">NAME</th>
                                <th class="py-3 px-4 font-semibold">PURCHASE REQUEST</th>
                                <th class="py-3 px-4 font-semibold">REQUEST DATE</th>
                                <th class="py-3 px-4 font-semibold">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="req in historyRequests"
                                :key="req.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="py-3 px-4 italic">{{ req.user?.name }}</td>
                                <td class="py-3 px-4">
                                    <button
                                        @click="openModal(req.id)"
                                        class="bg-blue-500 hover:bg-blue-600 transition-colors text-white px-4 py-1 rounded-lg shadow-sm"
                                    >
                                        View
                                    </button>
                                </td>
                                <td class="py-3 px-4">{{ new Date(req.created_at).toLocaleDateString() }}</td>
                                <td class="py-3 px-4 capitalize">{{ req.purchase_cart?.status || 'Unknown' }}</td>
                            </tr>
                            <tr v-if="historyRequests.length === 0">
                                <td colspan="4" class="text-center py-6 text-gray-500">
                                    No requests found.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
