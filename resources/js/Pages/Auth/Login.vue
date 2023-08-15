<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Logo from '/public/images/logo.jpg';
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
                <div class="g-6 flex h-full flex-wrap items-center justify-center lg:justify-between">
                    <div
                        class="shrink-1 mb-12 hidden md:block grow-0 basis-auto md:mb-0 md:w-9/12 md:shrink-0 lg:w-6/12 xl:w-6/12">
                        <img src="https://tecdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                            class="w-full" alt="Sample image" />

                    </div>

                    <!-- Right column container -->
                    <div class="mb-12 md:mb-0 md:w-8/12 lg:w-5/12 xl:w-5/12">
                        <div class="sm:mx-auto mb-5 sm:w-full sm:max-w-sm">
                            <img class="mx-auto h-16 w-auto" :src="Logo" alt="Imagen" />
                            <h2
                                class="mt-2 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                                Iniciar Sesión</h2>
                        </div>
                        <div v-if="usePage().props.flash.error" class="bg-red-600 text-white text-sm p-2">
                            {{ usePage().props.flash.error }}
                        </div>
                        <form @submit.prevent="submit" class="space-y-3">
                            <!-- Email input -->
                            <div class="relative mb-6" data-te-input-wrapper-init>
                                <input id="login" type="text" v-model="form.login" required placeholder="Usuario"
                                    class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0" />
                                <label for="login"
                                    class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[1.15rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[te-input-state-active]:-translate-y-[1.15rem] peer-data-[te-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-200 dark:peer-focus:text-primary">Usuario
                                </label>
                            </div>

                            <!-- Password input -->
                            <div class="relative mb-6" data-te-input-wrapper-init>
                                <input type="password" required
                                    class="peer block min-h-[auto] w-full rounded border-0 bg-transparent px-3 py-[0.32rem] leading-[2.15] outline-none transition-all duration-200 ease-linear focus:placeholder:opacity-100 data-[te-input-state-active]:placeholder:opacity-100 motion-reduce:transition-none dark:text-neutral-200 dark:placeholder:text-neutral-200 [&:not([data-te-input-placeholder-active])]:placeholder:opacity-0"
                                    id="password" placeholder="Contraseña:" v-model="form.password" />
                                <label for="password"
                                    class="pointer-events-none absolute left-3 top-0 mb-0 max-w-[90%] origin-[0_0] truncate pt-[0.37rem] leading-[2.15] text-neutral-500 transition-all duration-200 ease-out peer-focus:-translate-y-[1.15rem] peer-focus:scale-[0.8] peer-focus:text-primary peer-data-[te-input-state-active]:-translate-y-[1.15rem] peer-data-[te-input-state-active]:scale-[0.8] motion-reduce:transition-none dark:text-neutral-200 dark:peer-focus:text-primary">Contraseña
                                </label>
                            </div>

                            <!-- Login button -->
                            <div class="text-center lg:text-left">

                                <PrimaryButton type="submit" data-te-ripple-init data-te-ripple-color="light"
                                    :class="{ 'opacity-50': form.processing }" :disabled="form.processing">
                                    Iniciar Sesión
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>


    </GuestLayout>
</template>
