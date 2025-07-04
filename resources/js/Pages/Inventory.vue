<script setup>
import { ref, onMounted, watchEffect } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const showAddModal = ref(false);
const showPullOutModal = ref(false);
const showEditModal = ref(false);

// New item fields
const newItem = ref({ unit: '', description: '', quantity: '', price: '' });
const pullOutItem = ref({ id: '', quantity: '' });
const editItem = ref({ id: '', unit: '', description: '', quantity: '', price: '' });

const page = usePage();
const items = ref(page.props.items || []);

// Keep items in sync with backend props
watchEffect(() => {
  if (page.props.items) {
    items.value = page.props.items;
  }
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
</script>

<template>
    <Head title="Inventory" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Office Items Inventory
            </h2>
        </template>

        <div class="px-8 py-6 bg-gray-300 min-h-screen">
            <!-- Header Buttons -->
            <div class="flex justify-between items-center mb-4">
                <input type="text" placeholder="Search Item" class="px-3 py-1 rounded border" />
                <div class="space-x-2">
                    <button @click="showAddModal = true" class="bg-green-400 hover:bg-green-500 text-white px-4 py-1 rounded">Add Item</button>
                    <button @click="showPullOutModal = true" class="bg-red-400 hover:bg-red-500 text-white px-4 py-1 rounded">Pull out Item</button>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="bg-white rounded-lg shadow p-4">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 font-bold">Stock NO.</th>
                            <th class="py-2 font-bold">UNIT</th>
                            <th class="py-2 font-bold">ITEM DESCRIPTION</th>
                            <th class="py-2 font-bold">QUANTITY</th>
                            <th class="py-2 font-bold">UNIT PRICE</th>
                            <th class="py-2 font-bold">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in items" :key="item.id" class="border-t">
                            <td class="py-2">{{ index + 1 }}</td>
                            <td class="py-2">{{ item.unit }}</td>
                            <td class="py-2">{{ item.description }}</td>
                            <td class="py-2">{{ item.quantity }}</td>
                            <td class="py-2">₱ {{ item.price }}</td>
                            <td class="py-2 space-x-2">
                                <button @click="openEditModal(item)" class="bg-blue-200 hover:bg-blue-300 text-xs px-3 py-1 rounded">EDIT</button>
                                <button class="bg-gray-200 hover:bg-gray-300 text-xs px-3 py-1 rounded">Request →</button>
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td colspan="6" class="text-center py-4 text-gray-500">No items available.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Add Item Modal -->
            <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-96">
                    <h2 class="text-lg font-bold mb-4">Add Item</h2>
                    <input v-model="newItem.unit" type="text" placeholder="Unit" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="newItem.description" type="text" placeholder="Description" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="newItem.quantity" type="number" placeholder="Quantity" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="newItem.price" type="number" placeholder="Unit Price" class="mb-4 w-full p-2 border rounded" />
                    <div class="text-right space-x-2">
                        <button @click="showAddModal = false" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
                        <button @click="addItem" class="px-3 py-1 bg-blue-500 text-white rounded">Save</button>
                    </div>
                </div>
            </div>

            <!-- Pull Out Modal -->
            <div v-if="showPullOutModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-96">
                    <h2 class="text-lg font-bold mb-4">Pull Out Item</h2>
                    <input v-model="pullOutItem.id" type="text" placeholder="Item Description" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="pullOutItem.quantity" type="number" placeholder="Quantity to Pull Out" class="mb-4 w-full p-2 border rounded" />
                    <div class="text-right space-x-2">
                        <button @click="showPullOutModal = false" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
                        <button @click="pullOut" class="px-3 py-1 bg-red-500 text-white rounded">Pull Out</button>
                    </div>
                </div>
            </div>

            <!-- Edit Item Modal -->
            <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 w-96">
                    <h2 class="text-lg font-bold mb-4">Edit Item</h2>
                    <input v-model="editItem.unit" type="text" placeholder="Unit" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="editItem.description" type="text" placeholder="Description" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="editItem.quantity" type="number" placeholder="Quantity" class="mb-2 w-full p-2 border rounded" />
                    <input v-model="editItem.price" type="number" placeholder="Unit Price" class="mb-4 w-full p-2 border rounded" />
                    <div class="text-right space-x-2">
                        <button @click="showEditModal = false" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
                        <button @click="updateItem" class="px-3 py-1 bg-blue-500 text-white rounded">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
