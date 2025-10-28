import { toRefs, reactive, computed } from 'vue';
import { useToast } from "primevue/usetoast";


const layoutConfig = reactive({
	//ripple: false,

});


export function useCustomToast() {
	const toast = useToast();

	const setShow = (tipo, titulo, mensaje) => {
		toast.add({ severity: tipo, summary: titulo, detail: mensaje, life: 3000 });
	};

	return {
		setShow
	};
}
