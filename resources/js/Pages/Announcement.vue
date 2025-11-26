<script setup>
import { ref, computed, nextTick } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- Props ---
const props = defineProps({
    announcements: Object,
    stats: Object,
});

// --- Modal States ---
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const showViewModal = ref(false);
const selectedAnnouncement = ref(null);

// --- Textarea Refs ---
const createMessageTextarea = ref(null);
const editMessageTextarea = ref(null);

// --- Form States ---
const createForm = useForm({
    title: '',
    message: '',
    priority: 'medium',
    status: 'draft',
    target_roles: [],
    expires_at: '',
    is_pinned: false,
});

const editForm = useForm({
    title: '',
    message: '',
    priority: 'medium',
    status: 'draft',
    target_roles: [],
    expires_at: '',
    is_pinned: false,
});

// --- Computed Properties ---
const priorityColors = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-blue-100 text-blue-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800',
};

const statusColors = {
    draft: 'bg-yellow-100 text-yellow-800',
    published: 'bg-green-100 text-green-800',
    archived: 'bg-gray-100 text-gray-800',
};

// --- Actions ---
const openCreateModal = () => {
    createForm.reset();
    showCreateModal.value = true;
};

const openEditModal = (announcement) => {
    selectedAnnouncement.value = announcement;
    editForm.title = announcement.title;
    editForm.message = announcement.message;
    editForm.priority = announcement.priority;
    editForm.status = announcement.status;
    editForm.target_roles = announcement.target_roles || [];
    editForm.expires_at = announcement.expires_at ? announcement.expires_at.split('T')[0] : '';
    editForm.is_pinned = announcement.is_pinned;
    showEditModal.value = true;
    // Auto-resize textarea after modal opens and content is loaded
    nextTick(() => {
        setTimeout(() => {
            if (editMessageTextarea.value) {
                autoResizeTextarea(editMessageTextarea.value);
            }
        }, 100);
    });
};

const openDeleteModal = (announcement) => {
    selectedAnnouncement.value = announcement;
    showDeleteModal.value = true;
};

const openViewModal = (announcement) => {
    selectedAnnouncement.value = announcement;
    showViewModal.value = true;
};

const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showDeleteModal.value = false;
    showViewModal.value = false;
    selectedAnnouncement.value = null;
};

const openEditFromView = () => {
    const announcement = selectedAnnouncement.value;
    showViewModal.value = false;
    nextTick(() => {
        if (announcement) {
            openEditModal(announcement);
        }
    });
};

const createAnnouncement = () => {
    createForm.post('/announcements', {
        onSuccess: () => {
            closeModals();
        },
    });
};

const updateAnnouncement = () => {
    editForm.put(`/announcements/${selectedAnnouncement.value.id}`, {
        onSuccess: () => {
            closeModals();
        },
    });
};

const deleteAnnouncement = () => {
    router.delete(`/announcements/${selectedAnnouncement.value.id}`, {
        onSuccess: () => {
            closeModals();
        },
    });
};

const togglePin = (announcement) => {
    router.patch(`/announcements/${announcement.id}/pin`);
};

const publishAnnouncement = (announcement) => {
    router.patch(`/announcements/${announcement.id}/publish`);
};

const archiveAnnouncement = (announcement) => {
    router.patch(`/announcements/${announcement.id}/archive`);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Auto-resize textarea function
const autoResizeTextarea = (textarea) => {
    if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = textarea.scrollHeight + 'px';
    }
};

// Handle textarea input for create form
const handleCreateMessageInput = (event) => {
    autoResizeTextarea(event.target);
};

// Handle textarea input for edit form
const handleEditMessageInput = (event) => {
    autoResizeTextarea(event.target);
};
</script>

