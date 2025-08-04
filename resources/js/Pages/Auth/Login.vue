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

	<Head title="Log in" />


	<div
		class='flex items-center justify-center min-h-screen from-sky-900 via-indigo-700 to-indigo-500 bg-gradient-to-br'>
		<div class='w-full max-w-sm px-10 py-8 mx-auto bg-white border rounded-lg shadow-2xl'>

			<div class="sm:mx-auto mb-2 sm:w-full sm:max-w-sm">
				<img class="mx-auto h-28 w-auto" :src="Logo" alt="Imagen" />
				<h2 class="text-xl font-semibold text-center">
					Iniciar Sesión</h2>
			</div>
			<form @submit.prevent="submit" class="space-y-3">
				<!-- Email input -->
				<div class="relative mb-4">
					<InputLabel for="login" value="Usuario" class="block text-base font-medium leading-6" />
					<div class="mt-1">
						<TextInput id="login" type="text"
							class="border w-full py-2 px-2 rounded shadow hover:border-indigo-600 ring-1 ring-inset ring-gray-300"
							v-model="form.login" required autocomplete="login" />
						<InputError class="mt-2 text-xs" :message="form.errors.login" />

					</div>
				</div>

				<!-- Password input -->
				<div>
					<InputLabel for="password" value="Contraseña"
						class="bblock text-base font-medium leading-6" />
					<div class="mt-1">
						<TextInput id="password" type="password" class="border w-full py-2 px-2 rounded shadow hover:border-indigo-600 ring-1 ring-inset ring-gray-300" current-password
							v-model="form.password" required />
						<InputError class="mt-2 text-xs" :message="form.errors.password" />
					</div>
				</div>
				<!-- Login button -->
				<div class="text-center pt-3 lg:text-left">

					<PrimaryButton
						class="flex w-full justify-center rounded-md bg-blue-600 px-3 py-3 text-xl font-medium leading-6 text-white shadow-lg hover:bg-blue-500"
						:class="{ 'opacity-50': form.processing }" :disabled="form.processing">
						Iniciar sesión
					</PrimaryButton>
				</div>
			</form>

		</div>
	</div>
</template>
