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
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 rounded-2xl shadow-xl p-8 text-white">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-white/20 backdrop-blur-sm rounded-xl">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold leading-tight">
                            Dashboard Analytics
                        </h2>
                        <p class="text-white/80 mt-1">Monitor your API usage and costs</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                    <div v-for="(stat, index) in [
                        { 
                            label: 'Total Requests', 
                            value: statistics.totalRequests, 
                            color: 'from-blue-500 to-blue-600',
                            icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
                        },
                        { 
                            label: 'Total Cost', 
                            value: formatCurrency(statistics.totalCost), 
                            color: 'from-green-500 to-green-600',
                            icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
                        },
                        { 
                            label: 'Unique Users', 
                            value: statistics.uniqueUsers, 
                            color: 'from-purple-500 to-purple-600',
                            icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z'
                        },
                        { 
                            label: 'Avg Cost/Request', 
                            value: formatCurrency(statistics.averageCostPerRequest), 
                            color: 'from-orange-500 to-orange-600',
                            icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'
                        }
                    ]" :key="index" class="group">
                        <div class="relative overflow-hidden bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                            <div class="absolute inset-0 bg-gradient-to-br opacity-5 group-hover:opacity-10 transition-opacity" :class="stat.color"></div>
                            <div class="p-6 relative">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 mb-1">{{ stat.label }}</p>
                                        <p class="text-2xl font-bold text-gray-900">{{ stat.value || 0 }}</p>
                                    </div>
                                    <div class="p-3 rounded-xl bg-gradient-to-br shadow-sm" :class="stat.color">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tables -->
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-8">
                    
                    <!-- Most Expensive Users -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-5 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-green-100 rounded-lg">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Most Expensive Requests</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Cost</th>
                                            <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Requests</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="(user, index) in mostExpensiveUsers" :key="index" class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                            <td class="py-4 flex items-center">
                                                <div class="relative">
                                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-green-400 to-green-600 flex items-center justify-center font-bold text-white shadow-sm group-hover:shadow-md transition-shadow">
                                                        {{ user.user.name.charAt(0).toUpperCase() }}
                                                    </div>
                                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-100 rounded-full border-2 border-white flex items-center justify-center">
                                                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900">{{ user.user.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ user.user.email }}</p>
                                                </div>
                                            </td>
                                            <td class="py-4">
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold text-green-700 bg-green-100 rounded-full">
                                                    {{ formatCurrency(user.total_cost) }}
                                                </span>
                                            </td>
                                            <td class="py-4">
                                                <span class="text-sm font-medium text-gray-700">{{ user.request_count }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="mostExpensiveUsers.length === 0">
                                            <td colspan="3" class="text-center text-gray-500 py-12">
                                                <div class="flex flex-col items-center space-y-2">
                                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                    </svg>
                                                    <p class="text-sm">No data available</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Most Requesting Users -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-5 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-blue-100 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800">Most Active Requesters</h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr class="border-b border-gray-100">
                                            <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                                            <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Requests</th>
                                            <th class="pb-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Cost</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="(user, index) in mostRequestingUsers" :key="index" class="hover:bg-gray-50/50 transition-colors duration-200 group">
                                            <td class="py-4 flex items-center">
                                                <div class="relative">
                                                    <div class="h-12 w-12 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center font-bold text-white shadow-sm group-hover:shadow-md transition-shadow">
                                                        {{ user.user.name.charAt(0).toUpperCase() }}
                                                    </div>
                                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-blue-100 rounded-full border-2 border-white flex items-center justify-center">
                                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <p class="text-sm font-semibold text-gray-900">{{ user.user.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ user.user.email }}</p>
                                                </div>
                                            </td>
                                            <td class="py-4">
                                                <span class="inline-flex px-3 py-1 text-sm font-semibold text-blue-700 bg-blue-100 rounded-full">
                                                    {{ user.request_count }}
                                                </span>
                                            </td>
                                            <td class="py-4">
                                                <span class="text-sm font-medium text-gray-700">{{ formatCurrency(user.total_cost) }}</span>
                                            </td>
                                        </tr>
                                        <tr v-if="mostRequestingUsers.length === 0">
                                            <td colspan="3" class="text-center text-gray-500 py-12">
                                                <div class="flex flex-col items-center space-y-2">
                                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                    </svg>
                                                    <p class="text-sm">No data available</p>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-5 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">Analytics Summary</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-600">Most Expensive User</span>
                                </div>
                                <div class="pl-4">
                                    <p v-if="mostExpensiveUsers.length" class="text-sm text-gray-800">
                                        <span class="font-semibold">{{ mostExpensiveUsers[0].user.name }}</span>
                                        <span class="text-green-600 ml-2 font-medium">{{ formatCurrency(mostExpensiveUsers[0].total_cost) }}</span>
                                    </p>
                                    <p v-else class="text-sm text-gray-500">No data available</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-600">Most Active Requester</span>
                                </div>
                                <div class="pl-4">
                                    <p v-if="mostRequestingUsers.length" class="text-sm text-gray-800">
                                        <span class="font-semibold">{{ mostRequestingUsers[0].user.name }}</span>
                                        <span class="text-blue-600 ml-2 font-medium">{{ mostRequestingUsers[0].request_count }} requests</span>
                                    </p>
                                    <p v-else class="text-sm text-gray-500">No data available</p>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-orange-500 rounded-full"></div>
                                    <span class="text-sm font-medium text-gray-600">Average Cost per Request</span>
                                </div>
                                <div class="pl-4">
                                    <p class="text-sm font-semibold text-orange-600">{{ formatCurrency(statistics.averageCostPerRequest) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>