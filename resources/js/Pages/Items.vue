<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  items: Array,
});

const showModal = ref(false);
const selectedItem = ref(null);

const form = useForm({
  item_id: null,
  quantity: 1,
  expected_return: '',
});

const openBorrowModal = (item) => {
  selectedItem.value = item;
  form.item_id = item.id;
  form.quantity = 1;
  form.expected_return = '';
  showModal.value = true;
};

const submitBorrow = () => {
  form.post(route('borrow.store'), {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    },
  });
};
</script>


<template>
  <Head title="Items" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Items</h2>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 text-gray-900">
            <h3 class="text-lg font-semibold mb-4">Available Items</h3>

            <div v-if="items.length" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              <div v-for="item in items" :key="item.id" class="border rounded-lg shadow p-4 bg-gray-50">
                <img v-if="item.image" :src="`/storage/${item.image}`" class="h-32 w-full object-cover rounded mb-3" />
                <div v-else class="h-32 w-full flex items-center justify-center bg-gray-200 rounded mb-3 text-gray-500">
                  No Image
                </div>
                <h4 class="font-semibold text-lg">{{ item.name }}</h4>
                <p class="text-sm text-gray-600">Quantity: {{ item.quantity }}</p>
                <p class="text-sm text-gray-600">₱{{ item.fee }}</p>
                <button @click="openBorrowModal(item)" class="mt-2 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">
                  Borrow
                </button>
              </div>
            </div>

            <div v-else>
              <p class="text-gray-500">No items available.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Borrow Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">Borrow {{ selectedItem.name }}</h2>
        <form @submit.prevent="submitBorrow" class="space-y-4">
          <div>
            <label for="quantity" class="block font-medium">Quantity</label>
            <input type="number" v-model="form.quantity" min="1" :max="selectedItem.quantity" id="quantity" required class="w-full border rounded px-3 py-2" />
          </div>
          <div>
            <label for="expected_return" class="block font-medium">Expected Return Date</label>
            <input type="date" v-model="form.expected_return" id="expected_return" required class="w-full border rounded px-3 py-2" />
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="showModal = false" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">Cancel</button>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700" :disabled="form.processing">Submit</button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
