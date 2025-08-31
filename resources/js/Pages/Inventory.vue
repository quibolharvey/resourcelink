<script setup>
import { ref, watchEffect, computed } from 'vue'; // Removed onMounted as it's not strictly needed for the initial items load
import { Head, usePage, router, useForm } from '@inertiajs/vue3'; // Added useForm
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
    quantity: null, // Use null for numbers that can start empty
    price: null,
});

// Pull Out Item Form
const pullOutForm = useForm({
    description: '', // This will be the item's description for lookup
    quantity: null,
});

// Edit Item Form
const editForm = useForm({
    id: null, // Will be populated when opening the modal
    unit: '',
    description: '',
    quantity: null,
    price: null,
});

// Request Item Form
const requestItem = ref({}); // Still needed to store the selected item for display
const requestForm = useForm({
    inventory_item_id: null,
    unit: '',
    description: '',
    quantity: null, // This is the requested quantity
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
    ).slice(0, 5); // Limit to 5 suggestions
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
            showAddModal.value = false; // Close modal on success
            addForm.reset(); // Reset form fields
            // Inertia will automatically update the page props if the backend redirects/refreshes
        },
        onError: (errors) => {
            console.error('Add item errors:', errors);
            // form.errors will be automatically populated and displayed
        },
    });
};

// Pull out item
const pullOut = () => {
    // Find the item by description, as your original logic did
    const item = items.value.find(i => i.description.toLowerCase() === pullOutForm.description.toLowerCase());


    if (!item) {
        // Manually set an error if item is not found, as useForm doesn't have a direct way
        // to set a non-field-specific error without a backend response
        alert('Item not found for pull out.'); // Or use a reactive error state for better UX
        return;
    }

    pullOutForm.patch(`/inventory/${item.id}/pullout`, {
        onSuccess: () => {
            showPullOutModal.value = false;
            pullOutForm.reset();
        },
        onError: (errors) => {
            console.error('Pull out errors:', errors);
            alert(errors.quantity || 'Failed to pull out item.'); // Show specific error if available
        },
    });
};

// Open Edit Modal
const openEditModal = (item) => {
    // Populate the editForm with the item's current data
    editForm.id = item.id;
    editForm.unit = item.unit;
    editForm.description = item.description;
    editForm.quantity = item.quantity;
    editForm.price = item.price;
    showEditModal.value = true;
};

