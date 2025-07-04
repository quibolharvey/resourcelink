<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const showAddModal = ref(false);
const showPullOutModal = ref(false);

// New item fields
const newItem = ref({ unit: '', description: '', quantity: '', price: '' });
const pullOutItem = ref({ id: '', quantity: '' });

// Inventory items
const items = ref([]);

// Add item to inventory
const addItem = () => {
    if (newItem.value.unit && newItem.value.description && newItem.value.quantity && newItem.value.price) {
        items.value.push({
            id: Date.now(),
            unit: newItem.value.unit,
            description: newItem.value.description,
            quantity: parseInt(newItem.value.quantity),
            price: parseFloat(newItem.value.price),
        });
        newItem.value = { unit: '', description: '', quantity: '', price: '' };
        showAddModal.value = false;
    }
};

// Pull out item
const pullOut = () => {
    const itemIndex = items.value.findIndex(item => item.description.toLowerCase() === pullOutItem.value.id.toLowerCase());
    if (itemIndex !== -1) {
        const currentQty = items.value[itemIndex].quantity;
        const removeQty = parseInt(pullOutItem.value.quantity);
        if (removeQty > 0 && currentQty >= removeQty) {
            items.value[itemIndex].quantity -= removeQty;
        }
    }
    pullOutItem.value = { id: '', quantity: '' };
    showPullOutModal.value = false;
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
                                <button class="bg-blue-200 hover:bg-blue-300 text-xs px-3 py-1 rounded">EDIT</button>
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
        </div>
    </AuthenticatedLayout>
</template>
