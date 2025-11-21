<script setup>

import { ref, onMounted, computed, watch, onUnmounted } from "vue";
import { usePage } from '@inertiajs/vue3';
import AppTopBar from '@/Layouts/AppTopBar.vue';
import Breadcrumb from '@/Components/Breadcrumb.vue';
import AppSideBar from '@/Layouts/AppSideBar.vue'
import { useLayout } from '@/composables/layout';
import audio_envio from '@/assets/audio_envio.mp3'
import Loader from "@/Components/Loader.vue";
import { useEchoStore } from '@/stores/echoStore'
import { useToast } from "primevue/usetoast";

const toast = useToast();
const echoStore = useEchoStore()
const visible = ref(false);
const { configuracion } = usePage().props.auth
const { layoutConfig, layoutState, isSidebarActive } = useLayout();
const outsideClickListener = ref(null);
const props = defineProps({
	pagina: {
		type: Array,
		default: [],
	},

});
const subscriptions = [
	{
		channel: 'venta',
		events: ['.envio']
	},
	/*{
		channel: 'ml',
		events: ['.question']
	}*/
]

onMounted(() => {
	const pKey = getConfig('pusher-key')
	const pCluster = getConfig('pusher-cluster')

	echoStore.setKey(pKey)
	echoStore.setCluster(pCluster)
	echoStore.initEcho()

	subscriptions.forEach(item => {
		const channel = echoStore.subscribeChannel(item.channel)

		item.events.forEach(event => {
			channel.listen(event, (payload) => {
				switch (event) {
					case '.envio':
						showModal()
						break;
					case '.question':
							 showToast('success', 'Pregunta', 'Nueva Pregunta Recibida')
							 mlToast()
						break;

					default:
						break;
				}


			})
		})
	})

})
onUnmounted(() => {
  echoStore.channels.forEach((_, name) => {
    echoStore.unsubscribe(name)
  })
})

const showModal = () => {
	visible.value = true;
	playSound(audio_envio)
	setTimeout(() => {
		visible.value = false
		window.open(self.location, '_self');
	}, "5000");
}

const mlToast = () => {
	playSound(audio_envio)
	setTimeout(() => {
		window.open(self.location, '_self');
	}, "3000");
}


const playSound = (sound) => {
	if (sound) {
		var audio = new Audio(sound);
		audio.play();
	}
}

watch(isSidebarActive, (newVal) => {
	if (newVal) {
		bindOutsideClickListener();
	} else {
		unbindOutsideClickListener();
	}
});


const containerClass = computed(() => {
	return {
		'layout-theme-light': layoutConfig.darkTheme.value === 'light',
		'layout-overlay': layoutConfig.menuMode.value === 'overlay',
		'layout-static': layoutConfig.menuMode.value === 'static',
		'layout-static-inactive': layoutState.staticMenuDesktopInactive.value && layoutConfig.menuMode.value === 'static',
		'layout-overlay-active': layoutState.overlayMenuActive.value,
		'layout-mobile-active': layoutState.staticMenuMobileActive.value,
		'p-input-filled': layoutConfig.inputStyle.value === 'filled',
		'p-ripple-disabled': !layoutConfig.ripple.value
	};
});
const bindOutsideClickListener = () => {
	if (!outsideClickListener.value) {
		outsideClickListener.value = (event) => {
			if (isOutsideClicked(event)) {
				layoutState.overlayMenuActive.value = false;
				layoutState.staticMenuMobileActive.value = false;
				layoutState.menuHoverActive.value = false;
			}
		};
		document.addEventListener('click', outsideClickListener.value);
	}
};
const unbindOutsideClickListener = () => {
	if (outsideClickListener.value) {
		document.removeEventListener('click', outsideClickListener);
		outsideClickListener.value = null;
	}
};
const isOutsideClicked = (event) => {
	const sidebarEl = document.querySelector('.layout-sidebar');
	const topbarEl = document.querySelector('.layout-menu-button');

	return !(sidebarEl.isSameNode(event.target) || sidebarEl.contains(event.target) || topbarEl.isSameNode(event.target) || topbarEl.contains(event.target));
};

const getConfig = (dato) => {

	var valores = configuracion.filter((p) => {
		if (p.slug === dato) {
			return p
		} else {
			return ''
		}
	}
	)[0]
	return valores.value
}

const showToast = (tipo, titulo, mensaje) => {
    toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
};

</script>

<template>
	<Toast />
	<div class="layout-wrapper layout-static" :class="containerClass">
		<!--Top-->
		<Loader />
		<AppTopBar></AppTopBar>
		<div class="layout-sidebar bg-primary-900">
			<AppSideBar></AppSideBar>
		</div>

		<div class="layout-main-container">
			<div class="layout-main">
				<div class="col-span-12 px-2 flex justify-start text-base font-medium">
					<Breadcrumb :pagina="pagina"></Breadcrumb>

				</div>
				<div class="card flex justify-content-center">
					<Dialog v-model:visible="visible" modal header="Edit Profile" :style="{ width: '28rem' }">
						<template #header>
							<div class="inline-flex align-items-center justify-content-center gap-2">

								<span class="font-bold white-space-nowrap">Envios</span>
							</div>
						</template>
						<span class="text-center text-xl block mb-5">Se ha creado un envio</span>

						<template #footer>

							<Button label="Aceptar" severity="primary" @click="visible = false" autofocus />
						</template>
					</Dialog>
				</div>

				<div class="grid grid-cols-12 gap-4 px-2 pt-3 overflow-x-auto">

					<slot></slot>
				</div>
			</div>
		</div>
		<div class="layout-mask"></div>
	</div>
</template>

<style scoped></style>
