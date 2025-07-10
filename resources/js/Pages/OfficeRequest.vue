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
    } catch (e) {}
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
    } catch (e) {
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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Office Request
            </h2>
        </template>

        <div class="py-4">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="flex justify-end mb-2">
                    <button @click="openHistoryModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-1 rounded">History</button>
                </div>
                <div class="overflow-hidden bg-gray-200 shadow-sm sm:rounded-lg border-2 border-blue-400">
                    <table class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 font-bold">NAME</th>
                                <th class="py-2 px-4 font-bold">PURCHASE REQUEST</th>
                                <th class="py-2 px-4 font-bold">REQUEST DATE</th>
                                <th class="py-2 px-4 font-bold">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="req in requests" :key="req.id">
                                <td class="py-2 px-4 italic">{{ req.user?.name }}</td>
                                <td class="py-2 px-4">
                                    <button @click="openModal(req.id)" class="bg-blue-400 hover:bg-blue-500 text-white px-4 py-1 rounded">VIEW</button>
                                </td>
                                <td class="py-2 px-4">{{ new Date(req.created_at).toLocaleDateString() }}</td>
                                <td class="py-2 px-4">
                                    <button
                                        @click="approveRequest(req)"
                                        :disabled="req.purchase_cart?.status === 'approved'"
                                        class="px-3 py-1 rounded bg-green-500 text-white disabled:bg-gray-400"
                                    >
                                        {{ req.purchase_cart?.status === 'approved' ? 'Approved' : 'Approve' }}
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0">
                                <td colspan="4" class="text-center py-4 text-gray-500">No requests found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal for viewing purchase request details -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
                <button @click="closeModal" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>
                <h3 class="text-lg font-bold mb-2">Purchase Request Details</h3>
                <div v-if="modalLoading" class="text-gray-500">Loading...</div>
                <div v-else-if="modalError" class="text-red-500">{{ modalError }}</div>
                <div v-else-if="selectedRequest">
                    <div class="mb-2"><span class="font-semibold">Requested by:</span> {{ selectedRequest.user?.name }}</div>
                    <div class="mb-2"><span class="font-semibold">Request Date:</span> {{ new Date(selectedRequest.created_at).toLocaleDateString() }}</div>
                    <div class="mb-2"><span class="font-semibold">Items:</span></div>
                    <table class="w-full text-left mb-2">
                        <thead>
                            <tr>
                                <th class="py-1 font-bold">Unit</th>
                                <th class="py-1 font-bold">Description</th>
                                <th class="py-1 font-bold">Quantity</th>
                                <th class="py-1 font-bold">Unit Price</th>
                                <th class="py-1 font-bold">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in selectedRequest.purchase_cart.items" :key="item.id">
                                <td class="py-1">{{ item.unit }}</td>
                                <td class="py-1">{{ item.description }}</td>
                                <td class="py-1">{{ item.quantity }}</td>
                                <td class="py-1">₱ {{ item.price }}</td>
                                <td class="py-1">₱ {{ (item.price * item.quantity).toFixed(2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-right font-bold py-1">Grand Total:</td>
                                <td class="py-1 font-bold">
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
        <div v-if="showHistoryModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-3xl relative">
                <button @click="closeHistoryModal" class="absolute top-2 right-2 text-gray-500 hover:text-black">&times;</button>
                <h3 class="text-lg font-bold mb-2">Request History</h3>
                <div v-if="historyLoading" class="text-gray-500">Loading...</div>
                <div v-else-if="historyError" class="text-red-500">{{ historyError }}</div>
                <div v-else>
                    <table class="w-full text-left mb-2">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 font-bold">NAME</th>
                                <th class="py-2 px-4 font-bold">PURCHASE REQUEST</th>
                                <th class="py-2 px-4 font-bold">REQUEST DATE</th>
                                <th class="py-2 px-4 font-bold">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="req in historyRequests" :key="req.id">
                                <td class="py-2 px-4 italic">{{ req.user?.name }}</td>
                                <td class="py-2 px-4">
                                    <button @click="openModal(req.id)" class="bg-blue-400 hover:bg-blue-500 text-white px-4 py-1 rounded">VIEW</button>
                                </td>
                                <td class="py-2 px-4">{{ new Date(req.created_at).toLocaleDateString() }}</td>
                                <td class="py-2 px-4">{{ req.purchase_cart?.status || 'Unknown' }}</td>
                            </tr>
                            <tr v-if="historyRequests.length === 0">
                                <td colspan="4" class="text-center py-4 text-gray-500">No requests found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
