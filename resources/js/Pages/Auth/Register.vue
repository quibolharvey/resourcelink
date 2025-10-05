<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    phone_number: '',
    address: 'Calape/',
    password: '',
    password_confirmation: '',
});

const ADDRESS_PREFIX = 'Calape/';

const enforceAddressPrefix = () => {
    const current = form.address ?? '';
    if (!current.startsWith(ADDRESS_PREFIX)) {
        const suffix = current.replace(/^Calape\/?/, '');
        form.address = ADDRESS_PREFIX + suffix;
    }
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="max-w-md w-full mx-auto bg-white shadow-lg rounded-xl p-8 space-y-6">
            <!-- Heading -->
            <h1 class="text-2xl font-bold text-center text-gray-800">Create an Account</h1>
            <p class="text-center text-gray-500 text-sm">
                Fill in your details to get started
            </p>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <InputLabel for="name" value="Full Name" class="font-medium text-gray-700" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="given-name"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="email" value="Email" class="font-medium text-gray-700" />
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.email"
                        required
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="phone_number" value="Phone Number" class="font-medium text-gray-700" />
                    <TextInput
                        id="phone_number"
                        type="tel"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.phone_number"
                        required
                        autocomplete="tel"
                    />
                    <InputError class="mt-2" :message="form.errors.phone_number" />
                </div>

                <div>
                    <InputLabel for="address" value="Address / Barangay" class="font-medium text-gray-700" />
                    <TextInput
                        id="address"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.address"
                        @input="enforceAddressPrefix"
                        placeholder="Calape/Your Barangay"
                        required
                        autocomplete="street-address"
                    />
                    <InputError class="mt-2" :message="form.errors.address" />
                </div>

                <div>
                    <InputLabel for="password" value="Password" class="font-medium text-gray-700" />
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" value="Confirm Password" class="font-medium text-gray-700" />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>

                <div class="flex items-center justify-between">
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-blue-600 hover:underline"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton
                        class="py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition transform hover:scale-[1.02]"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Register
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </GuestLayout>
</template>
