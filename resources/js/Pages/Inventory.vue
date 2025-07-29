<script setup>
import { ref, onMounted, watchEffect, computed } from 'vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const showAddModal = ref(false);
const showPullOutModal = ref(false);
const showEditModal = ref(false);
const showRequestModal = ref(false);
const requestItem = ref({});
const requestQuantity = ref(1);
const requestSuccess = ref(false);
const requestError = ref('');

// New item fields
const newItem = ref({ unit: '', description: '', quantity: '', price: '' });
const pullOutItem = ref({ id: '', quantity: '' });
const editItem = ref({ id: '', unit: '', description: '', quantity: '', price: '' });

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

// Add item to inventory (backend)
const addItem = async () => {
    if (newItem.value.unit && newItem.value.description && newItem.value.quantity && newItem.value.price) {
        const response = await fetch('/inventory', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(newItem.value),
        });
        if (response.ok) {
            window.location.reload();
        } else {
            alert('Failed to add item.');
        }
    }
};

// Pull out item (backend)
const pullOut = async () => {
    const item = items.value.find(i => i.description.toLowerCase() === pullOutItem.value.id.toLowerCase());
    if (item) {
        const response = await fetch(`/inventory/${item.id}/pullout`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify({ quantity: pullOutItem.value.quantity }),
        });
        if (response.ok) {
            window.location.reload();
        } else {
            const error = await response.json();
            alert(error.error || 'Failed to pull out item.');
        }
    } else {
        alert('Item not found.');
    }
};

const openEditModal = (item) => {
    editItem.value = { ...item };
    showEditModal.value = true;
};

const updateItem = async () => {
    const response = await fetch(`/inventory/${editItem.value.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: JSON.stringify({
            unit: editItem.value.unit,
            description: editItem.value.description,
            quantity: editItem.value.quantity,
            price: editItem.value.price,
        }),
    });
    if (response.ok) {
        window.location.reload();
    } else {
        alert('Failed to update item.');
    }
};

const openRequestModal = (item) => {
    requestItem.value = { ...item };
    requestQuantity.value = 1;
    showRequestModal.value = true;
};

const submitRequest = async () => {
    try {
        const response = await fetch('/api/purchase-cart/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                inventory_item_id: requestItem.value.id,
                unit: requestItem.value.unit,
                description: requestItem.value.description,
                quantity: requestQuantity.value,
                price: requestItem.value.price,
            }),
        });
        if (response.ok) {
            requestSuccess.value = true;
            requestError.value = '';
            showRequestModal.value = false;
        } else {
            const error = await response.json();
            requestError.value = error.message || 'Failed to add item to cart.';
        }
    } catch (e) {
        requestError.value = 'Failed to add item to cart.';
    }
};

const goToPurchaseRequest = () => {
    router.visit('/purchaserequest');
};
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
                <!-- Header Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div class="w-full sm:w-64">
                        <input 
                            type="text" 
                            placeholder="Search Item" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" 
                            v-model="searchTerm"
                        />
                    </div>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                        <button 
                            @click="showAddModal = true" 
                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md shadow-sm flex items-center justify-center"
                        >
                            Add Item
                        </button>
                        <button 
                            @click="showPullOutModal = true" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md shadow-sm flex items-center justify-center"
                        >
                            Pull out Item
                        </button>
                        <button 
                            @click="goToPurchaseRequest" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm flex items-center justify-center"
                        >
                            Go to Purchase Request
                        </button>
                    </div>
                </div>

                <!-- Inventory Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock NO.</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UNIT</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ITEM DESCRIPTION</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QUANTITY</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UNIT PRICE</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTION</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(item, index) in filteredItems" :key="item.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.unit }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ item.description }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">₱ {{ item.price }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button 
                                                @click="openEditModal(item)" 
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Edit
                                            </button>
                                            <button 
                                                @click="openRequestModal(item)" 
                                                class="text-gray-600 hover:text-gray-900"
                                            >
                                                Request →
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No items available.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Add Item Modal -->
            <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Item</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
                                <input 
                                    v-model="newItem.unit" 
                                    type="text" 
                                    id="unit"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <input 
                                    v-model="newItem.description" 
                                    type="text" 
                                    id="description"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input 
                                    v-model="newItem.quantity" 
                                    type="number" 
                                    id="quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="price" class="block text-sm font-medium text-gray-700">Unit Price</label>
                                <input 
                                    v-model="newItem.price" 
                                    type="number" 
                                    id="price"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                @click="showAddModal = false" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="addItem" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pull Out Modal -->
            <div v-if="showPullOutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Pull Out Item</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="item-description" class="block text-sm font-medium text-gray-700">Item Description</label>
                                <input 
                                    v-model="pullOutItem.id" 
                                    type="text" 
                                    id="item-description"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="pullout-quantity" class="block text-sm font-medium text-gray-700">Quantity to Pull Out</label>
                                <input 
                                    v-model="pullOutItem.quantity" 
                                    type="number" 
                                    id="pullout-quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                @click="showPullOutModal = false" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="pullOut" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Pull Out
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Item Modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Item</h2>
                        <div class="space-y-4">
                            <div>
                                <label for="edit-unit" class="block text-sm font-medium text-gray-700">Unit</label>
                                <input 
                                    v-model="editItem.unit" 
                                    type="text" 
                                    id="edit-unit"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="edit-description" class="block text-sm font-medium text-gray-700">Description</label>
                                <input 
                                    v-model="editItem.description" 
                                    type="text" 
                                    id="edit-description"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="edit-quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
                                <input 
                                    v-model="editItem.quantity" 
                                    type="number" 
                                    id="edit-quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                            <div>
                                <label for="edit-price" class="block text-sm font-medium text-gray-700">Unit Price</label>
                                <input 
                                    v-model="editItem.price" 
                                    type="number" 
                                    id="edit-price"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                @click="showEditModal = false" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="updateItem" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Request Modal -->
            <div v-if="showRequestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Request Item</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-gray-700"><span class="font-medium">Description:</span> {{ requestItem.description }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-700"><span class="font-medium">Available:</span> {{ requestItem.quantity }}</p>
                            </div>
                            <div>
                                <label for="request-quantity" class="block text-sm font-medium text-gray-700">Quantity to Request</label>
                                <input 
                                    v-model.number="requestQuantity" 
                                    type="number" 
                                    id="request-quantity"
                                    min="1" 
                                    :max="requestItem.quantity"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end space-x-3">
                            <button 
                                @click="showRequestModal = false" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="submitRequest" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Messages -->
            <div v-if="requestSuccess" class="fixed top-4 right-4">
                <div class="bg-green-500 text-white px-4 py-2 rounded-md shadow-lg flex items-center">
                    <span>Item added to cart!</span>
                    <button @click="requestSuccess = false" class="ml-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <div v-if="requestError" class="fixed top-4 right-4">
                <div class="bg-red-500 text-white px-4 py-2 rounded-md shadow-lg flex items-center">
                    <span>{{ requestError }}</span>
                    <button @click="requestError = ''" class="ml-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>