<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Logo from '/public/images/logo.png';
import { onMounted } from 'vue';



onMounted(() => {

})

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const submit = () => {

    form.post(route('login'), {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />

        <section class="h-screen">
            <div class="h-full mx-6">
                <div v-if="status" class="mb-4 font-medium text-base text-green-600">
                    {{ status }}
                </div>
                <!-- Left column container with background-->
                <div class="g-6 flex h-full flex-wrap items-center justify-center lg:justify-center">
                    <div
                        class="shrink-1 mb-12 hidden md:block grow-0 basis-auto md:mb-0 md:w-9/12 md:shrink-0 lg:w-6/12 xl:w-6/12">
                        <img src="https://tecdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                            class="w-full" alt="Sample image" />

                    </div>

                    <!-- Right column container -->
                    <div class="mb-12 md:mb-0 md:w-8/12 lg:w-4/12 xl:w-4/12">
                        <div class="sm:mx-auto mb-5 sm:w-full sm:max-w-sm">
                            <img class="mx-auto h-36 w-auto" :src="Logo" alt="Imagen" />
                            <h2
                                class="mt-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                                Iniciar Sesión</h2>
                        </div>
                        <div v-if="usePage().props.flash.error" class="bg-red-600 text-white text-sm p-2">
                            {{ usePage().props.flash.error }}
                        </div>
                        <form @submit.prevent="submit" class="space-y-3">
                            <!-- Email input -->
                            <div class="relative mb-6">
                                <InputLabel for="login" value="Usuario"
                                    class="block text-base font-bold leading-6 text-gray-900" />
                                <div class="mt-1">
                                    <TextInput id="login" type="text" v-model="form.login" required autocomplete="login" />
                                    <InputError class="mt-2 text-xs" :message="form.errors.login" />

                                </div>
                            </div>


                            <!-- Password input -->
                            <div>
                                <InputLabel for="password" value="Contraseña"
                                    class="block text-base font-bold leading-6 text-gray-900" />
                                <div class="mt-1">
                                    <TextInput id="password" type="password" current-password v-model="form.password"
                                        required />
                                    <InputError class="mt-2 text-xs" :message="form.errors.password" />
                                </div>
                            </div>
                            <!-- Login button -->
                            <div class="text-center lg:text-left">

                                <PrimaryButton
                                    class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-1.5 text-base font-semibold leading-6 text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                                    :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                                    Iniciar sesión
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>


    </GuestLayout>
</template>
