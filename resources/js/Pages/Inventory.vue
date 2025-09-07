<script setup>
import { ref, watchEffect, computed } from 'vue';
import { Head, usePage, router, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// --- Modal Visibility States ---
const showAddModal = ref(false);
const showPullOutModal = ref(false);
const showEditModal = ref(false);
const showRequestModal = ref(false);

// --- Form Data and useForm Instances ---

// Add Item Form
const addForm = useForm({
    unit: '',
    description: '',
    quantity: null,
    price: null,
});

// Pull Out Item Form
const pullOutForm = useForm({
    description: '',
    quantity: null,
});

// Edit Item Form
const editForm = useForm({
    id: null,
    unit: '',
    description: '',
    quantity: null,
    price: null,
});

// Request Item Form
const requestItem = ref({});
const requestForm = useForm({
    inventory_item_id: null,
    unit: '',
    description: '',
    quantity: null,
    price: null,
});

// --- Page Props and Filters ---
const page = usePage();
const items = ref(page.props.items || []);
const searchTerm = ref('');

// Keep items in sync with backend props
watchEffect(() => {
    if (page.props.items) {
        items.value = page.props.items;
    }
});

const filteredItems = computed(() => {
    if (!searchTerm.value) return items.value;
    return items.value.filter(item =>
        item.unit.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
        item.description.toLowerCase().includes(searchTerm.value.toLowerCase())
    );
});

// --- Pull Out Modal Suggestions ---
const showPullOutSuggestions = ref(false);
const pullOutSuggestions = computed(() => {
    if (!pullOutForm.description) return [];
    return items.value.filter(item =>
        item.description.toLowerCase().includes(pullOutForm.description.toLowerCase())
    ).slice(0, 5);
});

const selectPullOutSuggestion = (item) => {
    pullOutForm.description = item.description;
    showPullOutSuggestions.value = false;
};

const handlePullOutBlur = () => {
    setTimeout(() => {
        showPullOutSuggestions.value = false;
    }, 200);
};

// --- Actions (using useForm) ---

// Add item to inventory
const addItem = () => {
    addForm.post('/inventory', {
        onSuccess: () => {
            showAddModal.value = false;
            addForm.reset();
        },
        onError: (errors) => {
            console.error('Add item errors:', errors);
        },
    });
};

// Pull out item
const pullOut = () => {
    const item = items.value.find(i => i.description.toLowerCase() === pullOutForm.description.toLowerCase());

    if (!item) {
        alert('Item not found for pull out.');
        return;
    }

    pullOutForm.patch(`/inventory/${item.id}/pullout`, {
        onSuccess: () => {
            showPullOutModal.value = false;
            pullOutForm.reset();
        },
        onError: (errors) => {
            console.error('Pull out errors:', errors);
            alert(errors.quantity || 'Failed to pull out item.');
        },
    });
};

// Open Edit Modal
const openEditModal = (item) => {
    editForm.id = item.id;
    editForm.unit = item.unit;
    editForm.description = item.description;
    editForm.quantity = item.quantity;
    editForm.price = item.price;
    showEditModal.value = true;
};

// Update Item
const updateItem = () => {
    editForm.patch(`/inventory/${editForm.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
        onError: (errors) => {
            console.error('Update item errors:', errors);
        },
    });
};

// Open Request Modal
const openRequestModal = (item) => {
    requestItem.value = { ...item };
    requestForm.inventory_item_id = item.id;
    requestForm.unit = item.unit;
    requestForm.description = item.description;
    requestForm.quantity = 1;
    requestForm.price = item.price;

    showRequestModal.value = true;
};

// Submit Request (Add to Cart)
const submitRequest = async () => {
    try {
        const response = await fetch('/api/purchase-cart/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                inventory_item_id: requestForm.inventory_item_id,
                unit: requestForm.unit,
                description: requestForm.description,
                quantity: requestQuantity.value,
                price: requestForm.price,
            }),
        });

        if (response.ok) {
            showRequestModal.value = false;
            requestForm.reset();
            requestQuantity.value = 1;
            requestSuccess.value = true;
            requestError.value = '';
            setTimeout(() => requestSuccess.value = false, 3000);
        } else {
            const errorData = await response.json();
            requestError.value = errorData.message || 'Failed to add item to cart.';
            setTimeout(() => requestError.value = '', 5000);
        }
    } catch (error) {
        console.error('Request item errors:', error);
        requestError.value = 'Failed to add item to cart.';
        setTimeout(() => requestError.value = '', 5000);
    }
};

// Existing Go to Purchase Request
const goToPurchaseRequest = () => {
    router.visit('/purchaserequest');
};

// --- Local Notification States ---
const requestSuccess = ref(false);
const requestError = ref('');
const requestQuantity = ref(1);
</script>

<template>
    <Head title="Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Office Items Inventory</h2>
                    <p class="text-sm text-gray-600 mt-1">Manage your office supplies and equipment</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Enhanced Header Section -->
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 mb-8 border border-gray-200">
                    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                        <!-- Search Section -->
                        <div class="w-full lg:w-80">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    placeholder="Search by item description or unit..." 
                                    v-model="searchTerm"
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                                />
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex flex-wrap gap-3 w-full lg:w-auto">
                            <button 
                                @click="showAddModal = true"
                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add Item
                            </button>
                            <button 
                                @click="showPullOutModal = true"
                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                                Pull Out Item
                            </button>
                            <button 
                                @click="goToPurchaseRequest"
                                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
                            >
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                Purchase Requests
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Table -->
                <div class="bg-white rounded-xl shadow-xl border border-gray-200 overflow-hidden">
                    <!-- Table Header -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Inventory Items</h3>
                            <div class="text-sm text-gray-600">
                                Total Items: <span class="font-semibold text-gray-900">{{ filteredItems.length }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Stock No.
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Unit
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Item Description
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Quantity
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Unit Price
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="(item, index) in filteredItems" :key="item.id" 
                                    class="hover:bg-gray-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-blue-800 text-sm font-medium">{{ index + 1 }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ item.unit }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ item.description }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <span class="text-sm font-semibold" :class="{
                                                'text-red-600': item.quantity < 10,
                                                'text-yellow-600': item.quantity >= 10 && item.quantity < 50,
                                                'text-green-600': item.quantity >= 50
                                            }">
                                                {{ item.quantity }}
                                            </span>
                                            <div class="ml-2 h-2 w-16 bg-gray-200 rounded-full">
                                                <div class="h-2 rounded-full" :class="{
                                                    'bg-red-500': item.quantity < 10,
                                                    'bg-yellow-500': item.quantity >= 10 && item.quantity < 50,
                                                    'bg-green-500': item.quantity >= 50
                                                }" :style="`width: ${Math.min(100, (item.quantity / 100) * 100)}%`"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900">₱{{ item.price?.toLocaleString() }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button 
                                                @click="openEditModal(item)"
                                                class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-100 transition-colors duration-150"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                Edit
                                            </button>
                                            <button 
                                                @click="openRequestModal(item)"
                                                class="inline-flex items-center px-3 py-1.5 bg-green-50 text-green-700 text-sm font-medium rounded-lg hover:bg-green-100 transition-colors duration-150"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                                Request
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td colspan="6" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                            <p class="text-lg font-medium text-gray-900">No items found</p>
                                            <p class="text-gray-500 mt-1">Try adjusting your search criteria</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Enhanced Add Modal -->
            <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
                    <form @submit.prevent="addItem" class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Add New Item</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="add-unit" class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
                                <input 
                                    v-model="addForm.unit" 
                                    type="text" 
                                    id="add-unit"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': addForm.errors.unit }" 
                                    placeholder="e.g., pcs, box, pack"
                                />
                                <div v-if="addForm.errors.unit" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ addForm.errors.unit }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="add-description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <input 
                                    v-model="addForm.description" 
                                    type="text" 
                                    id="add-description"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': addForm.errors.description }" 
                                    placeholder="Item description"
                                />
                                <div v-if="addForm.errors.description" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ addForm.errors.description }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="add-quantity" class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                                <input 
                                    v-model.number="addForm.quantity" 
                                    type="number" 
                                    id="add-quantity"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': addForm.errors.quantity }" 
                                    placeholder="0"
                                    min="0"
                                />
                                <div v-if="addForm.errors.quantity" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ addForm.errors.quantity }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="add-price" class="block text-sm font-semibold text-gray-700 mb-2">Unit Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input 
                                        v-model.number="addForm.price" 
                                        type="number" 
                                        id="add-price"
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500 ring-2 ring-red-200': addForm.errors.price }" 
                                        placeholder="0.00"
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                                <div v-if="addForm.errors.price" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ addForm.errors.price }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="showAddModal = false"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-lg"
                                :disabled="addForm.processing"
                            >
                                <span v-if="addForm.processing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Saving...
                                </span>
                                <span v-else>Save Item</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Pull Out Modal -->
            <div v-if="showPullOutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
                    <form @submit.prevent="pullOut" class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="p-2 bg-red-100 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Pull Out Item</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="relative">
                                <label for="pullout-description" class="block text-sm font-semibold text-gray-700 mb-2">Item Description</label>
                                <input 
                                    v-model="pullOutForm.description" 
                                    type="text" 
                                    id="pullout-description"
                                    @focus="showPullOutSuggestions = true"
                                    @blur="handlePullOutBlur"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': pullOutForm.errors.description }" 
                                    placeholder="Search for items to pull out..."
                                />
                                
                                <!-- Enhanced Suggestions Dropdown -->
                                <div v-if="showPullOutSuggestions && pullOutSuggestions.length > 0" 
                                     class="absolute z-10 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-xl max-h-60 overflow-auto">
                                    <div v-for="item in pullOutSuggestions" 
                                         :key="item.id"
                                         @mousedown="selectPullOutSuggestion(item)"
                                         class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0 transition-colors">
                                        <div class="font-medium text-gray-900">{{ item.description }}</div>
                                        <div class="text-sm text-gray-500 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-blue-100 text-blue-800 mr-2">
                                                {{ item.unit }}
                                            </span>
                                            Available: {{ item.quantity }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="pullOutForm.errors.description" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ pullOutForm.errors.description }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="pullout-quantity" class="block text-sm font-semibold text-gray-700 mb-2">Quantity to Pull Out</label>
                                <input 
                                    v-model.number="pullOutForm.quantity" 
                                    type="number" 
                                    id="pullout-quantity"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': pullOutForm.errors.quantity }" 
                                    placeholder="0"
                                    min="1"
                                />
                                <div v-if="pullOutForm.errors.quantity" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ pullOutForm.errors.quantity }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="showPullOutModal = false"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 shadow-lg"
                                :disabled="pullOutForm.processing"
                            >
                                <span v-if="pullOutForm.processing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Pulling Out...
                                </span>
                                <span v-else>Pull Out</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Edit Modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
                    <form @submit.prevent="updateItem" class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="p-2 bg-blue-100 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Edit Item</h2>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="edit-unit" class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
                                <input 
                                    v-model="editForm.unit" 
                                    type="text" 
                                    id="edit-unit"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': editForm.errors.unit }" 
                                />
                                <div v-if="editForm.errors.unit" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ editForm.errors.unit }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="edit-description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                <input 
                                    v-model="editForm.description" 
                                    type="text" 
                                    id="edit-description"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': editForm.errors.description }" 
                                />
                                <div v-if="editForm.errors.description" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ editForm.errors.description }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="edit-quantity" class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                                <input 
                                    v-model.number="editForm.quantity" 
                                    type="number" 
                                    id="edit-quantity"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    :class="{ 'border-red-500 ring-2 ring-red-200': editForm.errors.quantity }" 
                                    min="0"
                                />
                                <div v-if="editForm.errors.quantity" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ editForm.errors.quantity }}
                                </div>
                            </div>
                            
                            <div>
                                <label for="edit-price" class="block text-sm font-semibold text-gray-700 mb-2">Unit Price</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">₱</span>
                                    </div>
                                    <input 
                                        v-model.number="editForm.price" 
                                        type="number" 
                                        id="edit-price"
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                        :class="{ 'border-red-500 ring-2 ring-red-200': editForm.errors.price }" 
                                        step="0.01"
                                        min="0"
                                    />
                                </div>
                                <div v-if="editForm.errors.price" class="text-red-500 text-sm mt-1 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ editForm.errors.price }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="showEditModal = false"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg"
                                :disabled="editForm.processing"
                            >
                                <span v-if="editForm.processing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Updating...
                                </span>
                                <span v-else>Update Item</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Request Modal -->
            <div v-if="showRequestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm">
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all">
                    <form @submit.prevent="submitRequest" class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="p-2 bg-green-100 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900">Request Item</h2>
                        </div>
                        
                        <!-- Item Details Card -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 mb-6 border border-gray-200">
                            <div class="space-y-2">
                                <div class="flex justify-between items-start">
                                    <span class="text-sm font-semibold text-gray-700">Description:</span>
                                    <span class="text-sm text-gray-900 text-right">{{ requestItem.description }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-700">Available:</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="{
                                        'bg-red-100 text-red-800': requestItem.quantity < 10,
                                        'bg-yellow-100 text-yellow-800': requestItem.quantity >= 10 && requestItem.quantity < 50,
                                        'bg-green-100 text-green-800': requestItem.quantity >= 50
                                    }">
                                        {{ requestItem.quantity }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-semibold text-gray-700">Unit Price:</span>
                                    <span class="text-sm text-gray-900">₱{{ requestItem.price?.toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="request-quantity" class="block text-sm font-semibold text-gray-700 mb-2">Quantity to Request</label>
                            <input 
                                v-model.number="requestQuantity" 
                                type="number" 
                                id="request-quantity" 
                                min="1"
                                :max="requestItem.quantity"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                :class="{ 'border-red-500 ring-2 ring-red-200': requestForm.errors.quantity }" 
                                placeholder="1"
                            />
                            <div v-if="requestForm.errors.quantity" class="text-red-500 text-sm mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ requestForm.errors.quantity }}
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Total Cost: <span class="font-semibold text-gray-900">₱{{ (requestQuantity * requestItem.price)?.toLocaleString() || '0' }}</span>
                            </p>
                        </div>
                        
                        <div class="mt-8 flex justify-end space-x-3">
                            <button 
                                type="button" 
                                @click="showRequestModal = false"
                                class="px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 shadow-lg"
                                :disabled="requestForm.processing"
                            >
                                <span v-if="requestForm.processing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Adding to Cart...
                                </span>
                                <span v-else>Add to Cart</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Enhanced Success/Error Notifications -->
            <div v-if="requestSuccess" class="fixed top-4 right-4 z-50 transform transition-all duration-300">
                <div class="bg-white rounded-lg shadow-xl border border-green-200 p-4 max-w-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="p-2 bg-green-100 rounded-full">
                                <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-semibold text-green-800">Success!</p>
                            <p class="text-sm text-green-700 mt-1">Item has been added to your cart.</p>
                        </div>
                        <button @click="requestSuccess = false" class="ml-4 text-green-400 hover:text-green-500">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <div v-if="requestError" class="fixed top-4 right-4 z-50 transform transition-all duration-300">
                <div class="bg-white rounded-lg shadow-xl border border-red-200 p-4 max-w-sm">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="p-2 bg-red-100 rounded-full">
                                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-semibold text-red-800">Error!</p>
                            <p class="text-sm text-red-700 mt-1">{{ requestError }}</p>
                        </div>
                        <button @click="requestError = ''" class="ml-4 text-red-400 hover:text-red-500">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>