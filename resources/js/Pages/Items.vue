<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
  items: Array,
});

const showModal = ref(false);
const selectedItem = ref(null);
const searchTerm = ref('');

const form = useForm({
  item_id: null,
  quantity: 1,
  expected_return: '',
});

const quantityError = ref('');

// Filter items based on search term
const filteredItems = computed(() => {
  if (!searchTerm.value.trim()) {
    return props.items;
  }
  
  const term = searchTerm.value.toLowerCase().trim();
  return props.items.filter(item => 
    item.name.toLowerCase().includes(term) ||
    item.description?.toLowerCase().includes(term) ||
    item.category?.toLowerCase().includes(term)
  );
});

const openBorrowModal = (item) => {
  selectedItem.value = item;
  form.item_id = item.id;
  form.quantity = 1;
  form.expected_return = '';
  quantityError.value = '';
  showModal.value = true;
};

const validateQuantity = () => {
  if (!selectedItem.value) return;
  
  if (form.quantity > selectedItem.value.quantity) {
    quantityError.value = `Cannot borrow more than ${selectedItem.value.quantity} items (available in stock)`;
    return false;
  }
  
  if (form.quantity < 1) {
    quantityError.value = 'Quantity must be at least 1';
    return false;
  }
  
  quantityError.value = '';
  return true;
};

const submitBorrow = () => {
  if (!validateQuantity()) {
    return;
  }
  
  form.post(route('borrow.store'), {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
      quantityError.value = '';
    },
  });
};
</script>

