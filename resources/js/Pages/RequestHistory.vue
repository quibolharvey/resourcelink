<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const requests = page.props.requests || [];

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
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                Request History
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-md rounded-lg border border-gray-200">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-blue-50 text-black">
                            <tr>
                                <th class="py-3 px-4 font-semibold uppercase text-sm">Name</th>
                                <th class="py-3 px-4 font-semibold uppercase text-sm">Purchase Request</th>
                                <th class="py-3 px-4 font-semibold uppercase text-sm">Request Date</th>
                                <th class="py-3 px-4 font-semibold uppercase text-sm">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="req in requests"
                                :key="req.id"
                                class="hover:bg-blue-50 transition-colors even:bg-gray-50"
                            >
                                <td class="py-3 px-4 italic text-gray-800">{{ req.user?.name }}</td>
                                <td class="py-3 px-4">
                                    <button
                                        @click="openModal(req.id)"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1 rounded-md shadow-sm transition"
                                    >
                                        View
                                    </button>
                                </td>
                                <td class="py-3 px-4 text-gray-700">
                                    {{ new Date(req.created_at).toLocaleDateString() }}
                                </td>
                                <td class="py-3 px-4">
                                    <span
                                        class="px-3 py-1 text-xs font-semibold rounded-full"
                                        :class="{
                                            'bg-green-100 text-green-700': req.purchase_cart?.status === 'Approved',
                                            'bg-yellow-100 text-yellow-700': req.purchase_cart?.status === 'Pending',
                                            'bg-red-100 text-red-700': req.purchase_cart?.status === 'Rejected',
                                            'bg-gray-200 text-gray-700': !req.purchase_cart?.status
                                        }"
                                    >
                                        {{ req.purchase_cart?.status || 'Unknown' }}
                                    </span>
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
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-lg relative shadow-xl">
                <button
                    @click="closeModal"
                    class="absolute top-3 right-3 text-gray-500 hover:text-black text-2xl leading-none"
                >
                    &times;
                </button>

                <h3 class="text-xl font-bold mb-4 border-b pb-2">Purchase Request Details</h3>

                <div v-if="modalLoading" class="text-gray-500 text-center py-4">
                    Loading...
                </div>

                <div v-else-if="modalError" class="text-red-500 text-center py-4">
                    {{ modalError }}
                </div>

                <div v-else-if="selectedRequest">
                    <div class="mb-3">
                        <span class="font-semibold">Requested by:</span>
                        {{ selectedRequest.user?.name }}
                    </div>
                    <div class="mb-3">
                        <span class="font-semibold">Request Date:</span>
                        {{ new Date(selectedRequest.created_at).toLocaleDateString() }}
                    </div>

                    <div class="mb-3 font-semibold">Items:</div>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="w-full text-left">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 px-3 font-bold">Unit</th>
                                    <th class="py-2 px-3 font-bold">Description</th>
                                    <th class="py-2 px-3 font-bold">Quantity</th>
                                    <th class="py-2 px-3 font-bold">Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in selectedRequest.purchase_cart.items"
                                    :key="item.id"
                                    class="even:bg-gray-50"
                                >
                                    <td class="py-2 px-3">{{ item.unit }}</td>
                                    <td class="py-2 px-3">{{ item.description }}</td>
                                    <td class="py-2 px-3">{{ item.quantity }}</td>
                                    <td class="py-2 px-3">₱ {{ item.price }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
