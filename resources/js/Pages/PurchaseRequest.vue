<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const requested = ref([]);
const loading = ref(true);
const error = ref('');
const addForm = ref({ unit: '', description: '', quantity: '', price: '' });
const addError = ref('');
const submitSuccess = ref(false);

// Computed property for total amount
const totalAmount = computed(() => {
    return requested.value.reduce((sum, item) => {
        return sum + (parseFloat(item.price) * parseInt(item.quantity));
    }, 0);
});

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
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-900">
                    Purchase Request
                </h2>
                <div class="flex items-center space-x-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-.4-1L5 3m2 10v5a2 2 0 002 2h6a2 2 0 002-2v-5m-8 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                    </svg>
                    <span class="text-lg font-semibold text-gray-700">{{ requested.length }} items</span>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="submitSuccess" class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-green-800 font-medium">Purchase request submitted successfully!</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 rounded-xl">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <p class="text-red-800 font-medium">{{ error }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content - Cart Items -->
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    Requested Items
                                </h3>
                            </div>

                            <div class="p-6">
                                <div v-if="loading" class="flex items-center justify-center py-12">
                                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                                    <span class="ml-3 text-gray-600 font-medium">Loading items...</span>
                                </div>

                                <div v-else-if="requested.length === 0" class="text-center py-12">
                                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 3h2l.4 2M7 13h10l4-8H5.4m-.4-1L5 3m2 10v5a2 2 0 002 2h6a2 2 0 002-2v-5m-8 0V9a2 2 0 012-2h2a2 2 0 012 2v4.01"/>
                                    </svg>
                                    <p class="text-gray-500 text-lg font-medium mt-4">No items in your cart</p>
                                    <p class="text-gray-400 text-sm mt-2">Add items using the form on the right to get started</p>
                                </div>

                                <div v-else>
                                    <!-- Desktop Table -->
                                    <div class="hidden md:block overflow-x-auto">
                                        <table class="w-full">
                                            <thead>
                                                <tr class="border-b border-gray-200">
                                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Unit</th>
                                                    <th class="text-left py-3 px-2 font-semibold text-gray-700">Description</th>
                                                    <th class="text-right py-3 px-2 font-semibold text-gray-700">Qty</th>
                                                    <th class="text-right py-3 px-2 font-semibold text-gray-700">Unit Price</th>
                                                    <th class="text-right py-3 px-2 font-semibold text-gray-700">Total</th>
                                                    <th class="text-center py-3 px-2 font-semibold text-gray-700">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="item in requested" :key="item.id" class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                                    <td class="py-4 px-2">
                                                        <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                            {{ item.unit }}
                                                        </span>
                                                    </td>
                                                    <td class="py-4 px-2">
                                                        <div class="font-medium text-gray-900">{{ item.description }}</div>
                                                    </td>
                                                    <td class="py-4 px-2 text-right font-medium text-gray-900">{{ item.quantity }}</td>
                                                    <td class="py-4 px-2 text-right font-medium text-gray-900">₱{{ parseFloat(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                                    <td class="py-4 px-2 text-right font-bold text-gray-900">₱{{ (parseFloat(item.price) * parseInt(item.quantity)).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</td>
                                                    <td class="py-4 px-2 text-center">
                                                        <button 
                                                            @click="removeItem(item.id)" 
                                                            class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-lg transition-colors duration-200">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Mobile Cards -->
                                    <div class="md:hidden space-y-4">
                                        <div v-for="item in requested" :key="item.id" class="bg-gray-50 rounded-xl p-4 border border-gray-200">
                                            <div class="flex justify-between items-start mb-3">
                                                <div class="flex-1">
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full mb-2">
                                                        {{ item.unit }}
                                                    </span>
                                                    <h4 class="font-medium text-gray-900">{{ item.description }}</h4>
                                                </div>
                                                <button 
                                                    @click="removeItem(item.id)" 
                                                    class="ml-2 p-2 text-red-600 hover:bg-red-100 rounded-lg transition-colors">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="text-gray-500">Quantity:</span>
                                                    <span class="ml-1 font-medium">{{ item.quantity }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Unit Price:</span>
                                                    <span class="ml-1 font-medium">₱{{ parseFloat(item.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-3 border-t border-gray-200">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-gray-500">Total:</span>
                                                    <span class="text-lg font-bold text-gray-900">₱{{ (parseFloat(item.price) * parseInt(item.quantity)).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Total and Submit Section -->
                                    <div class="mt-6 pt-6 border-t border-gray-200">
                                        <div class="flex justify-between items-center mb-4">
                                            <span class="text-xl font-bold text-gray-900">Total Amount:</span>
                                            <span class="text-2xl font-bold text-blue-600">₱{{ totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2 }) }}</span>
                                        </div>
                                        <button 
                                            @click="submitPurchaseRequest" 
                                            class="w-full bg-gradient-to-r from-blue-600 to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold py-4 px-6 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            Submit Purchase Request
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar - Add Item Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white shadow-xl rounded-2xl overflow-hidden sticky top-4">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-700 px-6 py-4">
                                <h3 class="text-xl font-bold text-white flex items-center">
                                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add New Item
                                </h3>
                            </div>

                            <div class="p-6">
                                <form @submit.prevent="addCustomItem" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Unit</label>
                                        <input 
                                            v-model="addForm.unit" 
                                            type="text" 
                                            placeholder="e.g. pcs, kg, liter" 
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                                        <textarea 
                                            v-model="addForm.description" 
                                            placeholder="Item description..." 
                                            rows="3"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 resize-none"
                                        ></textarea>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Quantity</label>
                                            <input 
                                                v-model.number="addForm.quantity" 
                                                type="number" 
                                                min="1" 
                                                placeholder="0" 
                                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                            />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Unit Price</label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 font-medium">₱</span>
                                                <input 
                                                    v-model.number="addForm.price" 
                                                    type="number" 
                                                    min="0" 
                                                    step="0.01" 
                                                    placeholder="0.00" 
                                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="addError" class="p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                            <span class="text-red-700 text-sm font-medium">{{ addError }}</span>
                                        </div>
                                    </div>

                                    <button 
                                        type="submit"
                                        class="w-full bg-gradient-to-r from-green-600 to-emerald-700 hover:from-green-700 hover:to-emerald-800 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-[1.02] shadow-lg hover:shadow-xl">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Add to Cart
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>