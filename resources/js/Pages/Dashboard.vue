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
    }).format(amount);
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard Analytics
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total Requests</div>
                            <div class="text-2xl font-bold text-gray-900">{{ statistics.totalRequests }}</div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Total Cost</div>
                            <div class="text-2xl font-bold text-green-600">{{ formatCurrency(statistics.totalCost) }}</div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Unique Users</div>
                            <div class="text-2xl font-bold text-blue-600">{{ statistics.uniqueUsers }}</div>
                        </div>
                    </div>
                    
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500">Avg Cost/Request</div>
                            <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(statistics.averageCostPerRequest) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tables -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Most Expensive Users -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Expensive Requests</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(user, index) in mostExpensiveUsers" :key="index" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-blue-600">{{ user.user.name.charAt(0).toUpperCase() }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ user.user.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ user.user.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                                {{ formatCurrency(user.total_cost) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ user.request_count }}
                                            </td>
                                        </tr>
                                        <tr v-if="mostExpensiveUsers.length === 0">
                                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No data available</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Most Requesting Users -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Most Active Requesters</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requests</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(user, index) in mostRequestingUsers" :key="index" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-green-600">{{ user.user.name.charAt(0).toUpperCase() }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ user.user.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ user.user.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                                {{ user.request_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ formatCurrency(user.total_cost) }}
                                            </td>
                                        </tr>
                                        <tr v-if="mostRequestingUsers.length === 0">
                                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">No data available</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Analytics Summary</h3>
                        <div class="text-sm text-gray-600">
                            <p class="mb-2">• <strong>Most Expensive User:</strong> 
                                <span v-if="mostExpensiveUsers.length > 0">
                                    {{ mostExpensiveUsers[0].user.name }} with total cost of {{ formatCurrency(mostExpensiveUsers[0].total_cost) }}
                                </span>
                                <span v-else>No data available</span>
                            </p>
                            <p class="mb-2">• <strong>Most Active Requester:</strong> 
                                <span v-if="mostRequestingUsers.length > 0">
                                    {{ mostRequestingUsers[0].user.name }} with {{ mostRequestingUsers[0].request_count }} requests
                                </span>
                                <span v-else>No data available</span>
                            </p>
                            <p>• <strong>Average Cost per Request:</strong> {{ formatCurrency(statistics.averageCostPerRequest) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
