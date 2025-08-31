<script setup>
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const isOffice = computed(() => page.props.auth.role === 'office');
const isAdmin = computed(() => page.props.auth.role === 'admin');
const isUser = computed(() => page.props.auth.role === 'user');

const showingNavigationDropdown = ref(false);
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out z-50',
        showingNavigationDropdown ? 'translate-x-0' : '-translate-x-full',
        'sm:translate-x-0' // always visible on larger screens
      ]"
    >
      <div class="flex items-center justify-between px-4 py-4 border-b">
        <Link href="/" class="flex items-center gap-2">
          <ApplicationLogo class="h-9 w-auto text-gray-800" />
          <span class="font-semibold text-gray-800">Calape ResourceLink</span>
        </Link>
        <!-- Close button on mobile -->
        <button
          @click="showingNavigationDropdown = false"
          class="sm:hidden p-2 text-gray-500 hover:text-gray-700 focus:outline-none"
        >
          ✕
        </button>
      </div>

      <nav class="mt-4 px-2 space-y-1">
        <!-- Admin Links -->
        <template v-if="isAdmin">
          <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
            Dashboard
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('itemmanagement')" :active="route().current('itemmanagement')">
            Item Management
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('borrowrequest')" :active="route().current('borrowrequest')">
            Borrowing Request
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('borrowhistory')" :active="route().current('borrowhistory')">
            Borrowing History
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('officeinventory')" :active="route().current('officeinventory')">
            Office Inventory
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('officerequest')" :active="route().current('officerequest')">
            Office Request
          </ResponsiveNavLink>
        </template>

        <!-- User Links -->
        <template v-if="isUser">
          <ResponsiveNavLink :href="route('items')" :active="route().current('items')">
            Items
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('borroweditem')" :active="route().current('borroweditem')">
            Borrowed Items
          </ResponsiveNavLink>
        </template>

        <!-- Office Links -->
        <template v-if="isOffice">
          <ResponsiveNavLink :href="route('inventory')" :active="route().current('inventory')">
            Inventory
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('purchaserequest')" :active="route().current('purchaserequest')">
            Purchase Request
          </ResponsiveNavLink>
          <ResponsiveNavLink :href="route('requesthistory')" :active="route().current('requesthistory')">
            Request History
          </ResponsiveNavLink>
        </template>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
      <!-- Top Nav -->
      <nav class="bg-white border-b border-gray-200 px-4 py-3 sm:ml-64">
        <div class="flex justify-between items-center">
          <!-- Left: Hamburger (mobile only) -->
          <button
            @click="showingNavigationDropdown = true"
            class="sm:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 focus:outline-none"
          >
            ☰
          </button>

          <!-- Right: Account dropdown -->
          <div class="flex-1 flex justify-end">
            <Dropdown align="right" width="48">
              <template #trigger>
                <button
                  type="button"
                  class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                >
                  {{ $page.props.auth.user.name }}
                  <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </template>
              <template #content>
                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
              </template>
            </Dropdown>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white shadow" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 sm:ml-64 p-4">
        <slot />
      </main>
    </div>
  </div>
</template>
