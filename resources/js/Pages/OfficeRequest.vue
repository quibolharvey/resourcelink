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
