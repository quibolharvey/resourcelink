<!-- ItemModal.vue -->
<template>
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
</template>

<script setup>
defineProps(['showModal', 'isEditing', 'form']);
defineEmits(['submit', 'handleImageUpload', 'close']);

const handleImageUpload = (e) => {
  emit('handleImageUpload', e);
};

const submit = () => {
  emit('submit');
};
</script>