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
    <div class="min-h-screen flex flex-col bg-cover bg-center bg-no-repeat relative overflow-hidden"
        style="background-image: url('/images/background.jpg');">
        
        <!-- Animated background elements -->
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50/95 via-white/60 to-blue-100/90"></div>
        <div class="absolute top-0 left-0 w-48 h-48 sm:w-96 sm:h-96 bg-blue-200/20 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-40 h-40 sm:w-80 sm:h-80 bg-indigo-200/20 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/4 w-32 h-32 sm:w-64 sm:h-64 bg-cyan-200/20 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse animation-delay-4000"></div>

        <!-- Content wrapper -->
        <div class="relative flex flex-col flex-grow z-10">
            <!-- Enhanced Header with glassmorphism -->
            <header class="w-full py-4 sm:py-6 px-4 sm:px-8 flex justify-between items-center bg-white/10 backdrop-blur-xl shadow-lg border-b border-white/20">
                <div class="flex items-center group">
                    <div class="p-1.5 sm:p-2 bg-white/20 backdrop-blur-sm rounded-xl shadow-lg group-hover:bg-white/30 transition-all duration-300">
                        <ApplicationLogo class="block h-8 sm:h-10 w-auto fill-current text-blue-700" />
                    </div>
                    <div class="ml-2 sm:ml-4">
                        <span class="font-bold text-lg sm:text-2xl text-blue-800 tracking-wide drop-shadow-sm">Calape ResourceLink</span>
                        <p class="text-xs sm:text-sm text-blue-600/80 font-medium hidden sm:block">Municipality Resource Management</p>
                        <p class="text-xs text-blue-600/80 font-medium sm:hidden">Resource Management</p>
                    </div>
                </div>
                
                <!-- Mobile menu button for auth options -->
                <div class="flex items-center space-x-2 sm:space-x-6" v-if="!page.props.auth.user">
                    <Link v-if="canLogin" :href="route('login')"
                        class="relative text-blue-700 hover:text-blue-800 font-semibold text-sm sm:text-lg transition-all duration-300 group">
                        <span class="relative z-10">Log In</span>
                        <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-blue-700 transition-all duration-300 group-hover:w-full"></span>
                    </Link>
                    <Link v-if="canRegister" :href="route('register')"
                        class="px-4 py-2 sm:px-8 sm:py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold text-sm sm:text-lg rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 hover:from-blue-700 hover:to-blue-800 backdrop-blur-sm border border-blue-500/20">
                        Sign Up
                    </Link>
                </div>
            </header>

            <!-- Enhanced Main Content -->
            <main class="flex-grow flex flex-col justify-center items-center px-4 sm:px-8 text-center py-8 sm:py-16">
                <div class="w-full max-w-5xl text-center space-y-8 sm:space-y-12">
                    <!-- Animated welcome text -->
                    <div class="space-y-4 sm:space-y-6">
                        <h1 class="text-3xl sm:text-6xl md:text-7xl font-bold text-gray-800 tracking-tight leading-tight animate-fade-in">
                            WELCOME TO
                        </h1>

                        <div class="relative">
                            <h2 class="text-4xl sm:text-6xl md:text-8xl font-black tracking-tight leading-none">
                                <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 drop-shadow-2xl animate-gradient">
                                    CALAPE
                                </span>
                            </h2>
                            <h3 class="text-2xl sm:text-4xl md:text-6xl font-bold text-blue-800/90 tracking-wide mt-2">
                                RESOURCELINK
                            </h3>
                            <!-- Decorative line -->
                            <div class="mx-auto mt-4 sm:mt-6 w-24 sm:w-32 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full shadow-lg"></div>
                        </div>
                    </div>

                    <!-- Enhanced description -->
                    <div class="space-y-6">
                        <p class="text-lg sm:text-2xl text-gray-700 max-w-3xl mx-auto leading-relaxed font-medium px-4 sm:px-0">
                            A smarter way to borrow, track, and manage resources in the Municipality of Calape.
                        </p>
                        
                        <!-- Feature highlights -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 mt-8 sm:mt-12 max-w-4xl mx-auto px-2 sm:px-0">
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 hover:bg-white/30">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 shadow-lg">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-base sm:text-lg text-gray-800 mb-2">Easy Booking</h4>
                                <p class="text-gray-600 text-xs sm:text-sm">Quick and simple resource reservation system</p>
                            </div>
                            
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 hover:bg-white/30">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 shadow-lg">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-base sm:text-lg text-gray-800 mb-2">Real-time Tracking</h4>
                                <p class="text-gray-600 text-xs sm:text-sm">Monitor resource availability instantly</p>
                            </div>
                            
                            <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-4 sm:p-6 border border-white/30 shadow-lg hover:shadow-xl transition-all duration-300 hover:bg-white/30 sm:col-span-2 md:col-span-1">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-cyan-600 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4 shadow-lg">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-base sm:text-lg text-gray-800 mb-2">Secure Management</h4>
                                <p class="text-gray-600 text-xs sm:text-sm">Safe and reliable resource handling</p>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced CTA button -->
                    <div class="mt-10 sm:mt-16 space-y-4 px-4 sm:px-0">
                        <Link :href="route('login')"
                            class="group relative inline-block">
                            <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                            <div class="relative px-8 py-4 sm:px-12 sm:py-5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-lg sm:text-xl rounded-2xl shadow-2xl transition-all duration-300 transform group-hover:scale-105 group-hover:from-blue-700 group-hover:to-blue-800 border border-blue-500/20">
                                <span class="flex items-center justify-center space-x-2 sm:space-x-3">
                                    <span>GET STARTED</span>
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </span>
                            </div>
                        </Link>
                        
                        <p class="text-gray-600 text-xs sm:text-sm font-medium px-4 sm:px-0">
                            Join hundreds of users already managing resources efficiently
                        </p>
                    </div>
                </div>
            </main>

            <!-- Enhanced Footer -->
            <footer class="w-full py-6 sm:py-8 text-center bg-white/10 backdrop-blur-xl border-t border-white/20 mt-auto">
                <div class="max-w-6xl mx-auto px-4 sm:px-8">
                    <div class="flex flex-col space-y-4">
                        <p class="text-gray-600 text-sm sm:text-base font-medium">
                            &copy; {{ new Date().getFullYear() }} Municipality of Calape. All rights reserved.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center items-center space-y-2 sm:space-y-0 sm:space-x-6 text-xs sm:text-sm text-gray-500">
                            <span class="flex items-center space-x-1">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                <span>System Online</span>
                            </span>
                            <span>Laravel v{{ laravelVersion }}</span>
                            <span>PHP v{{ phpVersion }}</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes gradient {
    0%, 100% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.animate-gradient {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

/* Mobile-specific optimizations */
@media (max-width: 640px) {
    .bg-cover {
        background-attachment: scroll;
    }
    
    /* Ensure touch targets are large enough */
    button, a {
        min-height: 44px;
    }
    
    /* Optimize scroll performance */
    .backdrop-blur-xl {
        backdrop-filter: blur(8px);
    }
    
    .backdrop-blur-sm {
        backdrop-filter: blur(4px);
    }
}

/* Improve performance on lower-end devices */
@media (prefers-reduced-motion: reduce) {
    .animate-pulse,
    .animate-fade-in,
    .animate-gradient {
        animation: none;
    }
    
    .transition-all,
    .transition-transform,
    .transition {
        transition: none;
    }
}

/* Fix for iOS Safari viewport issues */
@supports (-webkit-touch-callout: none) {
    .min-h-screen {
        min-height: -webkit-fill-available;
    }
}
</style>