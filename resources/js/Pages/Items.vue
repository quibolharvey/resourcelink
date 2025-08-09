<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
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
      <h2 class="text-2xl font-bold text-gray-800">Items</h2>
    </template>

    <div class="py-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-xl p-6">
          <h3 class="text-lg font-semibold mb-6">Available Items</h3>

          <div
            v-if="items.length"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
          >
            <div
              v-for="item in items"
              :key="item.id"
              class="bg-gray-50 rounded-lg shadow hover:shadow-lg transition p-4 flex flex-col"
            >
              <div class="relative">
                <img
                  v-if="item.image"
                  :src="`/storage/${item.image}`"
                  class="h-40 w-full object-cover rounded-md"
                />
                <div
                  v-else
                  class="h-40 w-full flex items-center justify-center bg-gray-200 rounded-md text-gray-500 text-sm"
                >
                  No Image
                </div>
              </div>

              <div class="mt-4 flex-1">
                <h4 class="font-semibold text-lg text-gray-800">
                  {{ item.name }}
                </h4>
                <p class="text-sm text-gray-600 mt-1">
                  Quantity: {{ item.quantity }}
                </p>
                <p class="text-sm text-gray-600">
                  ₱{{ item.fee }}
                </p>
              </div>

              <button
                @click="openBorrowModal(item)"
                class="mt-4 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition"
              >
                Borrow
              </button>
            </div>
          </div>

          <div v-else class="text-center py-8 text-gray-500">
            No items available.
          </div>
        </div>
      </div>
    </div>

    <!-- Borrow Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden"
      >
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-bold text-gray-800">
            Borrow {{ selectedItem.name }}
          </h2>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4">
          <form @submit.prevent="submitBorrow" class="space-y-4">
            <div>
              <label for="quantity" class="block font-medium text-gray-700"
                >Quantity</label
              >
              <input
                type="number"
                v-model="form.quantity"
                min="1"
                :max="selectedItem.quantity"
                id="quantity"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
              />
            </div>

            <div>
              <label
                for="expected_return"
                class="block font-medium text-gray-700"
                >Expected Return Date</label
              >
              <input
                type="date"
                v-model="form.expected_return"
                id="expected_return"
                required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none"
              />
            </div>
          </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
          <button
            type="button"
            @click="showModal = false"
            class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition"
          >
            Cancel
          </button>
          <button
            type="submit"
            @click="submitBorrow"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition"
            :disabled="form.processing"
          >
            Submit
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
