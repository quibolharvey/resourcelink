<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const page = usePage()

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
    <div class="min-h-screen flex flex-col bg-cover bg-center bg-no-repeat relative"
        style="background-image: url('/images/background.jpg');">
        <!-- Gradient overlay -->
        <div class="absolute inset-0 bg-gradient-to-b from-blue-50/80 via-white/30 to-gray-50/70"></div>

        <!-- Content wrapper (keeps content above overlay) -->
        <div class="relative flex flex-col flex-grow">
            <!-- Header -->
            <header class="w-full py-4 px-6 flex justify-end items-center bg-white/80 backdrop-blur-sm shadow-sm">
                <div class="flex items-center mr-auto">
                    <p>
                        <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
                    </p>
                    <span class="ml-2 font-bold text-lg text-gray-800">Calape ResourceLink</span>
                </div>
                <div class="flex items-center space-x-4" v-if="!page.props.auth.user">
                    <Link v-if="canLogin" :href="route('login')"
                        class="text-gray-600 hover:text-blue-600 font-medium transition-colors">
                    Log In
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="px-3 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                    Sign Up
                    </Link>
                </div>
            </header>

            <!-- Main Content -->
            <main class="flex-grow flex flex-col justify-center items-center px-6 text-center">
                <div class="w-full max-w-3xl text-center space-y-6">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800">
                        WELCOME TO
                    </h1>

                    <div class="text-5xl md:text-6xl font-extrabold text-blue-600 tracking-wide">
                        CALAPE RESOURCELINK
                    </div>

                    <p class="mt-4 text-lg text-gray-700 max-w-xl mx-auto leading-relaxed">
                        A smarter way to borrow, track, and manage resources in the Municipality of Calape.
                    </p>

                    <div class="mt-8">
                        <Link :href="route('login')"
                            class="inline-block px-8 py-3 bg-blue-600 text-white font-bold rounded-lg shadow-lg hover:bg-blue-700 transform hover:scale-105 transition">
                        GET STARTED
                        </Link>
                    </div>
                </div>
            </main>

            <!-- Footer -->
            <footer class="w-full py-6 text-center bg-white/80 backdrop-blur-sm border-t">
                <p class="text-gray-500 text-sm">
                    &copy; {{ new Date().getFullYear() }} Municipality of Calape. All rights reserved.
                </p>
            </footer>
        </div>
    </div>
</template>