<template>
    <Head title="Announcement Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Announcement Dashboard</h2>
                        <p class="text-sm text-gray-600 mt-1">Manage system-wide announcements and messages</p>
                    </div>
                </div>
                <button 
                    @click="openCreateModal"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    New Announcement
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Published</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.published }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Drafts</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.draft }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-gray-100 rounded-lg">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6 6-6"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Archived</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.archived }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                        <div class="flex items-center">
                            <div class="p-3 bg-red-100 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Urgent</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.urgent }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Announcements List -->
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">All Announcements</h3>
                    </div>

                    <div class="divide-y divide-gray-200">
                        <div 
                            v-for="announcement in announcements.data" 
                            :key="announcement.id"
                            class="p-6 hover:bg-gray-50 transition-colors duration-200 cursor-pointer"
                            @click.stop="openViewModal(announcement)"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <h4 class="text-lg font-semibold text-gray-900">{{ announcement.title }}</h4>
                                        <span 
                                            v-if="announcement.is_pinned"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Pinned
                                        </span>
                                        <span 
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                priorityColors[announcement.priority]
                                            ]"
                                        >
                                            {{ announcement.priority.toUpperCase() }}
                                        </span>
                                        <span 
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                statusColors[announcement.status]
                                            ]"
                                        >
                                            {{ announcement.status.toUpperCase() }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-gray-700 mb-3">{{ announcement.message }}</p>
                                    
                                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ announcement.user.name }}
                                        </div>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ formatDate(announcement.created_at) }}
                                        </div>
                                        <div v-if="announcement.target_roles && announcement.target_roles.length > 0" class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            {{ announcement.target_roles.join(', ') }}
                                        </div>
                                        <div v-if="announcement.expires_at" class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Expires: {{ formatDate(announcement.expires_at) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2 ml-4" @click.stop>
                                    <button 
                                        @click="togglePin(announcement)"
                                        :class="[
                                            'p-2 rounded-lg transition-colors duration-200',
                                            announcement.is_pinned 
                                                ? 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200' 
                                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                        ]"
                                        :title="announcement.is_pinned ? 'Unpin' : 'Pin'"
                                    >
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>

                                    <button 
                                        v-if="announcement.status === 'draft'"
                                        @click="publishAnnouncement(announcement)"
                                        class="p-2 bg-green-100 text-green-600 hover:bg-green-200 rounded-lg transition-colors duration-200"
                                        title="Publish"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </button>

                                    <button 
                                        v-if="announcement.status === 'published'"
                                        @click="archiveAnnouncement(announcement)"
                                        class="p-2 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-lg transition-colors duration-200"
                                        title="Archive"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6 6-6"/>
                                        </svg>
                                    </button>

                                    <button 
                                        @click="openEditModal(announcement)"
                                        class="p-2 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded-lg transition-colors duration-200"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <button 
                                        @click="openDeleteModal(announcement)"
                                        class="p-2 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition-colors duration-200"
                                        title="Delete"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="announcements.data.length === 0" class="p-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-900">No announcements yet</p>
                                <p class="text-gray-500 mt-1">Create your first announcement to get started</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="announcements.last_page > 1" class="bg-white px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center text-sm text-gray-700">
                                <span>Showing {{ announcements.from }} to {{ announcements.to }} of {{ announcements.total }} results</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <template v-for="link in announcements.links" :key="link.label">
                                    <button 
                                        v-if="link.url"
                                        @click="router.visit(link.url)"
                                        :class="[
                                            'px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-150',
                                            link.active 
                                                ? 'bg-purple-600 text-white' 
                                                : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
                                        ]"
                                        v-html="link.label"
                                    ></button>
                                    <span v-else class="px-3 py-2 text-sm text-gray-500" v-html="link.label"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <form @submit.prevent="createAnnouncement" class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-purple-100 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Create New Announcement</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                            <input 
                                v-model="createForm.title" 
                                type="text" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                :class="{ 'border-red-500 ring-2 ring-red-200': createForm.errors.title }" 
                                placeholder="Enter announcement title"
                            />
                            <div v-if="createForm.errors.title" class="text-red-500 text-sm mt-1">{{ createForm.errors.title }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                            <textarea 
                                ref="createMessageTextarea"
                                v-model="createForm.message" 
                                @input="handleCreateMessageInput"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 resize-none overflow-hidden"
                                :class="{ 'border-red-500 ring-2 ring-red-200': createForm.errors.message }" 
                                placeholder="Enter announcement message"
                                style="min-height: 100px;"
                            ></textarea>
                            <div v-if="createForm.errors.message" class="text-red-500 text-sm mt-1">{{ createForm.errors.message }}</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                                <select 
                                    v-model="createForm.priority" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                <select 
                                    v-model="createForm.status" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Target Roles</label>
                            <div class="space-y-2">
                                <!-- <label class="flex items-center">
                                    <input type="checkbox" v-model="createForm.target_roles" value="admin" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Admin</span>
                                </label> -->
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="createForm.target_roles" value="office" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">Office</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="createForm.target_roles" value="user" class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                    <span class="ml-2 text-sm text-gray-700">User</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Expiration Date (Optional)</label>
                            <input 
                                v-model="createForm.expires_at" 
                                type="datetime-local" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200"
                            />
                        </div> -->
                        
                        <div>
                            <label class="flex items-center">
                                <input 
                                    v-model="createForm.is_pinned" 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                >
                                <span class="ml-2 text-sm font-semibold text-gray-700">Pin this announcement</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="closeModals"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 shadow-lg"
                            :disabled="createForm.processing"
                        >
                            <span v-if="createForm.processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Creating...
                            </span>
                            <span v-else>Create Announcement</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <form @submit.prevent="updateAnnouncement" class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="p-2 bg-blue-100 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Edit Announcement</h2>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                            <input 
                                v-model="editForm.title" 
                                type="text" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                :class="{ 'border-red-500 ring-2 ring-red-200': editForm.errors.title }" 
                            />
                            <div v-if="editForm.errors.title" class="text-red-500 text-sm mt-1">{{ editForm.errors.title }}</div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Message</label>
                            <textarea 
                                ref="editMessageTextarea"
                                v-model="editForm.message" 
                                @input="handleEditMessageInput"
                                rows="4"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none overflow-hidden"
                                :class="{ 'border-red-500 ring-2 ring-red-200': editForm.errors.message }" 
                                style="min-height: 100px;"
                            ></textarea>
                            <div v-if="editForm.errors.message" class="text-red-500 text-sm mt-1">{{ editForm.errors.message }}</div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                                <select 
                                    v-model="editForm.priority" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                                <select 
                                    v-model="editForm.status" 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                >
                                    <option value="draft">Draft</option>
                                    <option value="published">Published</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Target Roles (Optional)</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="editForm.target_roles" value="admin" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Admin</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="editForm.target_roles" value="office" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Office</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" v-model="editForm.target_roles" value="user" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">User</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Expiration Date (Optional)</label>
                            <input 
                                v-model="editForm.expires_at" 
                                type="datetime-local" 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                            />
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input 
                                    v-model="editForm.is_pinned" 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span class="ml-2 text-sm font-semibold text-gray-700">Pin this announcement</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-end space-x-3">
                        <button 
                            type="button" 
                            @click="closeModals"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg"
                            :disabled="editForm.processing"
                        >
                            <span v-if="editForm.processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Updating...
                            </span>
                            <span v-else>Update Announcement</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Modal -->
        <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm" @click.self="closeModals">
            <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="p-2 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Announcement Details</h2>
                            </div>
                        </div>
                        <button 
                            @click="closeModals"
                            class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Announcement Content -->
                    <div v-if="selectedAnnouncement" class="space-y-6">
                        <!-- Title and Badges -->
                        <div>
                            <div class="flex items-center flex-wrap gap-3 mb-4">
                                <h3 class="text-3xl font-bold text-gray-900">{{ selectedAnnouncement.title }}</h3>
                                <span 
                                    v-if="selectedAnnouncement.is_pinned"
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    Pinned
                                </span>
                                <span 
                                    :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                        priorityColors[selectedAnnouncement.priority]
                                    ]"
                                >
                                    {{ selectedAnnouncement.priority.toUpperCase() }}
                                </span>
                                <span 
                                    :class="[
                                        'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                        statusColors[selectedAnnouncement.status]
                                    ]"
                                >
                                    {{ selectedAnnouncement.status.toUpperCase() }}
                                </span>
                            </div>
                        </div>

                        <!-- Message Content -->
                        <div class="bg-gray-50 rounded-xl p-6 border border-gray-200">
                            <p class="text-gray-700 text-lg leading-relaxed whitespace-pre-wrap">{{ selectedAnnouncement.message }}</p>
                        </div>

                        <!-- Metadata -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-blue-900">Created By</span>
                                </div>
                                <p class="text-blue-700">{{ selectedAnnouncement.user?.name || 'N/A' }}</p>
                            </div>

                            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-green-900">Created At</span>
                                </div>
                                <p class="text-green-700">{{ formatDate(selectedAnnouncement.created_at) }}</p>
                            </div>

                            <div v-if="selectedAnnouncement.target_roles && selectedAnnouncement.target_roles.length > 0" class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-purple-900">Target Roles</span>
                                </div>
                                <p class="text-purple-700">{{ selectedAnnouncement.target_roles.join(', ') }}</p>
                            </div>

                            <div v-if="selectedAnnouncement.expires_at" class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                                <div class="flex items-center space-x-2 mb-2">
                                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm font-semibold text-orange-900">Expires At</span>
                                </div>
                                <p class="text-orange-700">{{ formatDate(selectedAnnouncement.expires_at) }}</p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button 
                                @click="closeModals"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200"
                            >
                                Close
                            </button>
                            <button 
                                @click="openEditFromView"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg"
                            >
                                Edit Announcement
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-red-100 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-900">Delete Announcement</h2>
                    </div>
                    
                    <p class="text-gray-700 mb-6">Are you sure you want to delete "{{ selectedAnnouncement?.title }}"? This action cannot be undone.</p>
                    
                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="closeModals"
                            class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="deleteAnnouncement"
                            class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-lg"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

