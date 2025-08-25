<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    borrowedHistories: {
        type: Array,
        default: () => []
    }
});

const statusOptions = ['accepted', 'returned', 'overdue'];

const updateStatus = (id, status) => {
    router.patch(route('borrowedhistory.update', id), { status });
};
</script>

<template>

    <Head title="Borrowed History" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                📜 Borrowed History
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-lg sm:rounded-xl">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                                <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Item</th>
                                        <th class="px-4 py-3 text-left">Qty</th>
                                        <th class="px-4 py-3 text-left">Return Date</th>
                                        <th class="px-4 py-3 text-left">Name</th>
                                        <th class="px-4 py-3 text-left">Email</th>
                                        <th class="px-4 py-3 text-left">Phone</th>
                                        <th class="px-4 py-3 text-left">Address</th>
                                        <th class="px-4 py-3 text-left">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr v-for="history in borrowedHistories" :key="history.id"
                                        class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 font-medium">{{ history.item?.name || 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ history.quantity }}</td>
                                        <td class="px-4 py-3">{{ history.expected_return_date }}</td>
                                        <td class="px-4 py-3">{{ history.user?.name || 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ history.user?.email || 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ history.user?.phone_number || 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ history.user?.address || 'N/A' }}</td>
                                        <td class="px-4 py-3">
                                            <select v-model="history.status"
                                                @change="updateStatus(history.id, history.status)"
                                                class="border-gray-300 rounded-lg px-3 py-1 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                                <option v-for="option in statusOptions" :key="option" :value="option">
                                                    {{ option.charAt(0).toUpperCase() + option.slice(1) }}
                                                </option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="borrowedHistories.length === 0" class="text-gray-500 text-center py-6">
                            No borrow history found.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
