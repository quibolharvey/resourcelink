<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const requested = ref([]);
const loading = ref(true);
const error = ref('');
const addForm = ref({ unit: '', description: '', quantity: '', price: '' });
const addError = ref('');
const submitSuccess = ref(false);

const fetchCart = async () => {
    loading.value = true;
    try {
        const response = await fetch('/api/purchase-cart');
        if (response.ok) {
            const data = await response.json();
            requested.value = data.items || [];
        } else {
            error.value = 'Failed to load cart.';
        }
    } catch (e) {
        error.value = 'Failed to load cart.';
    }
    loading.value = false;
};

const removeItem = async (itemId) => {
    try {
        const response = await fetch(`/api/purchase-cart/items/${itemId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        if (response.ok) {
            requested.value = requested.value.filter(i => i.id !== itemId);
        }
    } catch (e) {}
};

const addCustomItem = async () => {
    addError.value = '';
    if (!addForm.value.unit || !addForm.value.description || !addForm.value.quantity || !addForm.value.price) {
        addError.value = 'All fields are required.';
        return;
    }
    try {
        const response = await fetch('/api/purchase-cart/items', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({
                unit: addForm.value.unit,
                description: addForm.value.description,
                quantity: addForm.value.quantity,
                price: addForm.value.price,
            }),
        });
        if (response.ok) {
            const item = await response.json();
            requested.value.push(item);
            addForm.value = { unit: '', description: '', quantity: '', price: '' };
        } else {
            addError.value = 'Failed to add item.';
        }
    } catch (e) {
        addError.value = 'Failed to add item.';
    }
};

const submitPurchaseRequest = async () => {
    try {
        const response = await fetch('/api/purchase-cart/submit', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });
        if (response.ok) {
            const data = await response.json();
            submitSuccess.value = true;
            requested.value = [];
            if (data.redirect) {
                window.location.href = '/requesthistory';
            }
        }
    } catch (e) {}
};

onMounted(fetchCart);
</script>

<template>
    <Head title="Items" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Purchase Request
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="loading" class="text-gray-500">Loading...</div>
                        <div v-else>
                            <div v-if="submitSuccess" class="mb-4 p-2 bg-green-200 text-green-800 rounded">Purchase request submitted!</div>
                            <div v-if="requested.length > 0">
                                <h3 class="text-lg font-bold mb-2">Requested Items</h3>
                                <table class="w-full text-left mb-4">
                                    <thead>
                                        <tr>
                                            <th class="py-2 font-bold">Unit</th>
                                            <th class="py-2 font-bold">Description</th>
                                            <th class="py-2 font-bold">Quantity</th>
                                            <th class="py-2 font-bold">Unit Price</th>
                                            <th class="py-2 font-bold">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in requested" :key="item.id">
                                            <td class="py-2">{{ item.unit }}</td>
                                            <td class="py-2">{{ item.description }}</td>
                                            <td class="py-2">{{ item.quantity }}</td>
                                            <td class="py-2">₱ {{ item.price }}</td>
                                            <td class="py-2">
                                                <button @click="removeItem(item.id)" class="px-2 py-1 bg-red-400 text-white rounded">Remove</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button @click="submitPurchaseRequest" class="px-4 py-2 bg-blue-500 text-white rounded">Submit Purchase Request</button>
                            </div>
                            <div v-else class="text-gray-500 mb-4">No items requested.</div>
                            <div class="mt-8">
                                <h3 class="text-lg font-bold mb-2">Add Custom Item</h3>
                                <div class="flex flex-col md:flex-row gap-2 mb-2">
                                    <input v-model="addForm.unit" type="text" placeholder="Unit" class="p-2 border rounded w-full md:w-32" />
                                    <input v-model="addForm.description" type="text" placeholder="Description" class="p-2 border rounded w-full md:w-64" />
                                    <input v-model.number="addForm.quantity" type="number" placeholder="Quantity" class="p-2 border rounded w-full md:w-32" />
                                    <input v-model.number="addForm.price" type="number" placeholder="Unit Price" class="p-2 border rounded w-full md:w-32" />
                                    <button @click="addCustomItem" class="px-3 py-2 bg-green-500 text-white rounded">Add</button>
                                </div>
                                <div v-if="addError" class="text-red-500">{{ addError }}</div>
                            </div>
                        </div>
                        <div v-if="error" class="text-red-500 mt-4">{{ error }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
