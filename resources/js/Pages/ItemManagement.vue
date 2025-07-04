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
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Item Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Add Item Button -->
                        <div class="mb-4">
                            <button
                                @click="openAddModal"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                            >
                                + Add Item
                            </button>
                        </div>

                        <!-- Item List Table -->
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Item List</h3>
                            <div v-if="props.items.length">
                                <table class="min-w-full border border-gray-300 text-sm">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="border px-4 py-2 text-left">Name</th>
                                            <th class="border px-4 py-2 text-left">Quantity</th>
                                            <th class="border px-4 py-2 text-left">Fee</th>
                                            <th class="border px-4 py-2 text-left">Image</th>
                                            <th class="border px-4 py-2 text-left">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in props.items" :key="item.id">
                                            <td class="border px-4 py-2">{{ item.name }}</td>
                                            <td class="border px-4 py-2">{{ item.quantity }}</td>
                                            <td class="border px-4 py-2">₱{{ item.fee }}</td>
                                            <td class="border px-4 py-2">
                                                <img v-if="item.image" :src="`/storage/${item.image}`" class="h-12" />
                                                <span v-else>No image</span>
                                            </td>
                                            <td class="border px-4 py-2 space-x-2">
                                                <button
                                                    @click="openEditModal(item)"
                                                    class="text-blue-600 hover:underline"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    @click="deleteItem(item.id)"
                                                    class="text-red-600 hover:underline"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div v-else>
                                <p class="text-gray-500">No items yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        >
            <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-lg">
                <h2 class="text-lg font-bold mb-4">{{ isEditing ? 'Edit Item' : 'Add New Item' }}</h2>
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block font-medium">Item Name</label>
                        <input type="text" v-model="form.name" id="name" required class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label for="quantity" class="block font-medium">Quantity</label>
                        <input type="number" v-model="form.quantity" id="quantity" required class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label for="fee" class="block font-medium">Fee per Item</label>
                        <input type="number" v-model="form.fee" id="fee" step="0.01" required class="w-full border rounded px-3 py-2" />
                    </div>

                    <div>
                        <label for="image" class="block font-medium">Image</label>
                        <input type="file" @change="handleImageUpload" id="image" accept="image/*" class="w-full border rounded px-3 py-2" />
                        <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image.</p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showModal = false"
                            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
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
