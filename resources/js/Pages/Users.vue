<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, defineProps, ref } from 'vue';

const props = defineProps({
  offices: Array,
  users: Array,
});

const page = usePage();
const isAdmin = computed(() => page.props.auth.role === 'admin');

const search = ref('');
const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredUsers = computed(() => {
  if (!normalizedSearch.value) return props.users;
  return props.users.filter(u => (u.name || '').toLowerCase().includes(normalizedSearch.value));
});

const filteredOffices = computed(() => {
  if (!normalizedSearch.value) return props.offices;
  return props.offices.filter(u => (u.name || '').toLowerCase().includes(normalizedSearch.value));
});

const deleteUser = (id) => {
  if (!isAdmin.value) return;
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(route('users.destroy', id));
  }
};
</script>

<template>
  <Head title="Users" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0V10M7 20v-6h10v6M7 10h10" />
          </svg>
        </div>
        <div>
          <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
            Users
          </h2>
          <p class="text-sm text-gray-500 mt-1">Manage application users</p>
        </div>
      </div>
    </template>

    <div v-if="isAdmin" class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Search -->
        <div class="mb-6">
          <div class="relative max-w-md">
            <input
              v-model="search"
              type="text"
              class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-gray-900 placeholder:text-gray-400"
              placeholder="Search by name..."
            />
            <svg class="w-5 h-5 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 110-16 8 8 0 010 16z" />
            </svg>
          </div>
        </div>
        <!-- User Accounts (on top) -->
        <div class="overflow-hidden bg-white shadow-sm border border-gray-100 rounded-2xl mb-8">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">User Accounts</h3>
            <span class="text-sm text-gray-500">{{ filteredUsers.length }} total</span>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                  <th class="px-6 py-3" />
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="h-9 w-9 rounded-lg bg-green-600 text-white flex items-center justify-center mr-3">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <div class="text-xs text-gray-500">Joined {{ new Date(user.created_at).toLocaleDateString() }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.phone_number }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.address }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <button
                      @click="deleteUser(user.id)"
                      class="inline-flex items-center px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 hover:text-red-800 font-medium rounded-xl border border-red-200 transition-colors duration-200"
                    >
                      <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Office Accounts -->
        <div class="overflow-hidden bg-white shadow-sm border border-gray-100 rounded-2xl">
          <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Office Accounts</h3>
            <span class="text-sm text-gray-500">{{ filteredOffices.length }} total</span>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Address</th>
                  <th class="px-6 py-3" />
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <tr v-for="user in filteredOffices" :key="user.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="h-9 w-9 rounded-lg bg-purple-600 text-white flex items-center justify-center mr-3">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </div>
                      <div>
                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <div class="text-xs text-gray-500">Joined {{ new Date(user.created_at).toLocaleDateString() }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.phone_number }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ user.address }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                    <span class="inline-flex items-center px-3 py-2 text-gray-400 bg-gray-50 border border-gray-200 rounded-xl cursor-not-allowed">Protected</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="py-16 text-center">
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Unauthorized</h3>
      <p class="text-gray-600">This page is only accessible to admins.</p>
    </div>
  </AuthenticatedLayout>
  
</template>