// Update Item
const updateItem = () => {
    // Use form.patch for update operations
    editForm.patch(`/inventory/${editForm.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset(); // Reset fields to initial state if desired, or let Inertia refresh
        },
        onError: (errors) => {
            console.error('Update item errors:', errors);
            // form.errors will be automatically populated
        },
    });
};

// Open Request Modal
const openRequestModal = (item) => {
    requestItem.value = { ...item }; // Store the selected item for display
    // Populate the requestForm for submission
    requestForm.inventory_item_id = item.id;
    requestForm.unit = item.unit;
    requestForm.description = item.description;
    requestForm.quantity = 1; // Default quantity
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

// --- Local Notification States (kept for existing logic) ---
const requestSuccess = ref(false);
const requestError = ref('');
const requestQuantity = ref(1); // Keep this ref for the modal input binding

</script>

<template>

    <Head title="Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Office Items Inventory
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="w-full sm:w-64">
                        <input type="text" placeholder="Search Item"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            v-model="searchTerm" />
                    </div>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <button @click="showAddModal = true"
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md shadow-sm flex items-center justify-center">
                            Add Item
                        </button>
                        <button @click="showPullOutModal = true"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md shadow-sm flex items-center justify-center">
                            Pull out Item
                        </button>
                        <button @click="goToPurchaseRequest"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm flex items-center justify-center">
                            Go to Purchase Request
                        </button>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Stock NO.</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        UNIT</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ITEM DESCRIPTION</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        QUANTITY</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        UNIT PRICE</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ACTION</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in filteredItems" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.description }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱ {{ item.price }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button @click="openEditModal(item)"
                                                class="text-blue-600 hover:text-blue-900">
                                                Edit
                                            </button>
                                            <button @click="openRequestModal(item)"
                                                class="text-gray-600 hover:text-gray-900">
                                                Request →
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No items
                                        available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="showAddModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form @submit.prevent="addItem" class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Item</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="add-unit" class="block text-sm font-medium text-gray-700">Unit</label>
                                <input v-model="addForm.unit" type="text" id="add-unit"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': addForm.errors.unit }" />
                                <div v-if="addForm.errors.unit" class="text-red-500 text-sm mt-1">{{ addForm.errors.unit
                                }}
                                </div>
                            </div>
                            <div>
                                <label for="add-description"
                                    class="block text-sm font-medium text-gray-700">Description</label>
                                <input v-model="addForm.description" type="text" id="add-description"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': addForm.errors.description }" />
                                <div v-if="addForm.errors.description" class="text-red-500 text-sm mt-1">{{
                                    addForm.errors.description }}</div>
                            </div>
                            <div>
                                <label for="add-quantity"
                                    class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input v-model.number="addForm.quantity" type="number" id="add-quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': addForm.errors.quantity }" />
                                <div v-if="addForm.errors.quantity" class="text-red-500 text-sm mt-1">{{
                                    addForm.errors.quantity
                                }}</div>
                            </div>
                            <div>
                                <label for="add-price" class="block text-sm font-medium text-gray-700">Unit
                                    Price</label>
                                <input v-model.number="addForm.price" type="number" id="add-price"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': addForm.errors.price }" />
                                <div v-if="addForm.errors.price" class="text-red-500 text-sm mt-1">{{
                                    addForm.errors.price }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showAddModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :disabled="addForm.processing">
                                {{ addForm.processing ? 'Saving...' : 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showPullOutModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form @submit.prevent="pullOut" class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Pull Out Item</h2>
                        <div class="space-y-4">
                            <div class="relative">
                                <label for="pullout-description" class="block text-sm font-medium text-gray-700">Item
                                    Description</label>
                                <input 
                                    v-model="pullOutForm.description" 
                                    type="text" 
                                    id="pullout-description"
                                    @focus="showPullOutSuggestions = true"
                                    @blur="handlePullOutBlur"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': pullOutForm.errors.description }" 
                                    placeholder="Search items..." />
                                
                                <!-- Suggestions Dropdown -->
                                <div v-if="showPullOutSuggestions && pullOutSuggestions.length > 0" 
                                     class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                                    <div v-for="item in pullOutSuggestions" 
                                         :key="item.id"
                                         @mousedown="selectPullOutSuggestion(item)"
                                         class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                        <div class="font-medium text-gray-900">{{ item.description }}</div>
                                        <div class="text-sm text-gray-500">
                                            Available: {{ item.quantity }} | Unit: {{ item.unit }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-if="pullOutForm.errors.description" class="text-red-500 text-sm mt-1">{{
                                    pullOutForm.errors.description }}</div>
                            </div>
                            <div>
                                <label for="pullout-quantity" class="block text-sm font-medium text-gray-700">Quantity
                                    to Pull Out</label>
                                <input v-model.number="pullOutForm.quantity" type="number" id="pullout-quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': pullOutForm.errors.quantity }" />
                                <div v-if="pullOutForm.errors.quantity" class="text-red-500 text-sm mt-1">{{
                                    pullOutForm.errors.quantity }}</div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showPullOutModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                :disabled="pullOutForm.processing">
                                {{ pullOutForm.processing ? 'Pulling Out...' : 'Pull Out' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showEditModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form @submit.prevent="updateItem" class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Item</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="edit-unit" class="block text-sm font-medium text-gray-700">Unit</label>
                                <input v-model="editForm.unit" type="text" id="edit-unit"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': editForm.errors.unit }" />
                                <div v-if="editForm.errors.unit" class="text-red-500 text-sm mt-1">{{
                                    editForm.errors.unit }}
                                </div>
                            </div>
                            <div>
                                <label for="edit-description"
                                    class="block text-sm font-medium text-gray-700">Description</label>
                                <input v-model="editForm.description" type="text" id="edit-description"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': editForm.errors.description }" />
                                <div v-if="editForm.errors.description" class="text-red-500 text-sm mt-1">{{
                                    editForm.errors.description }}</div>
                            </div>
                            <div>
                                <label for="edit-quantity"
                                    class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input v-model.number="editForm.quantity" type="number" id="edit-quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': editForm.errors.quantity }" />
                                <div v-if="editForm.errors.quantity" class="text-red-500 text-sm mt-1">{{
                                    editForm.errors.quantity }}</div>
                            </div>
                            <div>
                                <label for="edit-price" class="block text-sm font-medium text-gray-700">Unit
                                    Price</label>
                                <input v-model.number="editForm.price" type="number" id="edit-price"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': editForm.errors.price }" />
                                <div v-if="editForm.errors.price" class="text-red-500 text-sm mt-1">{{
                                    editForm.errors.price }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                :disabled="editForm.processing">
                                {{ editForm.processing ? 'Updating...' : 'Update' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="showRequestModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form @submit.prevent="submitRequest" class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Request Item</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-700"><span class="font-medium">Description:</span> {{
                                    requestItem.description }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-700"><span class="font-medium">Available:</span> {{
                                    requestItem.quantity }}</p>
                            </div>
                            <div>
                                <label for="request-quantity" class="block text-sm font-medium text-gray-700">Quantity
                                    to Request</label>
                                <input v-model.number="requestQuantity" type="number" id="request-quantity" min="1"
                                    :max="requestItem.quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    :class="{ 'border-red-500': requestForm.errors.quantity }" />
                                <div v-if="requestForm.errors.quantity" class="text-red-500 text-sm mt-1">{{
                                    requestForm.errors.quantity }}</div>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showRequestModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                :disabled="requestForm.processing">
                                {{ requestForm.processing ? 'Requesting...' : 'Request' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div v-if="requestSuccess" class="fixed top-4 right-4">
                <div class="bg-green-500 text-white px-4 py-2 rounded-md shadow-lg flex items-center">
                    <span>Item added to cart!</span>
                    <button @click="requestSuccess = false" class="ml-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div v-if="requestError" class="fixed top-4 right-4">
                <div class="bg-red-500 text-white px-4 py-2 rounded-md shadow-lg flex items-center">
                    <span>{{ requestError }}</span>
                    <button @click="requestError = ''" class="ml-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
