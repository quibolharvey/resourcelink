<script setup>
import { ref, computed, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';

const page = usePage();

const isOffice = computed(() => page.props.auth.role === 'office');
const isAdmin = computed(() => page.props.auth.role === 'admin');
const isUser = computed(() => page.props.auth.role === 'user');

const showingNavigationDropdown = ref(false);
const showingAnnouncements = ref(false);
const announcements = ref([]);
const announcementCount = ref(0);
const isLoading = ref(false);

// Announcement modal state
const showAnnouncementModal = ref(false);
const selectedAnnouncement = ref(null);

// Show bell icon only for users and office (not admin)
const showBellIcon = computed(() => isUser.value || isOffice.value);

// Fetch announcements
const fetchAnnouncements = async () => {
    try {
        isLoading.value = true;
        const response = await axios.get('/api/announcements');
        announcements.value = response.data.announcements;
        announcementCount.value = response.data.count;
    } catch (error) {
        console.error('Error fetching announcements:', error);
    } finally {
        isLoading.value = false;
    }
};

// Toggle announcements dropdown
const toggleAnnouncements = () => {
    showingAnnouncements.value = !showingAnnouncements.value;
    if (showingAnnouncements.value && announcements.value.length === 0) {
        fetchAnnouncements();
    }
};

// Open/close announcement modal
const openAnnouncementModal = (announcement) => {
    selectedAnnouncement.value = announcement;
    showAnnouncementModal.value = true;
    showingAnnouncements.value = false; // Close dropdown when opening modal
};

const closeAnnouncementModal = () => {
    showAnnouncementModal.value = false;
    selectedAnnouncement.value = null;
};

// Get priority color
const getPriorityColor = (priority) => {
    const colors = {
        urgent: 'text-red-600 bg-red-50',
        high: 'text-orange-600 bg-orange-50',
        medium: 'text-blue-600 bg-blue-50',
        low: 'text-gray-600 bg-gray-50'
    };
    return colors[priority] || colors.medium;
};

// Format date
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

onMounted(() => {
    // Fetch announcements count on mount for users and office
    if (showBellIcon.value) {
        fetchAnnouncements();
    }
});
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 flex">
    <!-- Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 w-64 bg-white/95 backdrop-blur-sm shadow-2xl border-r border-slate-200/50 transform transition-all duration-300 ease-out z-50',
        showingNavigationDropdown ? 'translate-x-0' : '-translate-x-full',
        'sm:translate-x-0' // always visible on larger screens
      ]"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-5 border-b border-slate-200/60 bg-gradient-to-r from-blue-600 to-indigo-600">
        <Link href="/" class="flex items-center gap-3 group">
          <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm group-hover:scale-105 transition-transform duration-200">
            <ApplicationLogo class="h-7 w-auto text-white" />
          </div>
          <div>
            <span class="font-bold text-white text-sm tracking-wide">Calape</span>
            <div class="text-blue-100 text-xs font-medium">ResourceLink</div>
          </div>
        </Link>
        <!-- Close button on mobile -->
        <button
          @click="showingNavigationDropdown = false"
          class="sm:hidden p-2 text-white/80 hover:text-white hover:bg-white/20 rounded-lg focus:outline-none transition-all duration-200"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="mt-6 px-4 space-y-2">
        <!-- Admin Links -->
        <template v-if="isAdmin">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 mb-4">
            Administration
          </div>
          <ResponsiveNavLink 
            :href="route('dashboard')" 
            :active="route().current('dashboard')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('dashboard') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
            </svg>
            Dashboard
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('itemmanagement')" 
            :active="route().current('itemmanagement')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('itemmanagement') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Item Management
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('borrowrequest')" 
            :active="route().current('borrowrequest')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('borrowrequest') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4V7a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v-2m0 0v-2m0 2h.01M12 21v-9" />
            </svg>
            Borrowing Request
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('borrowhistory')" 
            :active="route().current('borrowhistory')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('borrowhistory') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Borrowing History
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('officeinventory')" 
            :active="route().current('officeinventory')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('officeinventory') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            Office Inventory
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('officerequest')" 
            :active="route().current('officerequest')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('officerequest') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Office Request
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('announcement')" 
            :active="route().current('announcement')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('announcement') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            Announcements
          </ResponsiveNavLink>

          <ResponsiveNavLink 
            :href="route('users')" 
            :active="route().current('users')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-blue-50 hover:text-blue-700"
            :class="route().current('users') ? 'bg-blue-100 text-blue-700 shadow-sm border-l-4 border-blue-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0V10M7 20v-6h10v6M7 10h10" />
            </svg>
            Users
          </ResponsiveNavLink>
        </template>

        <!-- User Links -->
        <template v-if="isUser">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 mb-4">
            User Dashboard
          </div>
          <ResponsiveNavLink 
            :href="route('items')" 
            :active="route().current('items')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-green-50 hover:text-green-700"
            :class="route().current('items') ? 'bg-green-100 text-green-700 shadow-sm border-l-4 border-green-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Items
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('borroweditem')" 
            :active="route().current('borroweditem')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-green-50 hover:text-green-700"
            :class="route().current('borroweditem') ? 'bg-green-100 text-green-700 shadow-sm border-l-4 border-green-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            Borrowed Items
          </ResponsiveNavLink>
        </template>

        <!-- Office Links -->
        <template v-if="isOffice">
          <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider px-3 mb-4">
            Office Management
          </div>
          <ResponsiveNavLink 
            :href="route('inventory')" 
            :active="route().current('inventory')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-purple-50 hover:text-purple-700"
            :class="route().current('inventory') ? 'bg-purple-100 text-purple-700 shadow-sm border-l-4 border-purple-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
            Inventory
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('purchaserequest')" 
            :active="route().current('purchaserequest')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-purple-50 hover:text-purple-700"
            :class="route().current('purchaserequest') ? 'bg-purple-100 text-purple-700 shadow-sm border-l-4 border-purple-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 3H3m4 10v6a1 1 0 001 1h9a1 1 0 001-1v-6" />
            </svg>
            Purchase Request
          </ResponsiveNavLink>
          
          <ResponsiveNavLink 
            :href="route('requesthistory')" 
            :active="route().current('requesthistory')"
            class="group flex items-center px-3 py-3 text-sm font-medium rounded-xl transition-all duration-200 hover:bg-purple-50 hover:text-purple-700"
            :class="route().current('requesthistory') ? 'bg-purple-100 text-purple-700 shadow-sm border-l-4 border-purple-500' : 'text-slate-600 hover:translate-x-1'"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Request History
          </ResponsiveNavLink>
        </template>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen">
      <!-- Top Nav -->
      <nav class="bg-white/80 backdrop-blur-sm border-b border-slate-200/60 px-4 py-4 sm:ml-64 sticky top-0 z-40">
        <div class="flex justify-between items-center">
          <!-- Left: Hamburger (mobile only) -->
          <button
            @click="showingNavigationDropdown = true"
            class="sm:hidden p-2.5 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition-all duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>

          <!-- Right: Notifications and Account dropdown -->
          <div class="flex-1 flex justify-end items-center space-x-3">
            <!-- Bell Icon for Announcements (Users and Office only) -->
            <div v-if="showBellIcon" class="relative">
              <button
                @click="toggleAnnouncements"
                class="relative p-2.5 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none transition-all duration-200"
                :class="{ 'text-blue-600 bg-blue-50': showingAnnouncements }"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L15 7H4.828zM4.828 7H3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-1.172M4.828 7l2.586-2.586a2 2 0 012.828 0L15 7" />
                </svg>
                <!-- Notification Badge -->
                <span 
                  v-if="announcementCount > 0"
                  class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-medium"
                >
                  {{ announcementCount > 9 ? '9+' : announcementCount }}
                </span>
              </button>

              <!-- Announcements Dropdown -->
              <div 
                v-show="showingAnnouncements"
                class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-slate-200 z-50 max-h-96 overflow-y-auto"
              >
                <div class="p-4 border-b border-slate-100">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Announcements</h3>
                    <button 
                      @click="showingAnnouncements = false"
                      class="p-1 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>
                  </div>
                </div>

                <!-- Loading State -->
                <div v-if="isLoading" class="p-4 text-center">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600 mx-auto"></div>
                  <p class="text-sm text-slate-500 mt-2">Loading announcements...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="announcements.length === 0" class="p-4 text-center">
                  <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.828 7l2.586 2.586a2 2 0 002.828 0L15 7H4.828zM4.828 7H3a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-1.172M4.828 7l2.586-2.586a2 2 0 012.828 0L15 7" />
                  </svg>
                  <p class="text-sm text-slate-500">No announcements available</p>
                </div>

                <!-- Announcements List -->
                <div v-else class="max-h-64 overflow-y-auto">
                  <div 
                    v-for="announcement in announcements" 
                    :key="announcement.id"
                    class="p-4 border-b border-slate-100 hover:bg-slate-50 transition-colors duration-150 cursor-pointer"
                    @click="openAnnouncementModal(announcement)"
                  >
                    <div class="flex items-start justify-between mb-2">
                      <div class="flex items-center space-x-2">
                        <span 
                          :class="getPriorityColor(announcement.priority)"
                          class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                          {{ announcement.priority.toUpperCase() }}
                        </span>
                        <span v-if="announcement.is_pinned" class="text-yellow-500">
                          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                        </span>
                      </div>
                      <span class="text-xs text-slate-500">{{ formatDate(announcement.created_at) }}</span>
                    </div>
                    
                    <h4 class="font-semibold text-slate-800 mb-1">{{ announcement.title }}</h4>
                    <p class="text-sm text-slate-600 line-clamp-2">{{ announcement.message }}</p>
                    
                    <div class="flex items-center justify-between mt-2">
                      <span class="text-xs text-slate-500">By {{ announcement.user.name }}</span>
                      <span 
                        :class="announcement.status === 'published' ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-50'"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ announcement.status.toUpperCase() }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Footer -->
                <div v-if="announcements.length > 0" class="p-3 border-t border-slate-100 bg-slate-50 rounded-b-xl">
                  <p class="text-xs text-slate-500 text-center">
                    {{ announcementCount }} announcement{{ announcementCount !== 1 ? 's' : '' }} available
                  </p>
                </div>
              </div>
            </div>

            <!-- Account Dropdown -->
            <Dropdown align="right" width="48">
              <template #trigger>
                <button
                  type="button"
                  class="inline-flex items-center rounded-xl border border-slate-200 bg-white/70 backdrop-blur-sm px-4 py-2.5 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-white hover:shadow-md transition-all duration-200"
                >
                  <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                    <span class="text-white text-sm font-semibold">
                      {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                  {{ $page.props.auth.user.name }}
                  <svg class="ml-2 h-4 w-4 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </button>
              </template>
              <template #content>
                <div class="py-2">
                  <DropdownLink 
                    :href="route('profile.edit')"
                    class="flex items-center px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors duration-150"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                  </DropdownLink>
                  <div class="border-t border-slate-100 my-1"></div>
                  <DropdownLink 
                    :href="route('logout')" 
                    method="post" 
                    as="button"
                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150"
                  >
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log Out
                  </DropdownLink>
                </div>
              </template>
            </Dropdown>
          </div>
        </div>
      </nav>

      <!-- Page Heading -->
      <header class="bg-white/60 backdrop-blur-sm shadow-sm border-b border-slate-200/40 sm:ml-64" v-if="$slots.header">
        <div class="mx-auto max-w-7xl px-6 py-6 sm:px-8 lg:px-10">
          <slot name="header" />
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 sm:ml-64 p-6 lg:p-8">
        <div class="max-w-7xl mx-auto">
          <slot />
        </div>
      </main>
    </div>

    <!-- Mobile backdrop -->
    <div 
      v-show="showingNavigationDropdown"
      @click="showingNavigationDropdown = false"
      class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 sm:hidden transition-opacity duration-300"
    ></div>

    <!-- Announcement Readable Modal -->
    <div 
      v-if="showAnnouncementModal"
      class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
      @click.self="closeAnnouncementModal"
    >
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-blue-600 to-indigo-600">
          <div class="flex items-center justify-between">
            <h3 class="text-white text-xl font-semibold">Announcement</h3>
            <button @click="closeAnnouncementModal" class="text-white/90 hover:text-white">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div class="px-6 py-5 space-y-4">
          <div class="flex items-center flex-wrap gap-2">
            <h4 class="text-2xl font-bold text-slate-800">{{ selectedAnnouncement?.title }}</h4>
            <span v-if="selectedAnnouncement?.is_pinned" class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
              <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              Pinned
            </span>
            <span 
              v-if="selectedAnnouncement"
              :class="getPriorityColor(selectedAnnouncement.priority)"
              class="px-2.5 py-1 text-xs font-medium rounded-full"
            >
              {{ selectedAnnouncement.priority?.toUpperCase() }}
            </span>
            <span 
              v-if="selectedAnnouncement"
              :class="selectedAnnouncement.status === 'published' ? 'text-green-600 bg-green-50' : 'text-gray-600 bg-gray-50'"
              class="px-2.5 py-1 text-xs font-medium rounded-full"
            >
              {{ selectedAnnouncement.status?.toUpperCase() }}
            </span>
          </div>

          <div class="text-sm text-slate-500">
            <span>By {{ selectedAnnouncement?.user?.name }}</span>
            <span class="mx-2">•</span>
            <span>{{ formatDate(selectedAnnouncement?.created_at) }}</span>
          </div>

          <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
            <p class="text-slate-700 whitespace-pre-wrap leading-relaxed">{{ selectedAnnouncement?.message }}</p>
          </div>

          <div v-if="selectedAnnouncement?.target_roles?.length" class="text-sm text-slate-600">
            <span class="font-medium">Target Roles:</span>
            <span>{{ selectedAnnouncement.target_roles.join(', ') }}</span>
          </div>

          <div v-if="selectedAnnouncement?.expires_at" class="text-sm text-slate-600">
            <span class="font-medium">Expires:</span>
            <span>{{ formatDate(selectedAnnouncement.expires_at) }}</span>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end space-x-3">
          <button @click="closeAnnouncementModal" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-700 hover:bg-white">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>
