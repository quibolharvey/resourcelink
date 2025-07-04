<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div class="min-h-screen bg-gray-50 flex flex-col">
        <!-- Header with Login/Sign Up -->
        <header class="w-full py-4 px-6 flex justify-end">
            <div class="flex space-x-4">
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="text-gray-600 hover:text-gray-900 font-medium mt-10"
                >
                    Log In
                </Link>
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="text-gray-600 hover:text-gray-900 font-medium mt-10"
                >
                    Sign Up
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow flex flex-col justify-center items-center px-6">
            <div class="w-full text-center space-y-8">
                <h1 class="text-4xl font-bold text-gray-800">WELCOME TO</h1>
                
                <div class="text-5xl font-extrabold text-blue-600">
                    CALAPE RESOURCELINK
                </div>
                
                <div class="mt-12">
                    <Link
                        :href="route('login')"
                        class="inline-block px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition"
                    >
                        GET STARTED
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full py-8 text-center">
            <p class="text-gray-600 mb-4">
                A smarter way to borrow, track, and manage resources in the Municipality of Calape
            </p>
        </footer>
    </div>
</template>