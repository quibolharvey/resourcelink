<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { defineProps, ref } from 'vue';

const props = defineProps({
    items: Array
});

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    quantity: '',
    fee: '',
    image: null,
});

const handleImageUpload = (e) => {
    form.image = e.target.files[0];
};

const openAddModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.name = item.name;
    form.quantity = item.quantity;
    form.fee = item.fee;
    form.image = null;
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        form.post(route('items.update', editingId.value), {
            method: 'post',
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('items.store'), {
            forceFormData: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteItem = (id) => {
    if (confirm("Are you sure you want to delete this item?")) {
        router.delete(route('items.destroy', id));
    }
};
</script>

<template>
    <Head title="Items" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-800">
                📦 Item Management
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-lg sm:rounded-xl">
                    <div class="p-6 text-gray-900">
                        <!-- Add Item Button -->
                        <div class="mb-6 flex justify-end">
                            <button
                                @click="openAddModal"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow-md transition duration-200"
                            >
                                + Add Item
                            </button>
                        </div>

                        <!-- Item List Grid -->
                        <h3 class="text-lg font-semibold mb-4">Item List</h3>
                        <div v-if="props.items.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="item in props.items"
                                :key="item.id"
                                class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition p-5 flex flex-col"
                            >
                                <!-- Image -->
                                <div class="w-full h-40 bg-gray-100 rounded-lg overflow-hidden mb-4 flex items-center justify-center">
                                    <img
                                        v-if="item.image"
                                        :src="`/storage/${item.image}`"
                                        alt="Item Image"
                                        class="h-full w-full object-cover"
                                    />
                                    <span v-else class="text-gray-400 italic">No image</span>
                                </div>

                                <!-- Item Info -->
                                <h4 class="text-lg font-bold text-gray-800 mb-1">{{ item.name }}</h4>
                                <p class="text-gray-600 text-sm mb-1">Quantity: {{ item.quantity }}</p>
                                <p class="text-green-600 font-semibold mb-4">₱{{ item.fee }}</p>

                                <!-- Actions -->
                                <div class="mt-auto flex gap-3">
                                    <button
                                        @click="openEditModal(item)"
                                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        @click="deleteItem(item.id)"
                                        class="flex-1 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-gray-500 italic">No items yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
        >
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-lg">
                <h2 class="text-xl font-bold mb-5 border-b pb-3">
                    {{ isEditing ? '✏️ Edit Item' : '➕ Add New Item' }}
                </h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block font-medium text-gray-700">Item Name</label>
                        <input type="text" v-model="form.name" id="name" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                    </div>

                    <div>
                        <label for="quantity" class="block font-medium text-gray-700">Quantity</label>
                        <input type="number" v-model="form.quantity" id="quantity" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                    </div>

                    <div>
                        <label for="fee" class="block font-medium text-gray-700">Fee per Item</label>
                        <input type="number" v-model="form.fee" id="fee" step="0.01" required
                            class="w-full border-gray-300 rounded-lg px-4 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                    </div>

                    <div>
                        <label for="image" class="block font-medium text-gray-700">Image</label>
                        <input type="file" @change="handleImageUpload" id="image" accept="image/*"
                            class="w-full border-gray-300 rounded-lg px-4 py-2 mt-1 focus:ring-2 focus:ring-blue-400 focus:outline-none" />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image.</p>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-5 py-2 rounded-lg transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded-lg shadow-md transition"
                            :disabled="form.processing"
                        >
                            {{ isEditing ? 'Update' : 'Add' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