<template>
  <Head title="Items" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex items-center space-x-3">
        <div class="p-2 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl shadow-lg">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
          </svg>
        </div>
        <h2 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
          Available Items
        </h2>
      </div>
    </template>

    <div class="py-8">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <!-- Search and Filter Bar -->
        <div class="bg-white/70 backdrop-blur-md rounded-2xl shadow-sm border border-white/20 p-6 mb-8">
          <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative flex-1 max-w-md">
              <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <input 
                type="text" 
                v-model="searchTerm"
                placeholder="Search items..." 
                class="w-full pl-10 pr-4 py-3 bg-white/80 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200 placeholder-gray-500"
              />
            </div>
            <div class="flex items-center space-x-2 text-sm text-gray-600">
              <span class="px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full font-medium">
                {{ filteredItems.length }} {{ searchTerm ? 'items found' : 'items available' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Items Grid -->
        <div class="bg-white/50 backdrop-blur-sm rounded-2xl shadow-sm border border-white/20 p-8">
          <div
            v-if="filteredItems.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
          >
            <div
              v-for="item in filteredItems"
              :key="item.id"
              class="group bg-white rounded-2xl shadow-sm hover:shadow-xl border border-gray-100 hover:border-emerald-200 transition-all duration-300 overflow-hidden transform hover:-translate-y-1"
            >
              <!-- Item Image -->
              <div class="relative overflow-hidden">
                <img
                  v-if="item.image"
                  :src="`/storage/${item.image}`"
                  class="h-48 w-full object-cover transition-transform duration-500 group-hover:scale-110"
                />
                <div
                  v-else
                  class="h-48 w-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 transition-all duration-300 group-hover:from-emerald-50 group-hover:to-teal-50"
                >
                  <div class="text-center">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-2 group-hover:text-emerald-500 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-sm text-gray-500 group-hover:text-emerald-600 transition-colors duration-300">No Image</p>
                  </div>
                </div>
                
                <!-- Availability Badge -->
                <div class="absolute top-3 right-3">
                  <span 
                    :class="[
                      'px-2 py-1 text-white text-xs font-semibold rounded-full shadow-lg',
                      item.quantity > 0 
                        ? 'bg-emerald-500' 
                        : 'bg-red-500'
                    ]"
                  >
                    {{ item.quantity > 0 ? 'Available' : 'Out of Stock' }}
                  </span>
                </div>
              </div>

              <!-- Item Details -->
              <div class="p-6">
                <div class="flex-1 space-y-3">
                  <h4 class="font-bold text-xl text-gray-800 group-hover:text-emerald-700 transition-colors duration-200">
                    {{ item.name }}
                  </h4>
                  
                  <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                      </svg>
                      <span 
                        :class="[
                          'text-sm font-medium',
                          item.quantity > 0 ? 'text-gray-600' : 'text-red-600'
                        ]"
                      >
                        {{ item.quantity > 0 ? `${item.quantity} in stock` : 'Out of stock' }}
                      </span>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    <span class="text-lg font-bold text-emerald-600">
                      ₱{{ item.fee }}
                    </span>
                  </div>
                </div>

                <!-- Borrow Button -->
                <button
                  @click="item.quantity > 0 ? openBorrowModal(item) : null"
                  :disabled="item.quantity === 0"
                  :class="[
                    'w-full mt-6 font-semibold px-6 py-3 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
                    item.quantity > 0 
                      ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white transform hover:scale-105 shadow-lg hover:shadow-xl focus:ring-emerald-500' 
                      : 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none'
                  ]"
                >
                  <div class="flex items-center justify-center space-x-2">
                    <svg v-if="item.quantity > 0" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span>{{ item.quantity > 0 ? 'Borrow Item' : 'Out of Stock' }}</span>
                  </div>
                </button>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16">
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-full w-24 h-24 flex items-center justify-center mx-auto mb-6">
              <svg v-if="searchTerm" class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              <svg v-else class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">
              {{ searchTerm ? 'No items found' : 'No items available' }}
            </h3>
            <p class="text-gray-500">
              {{ searchTerm ? `No items match "${searchTerm}". Try a different search term.` : 'Check back later for new items to borrow.' }}
            </p>
            <button 
              v-if="searchTerm"
              @click="searchTerm = ''"
              class="mt-4 px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors duration-200"
            >
              Clear Search
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Borrow Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-md"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all duration-300 scale-100"
      >
        <!-- Modal Header -->
        <div class="relative px-8 py-6 bg-gradient-to-r from-emerald-600 to-teal-600">
          <h2 class="text-2xl font-bold text-white flex items-center space-x-3">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <span>Borrow {{ selectedItem?.name }}</span>
          </h2>
          <button
            @click="showModal = false"
            class="absolute top-4 right-4 text-white/80 hover:text-white transition-colors duration-200"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>

        <!-- Modal Body -->
        <div class="px-8 py-8">
          <form @submit.prevent="submitBorrow" class="space-y-6">
            <!-- Quantity Input -->
            <div class="space-y-2">
              <label for="quantity" class="flex text-sm font-semibold text-gray-700 items-center space-x-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
                <span>Quantity</span>
              </label>
              <div class="relative">
                <input
                  type="number"
                  v-model="form.quantity"
                  @input="validateQuantity"
                  min="1"
                  :max="selectedItem?.quantity"
                  id="quantity"
                  required
                  :class="[
                    'w-full border-2 rounded-xl px-4 py-3 focus:ring-2 focus:outline-none transition-all duration-200 text-lg font-medium',
                    quantityError 
                      ? 'border-red-300 focus:ring-red-500 focus:border-red-500 bg-red-50' 
                      : 'border-gray-200 focus:ring-emerald-500 focus:border-emerald-500'
                  ]"
                />
                <div class="absolute right-3 top-1/2 transform -translate-y-1/2 text-sm text-gray-500">
                  / {{ selectedItem?.quantity }}
                </div>
              </div>
              <!-- Error Message -->
              <div v-if="quantityError" class="flex items-center space-x-2 text-red-600 text-sm">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>{{ quantityError }}</span>
              </div>
            </div>

            <!-- Expected Return Date -->
            <div class="space-y-2">
              <label
                for="expected_return"
                class="flex text-sm font-semibold text-gray-700 items-center space-x-2"
              >
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Expected Return Date</span>
              </label>
              <input
                type="date"
                v-model="form.expected_return"
                id="expected_return"
                :min="new Date().toISOString().split('T')[0]"
                required
                class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all duration-200 text-lg"
              />
            </div>

            <!-- Fee Display -->
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-xl p-4 border border-emerald-200">
              <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-emerald-800">Per Item Fee:</span>
                <span class="text-xl font-bold text-emerald-700">
                  ₱{{ selectedItem?.fee ? (selectedItem.fee * form.quantity) : 0 }}
                </span>
              </div>
            </div>
          </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex justify-end space-x-4">
          <button
            type="button"
            @click="showModal = false"
            class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
          >
            Cancel
          </button>
          <button
            type="submit"
            @click="submitBorrow"
            class="px-8 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
            :disabled="form.processing"
          >
            <div class="flex items-center space-x-2">
              <svg v-if="form.processing" class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ form.processing ? 'Processing...' : 'Confirm Borrow' }}</span>
            </div>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>