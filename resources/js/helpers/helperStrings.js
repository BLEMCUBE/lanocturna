export default {
	install: (app, options) => {

		//primera letra mayuscula
		app.config.globalProperties.$capitalizeFirstLetter = (key) => {
			return key.charAt(0).toUpperCase() + key.slice(1);
		},
		   //mayusculas
		   app.config.globalProperties.$mayuscula = (key) => {
			return key.toUpperCase();
		}
		//mayusculas
		app.config.globalProperties.$numbersOnly=(evt)=> {
			evt = (evt) ? evt : window.e;
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
			  evt.preventDefault();;
			} else {
			  return true;
			}
		  }

	  app.provide('helperStrings', options)
	}
  }
