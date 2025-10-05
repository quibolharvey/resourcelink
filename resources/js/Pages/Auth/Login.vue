<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="max-w-md w-full mx-auto bg-white shadow-lg rounded-xl p-8 space-y-6">
            <!-- Status message -->
            <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 p-3 rounded-lg">
                {{ status }}
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-center text-gray-800">Welcome to CalapeResourceLink</h1>
            <p class="text-center text-gray-500 text-sm">
                Please sign in to continue
            </p>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="email" value="Email" class="font-medium text-gray-700" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="password" value="Password" class="font-medium text-gray-700" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>

                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-blue-600 hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>

                <PrimaryButton
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition transform hover:scale-[1.02]"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log In
                </PrimaryButton>
            </form>
            <p class="text-center text-sm text-gray-600">
                    Don’t have an account?
                    <Link :href="route('register')" class="font-medium text-blue-600 hover:text-blue-700">
                        Sign up
                    </Link>
                </p>
        </div>
    </GuestLayout>
</template>
