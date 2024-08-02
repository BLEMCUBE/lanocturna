export default {
	install: (app, options) => {
		app.config.globalProperties.$numberFormat = (value, location = 'es-UY') => {
			var numero = Intl.NumberFormat(
				location, {
				style: 'decimal',
				//style: 'currency',
				//currency: 'HNL',
				maximumFractionDigits: 2,
				// These options are needed to round to whole numbers if that's what you want.
				minimumFractionDigits: 2, // (this suffices for whole numbers, but will print 2500.10 as $2,500.1)
				//maximumFractionDigits: 0, // (causes 2500.99 to be printed as $2,501)
			})
			return numero.format(value);
		}

		//formato numero dolar
		app.config.globalProperties.$numFormatWithDollar = (key) => {
			return key ? '$' + Number(key).toLocaleString() : '-';
		}


		app.provide('helperNumbers', options)
	}
}
