<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const mostExpensiveUsers = ref(page.props.mostExpensiveUsers || []);
const mostRequestingUsers = ref(page.props.mostRequestingUsers || []);
const statistics = ref(page.props.statistics || {});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(amount || 0);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                📊 Dashboard Analytics
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div v-for="(stat, index) in [
                        { label: 'Total Requests', value: statistics.totalRequests, color: 'text-gray-900' },
                        { label: 'Total Cost', value: formatCurrency(statistics.totalCost), color: 'text-green-600' },
                        { label: 'Unique Users', value: statistics.uniqueUsers, color: 'text-blue-600' },
                        { label: 'Avg Cost/Request', value: formatCurrency(statistics.averageCostPerRequest), color: 'text-purple-600' }
                    ]" :key="index" class="bg-white shadow-md rounded-lg p-6 border border-gray-100">
                        <p class="text-sm font-medium text-gray-500">{{ stat.label }}</p>
                        <p class="text-2xl font-bold mt-1" :class="stat.color">{{ stat.value || 0 }}</p>
                    </div>
                </div>

                <!-- Analytics Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    
                    <!-- Most Expensive Users -->
                    <div class="bg-white shadow-md rounded-lg border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">💰 Most Expensive Requests</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Cost</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requests</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(user, index) in mostExpensiveUsers" :key="index" class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center font-bold text-blue-600">
                                                    {{ user.user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900">{{ user.user.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ user.user.email }}</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-green-600">{{ formatCurrency(user.total_cost) }}</td>
                                            <td class="px-6 py-4">{{ user.request_count }}</td>
                                        </tr>
                                        <tr v-if="mostExpensiveUsers.length === 0">
                                            <td colspan="3" class="text-center text-gray-500 py-6">No data available</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Most Requesting Users -->
                    <div class="bg-white shadow-md rounded-lg border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">📥 Most Active Requesters</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Requests</th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr v-for="(user, index) in mostRequestingUsers" :key="index" class="hover:bg-gray-50 transition">
                                            <td class="px-6 py-4 flex items-center">
                                                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center font-bold text-green-600">
                                                    {{ user.user.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900">{{ user.user.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ user.user.email }}</p>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-blue-600">{{ user.request_count }}</td>
                                            <td class="px-6 py-4">{{ formatCurrency(user.total_cost) }}</td>
                                        </tr>
                                        <tr v-if="mostRequestingUsers.length === 0">
                                            <td colspan="3" class="text-center text-gray-500 py-6">No data available</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-8 bg-white shadow-md rounded-lg border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">📌 Analytics Summary</h3>
                        <div class="space-y-2 text-gray-700 text-sm">
                            <p><strong>Most Expensive User:</strong> 
                                <span v-if="mostExpensiveUsers.length">{{ mostExpensiveUsers[0].user.name }} ({{ formatCurrency(mostExpensiveUsers[0].total_cost) }})</span>
                                <span v-else>No data available</span>
                            </p>
                            <p><strong>Most Active Requester:</strong> 
                                <span v-if="mostRequestingUsers.length">{{ mostRequestingUsers[0].user.name }} ({{ mostRequestingUsers[0].request_count }} requests)</span>
                                <span v-else>No data available</span>
                            </p>
                            <p><strong>Average Cost per Request:</strong> {{ formatCurrency(statistics.averageCostPerRequest) }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
