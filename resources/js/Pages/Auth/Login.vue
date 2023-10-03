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
    form.clearErrors();
    form.post(route('login'), {
        preserveScroll: true,
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>

        <Head title="Log in" />


        <section class="absolute w-full top-0">
            <div class="absolute top-0 w-full h-screen bg-[radial-gradient(ellipse_at_right,_var(--tw-gradient-stops))] from-sky-400 to-indigo-900 ">
            </div>
            <div class="container mx-auto px-4 h-full">
                <div v-if="status" class="mb-4 font-medium text-base text-green-600">
                    {{ status }}
                </div>

                <div class="flex content-center items-center justify-center h-full">
                    <div class="w-full lg:w-4/12 px-4 pt-32 lg:pt-20 2xl:pt-64">
                        <div
                            class="relative flex flex-col min-w-0 break-words w-full mb-6 shadow-3xl rounded-lg backdrop-blur-lg bg-white/20 border-0">
                            <div class="rounded-t mb-0 px-6 py-4">
                                <div class="sm:mx-auto mb-2 sm:w-full sm:max-w-sm">
                                    <img class="mx-auto h-28 w-auto" :src="Logo" alt="Imagen" />
                                    <h2
                                        class="mt-2 text-center text-2xl font-bold leading-9 tracking-tight text-white dark:text-white">
                                        Iniciar Sesión</h2>
                                </div>
                                <div v-if="usePage().props.flash.error" class="bg-red-600 text-white text-sm p-2">
                                    {{ usePage().props.flash.error }}
                                </div>
                                <hr class="mt-4 border-b-2 border-white">
                            </div>
                            <div class="flex-auto px-2 lg:px-10 py-10 pt-0">
                                <form @submit.prevent="submit" class="space-y-3">
                                    <!-- Email input -->
                                    <div class="relative mb-4">
                                        <InputLabel for="login" value="Usuario"
                                            class="block text-base xl:text-2xl font-medium leading-6 text-white" />
                                        <div class="mt-1">
                                            <TextInput id="login" type="text"  class="rounded-md py-2"  v-model="form.login" required
                                                autocomplete="login" />
                                            <InputError class="mt-2 text-xs" :message="form.errors.login" />

                                        </div>
                                    </div>


                                    <!-- Password input -->
                                    <div>
                                        <InputLabel for="password" value="Contraseña"
                                            class="block text-base lg:text-lg xl:text-2xl rounded-md  font-medium leading-6 text-white" />
                                        <div class="mt-1">
                                            <TextInput id="password" type="password" class="rounded-md py-2" current-password
                                                v-model="form.password" required />
                                            <InputError class="mt-2 text-xs" :message="form.errors.password" />
                                        </div>
                                    </div>
                                    <!-- Login button -->
                                    <div class="text-center pt-5 lg:text-left">

                                        <PrimaryButton
                                            class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-3 text-xl font-medium leading-6 text-white shadow-lg hover:bg-blue-500"
                                            :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                                            Iniciar sesión
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


        </section>

</GuestLayout></template>
