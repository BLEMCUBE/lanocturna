import { ref } from "vue";

window.URL = window.URL || window.webkitURL;

// Leer datos de imagen
function useImageFilePreview(file) {
	return window.URL.createObjectURL(file);
}

// vista previa local
function useQueuePreview(fileArr) {
	// Lista de múltiples imágenes y múltiples videos
	const previewMap = {};

	//índice de clasificación
	let idx = 0;
	for (const file of fileArr) {
		const fileData = useImageFilePreview(file);
		previewMap[idx] = fileData;
		idx++;
	}

	return previewMap;
}

export function useFileUpdate() {
	// Vista previa del archivo
	const previewMap = ref({});

	// inicialización
	const initData = () => {
		previewMap.value = {};
	};

	// Seleccionar varios archivos
	const setFile = async (file = []) => {
		initData();
		previewMap.value = useQueuePreview(file);
		console.log(previewMap.value);
	};

	return { setFile, previewMap };
}
