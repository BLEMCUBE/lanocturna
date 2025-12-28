import{_ as h}from"./AppLayout-uReGjGr-.js";import{K as u,Z as v}from"./@inertiajs-CMyvZJ1U.js";import{aI as b}from"./primevue-DAb-R_om.js";import{r as m,o as y,R as l,f as w,g as P,W as s,E as k,Y as o,j as t,D as n,C as g,F as T}from"./@vue-BLfC86mR.js";import"./logo-C8Jea0Rc.js";import"./pinia-IH2nVPr3.js";import"./@vueuse-BEDuuF1-.js";import"./sweetalert2-DhUVDY3P.js";import"./@headlessui-CeD_lX9I.js";import"./@heroicons-CatyAbtO.js";import"./loader-D-Kjbgh8.js";import"./laravel-echo-CPaRdDZ5.js";import"./pusher-js-Ck_2Ecjm.js";import"./axios-ngrFHoWO.js";import"./deepmerge-Bh_OW9zV.js";import"./qs-ErqSC6LE.js";import"./side-channel-QUUGuE8A.js";import"./get-intrinsic-Dq22P_nh.js";import"./es-object-atoms-BR76m4z7.js";import"./es-errors-DjMuYndc.js";import"./math-intrinsics-DP1tGiab.js";import"./gopd-C1lCZ5Qs.js";import"./es-define-property-D_7cP-M3.js";import"./has-symbols-BaUvM3gb.js";import"./get-proto-B8JEaMPD.js";import"./dunder-proto-DWby8p2a.js";import"./call-bind-apply-helpers-CZLbXZmW.js";import"./function-bind-CHqF18-c.js";import"./hasown-DwiY0sux.js";import"./call-bind-BmU8hBQf.js";import"./object-inspect-ronoqz_t.js";import"./nprogress-Di1940OM.js";import"./lodash.clonedeep-D_Ud3gdH.js";import"./lodash.isequal-BY8B19Vp.js";const L={class:"px-4 py-3 mb-4 bg-white col-span-12 dark:border-gray-700 dark:bg-gray-800"},$={class:"align-middle"},D={class:"flex justify-content-end text-md"},M=["innerHTML"],C={class:"text-xs text-start"},N=["innerHTML"],F={class:"my-4 flex gap-3"},H={class:"bg-blue-500 text-white p-4 rounded-lg shadow w-full"},V=t("i",{class:"fas fa-comment fa-flip-horizontal fa-3x"},null,-1),j={class:"text-xs"},R={class:"text-xs text-blue-100 mt-3"},ht={__name:"PreguntaHistorial",setup(I){const{tienda:f}=u().props,r=`Historial de preguntas "${f}"`,p=m([]),i=m({global:{value:null,matchMode:b.CONTAINS}});return y(()=>{p.value=u().props.datos.data.map(a=>({...a,productoDisplay:`<div class="flex flex-col justify-center items-center py-3 w-56">
									<img src="${a.producto.thumbnail}"
										class="w-24 h-24 object-contain rounded-md border" />
									<div class="w-full">
										<h2 class="text-gray-800 text-sm text-center font-semibold leading-tight">
											${a.producto.title}
										</h2>
										<p class="text-xs text-center text-gray-500 mt-1"># ${a.producto.id}</p>
									</div>
			</div>`,pregunta:`<div class="bg-gray-100 border border-gray-200 w-full p-4 rounded-lg">
										<i class="fas fa-comment fa-3x text-gray-500"></i>
										<p class="text-gray-800">
											${a.pregunta}
										</p>
										<p class="text-xs text-gray-500 mt-3">
											Preguntado el ${a.date_created} — Usuario: <span
												class="font-semibold">${a.usuario.nickname}</span>
																<span v-if="item.usuario.city" class="px-1">

											| <i class="fa fa-map-marker-alt text-gray-500"></i>  ${a.usuario.city}
											,<span v-if="item.usuario.state">
												 ${a.usuario.state}
											</span>
										</span>
										</p>

									</div>
									</div>`}))}),(a,d)=>{const x=l("InputText"),c=l("Column"),_=l("DataTable");return P(),w(T,null,[s(k(v),{title:r}),s(h,{pagina:[{label:r,link:!1}]},{default:o(()=>[t("div",L,[t("div",{class:"px-5 pb-2 col-span-full flex justify-between items-center"},[t("h5",{class:"text-2xl font-medium"},n(r))]),t("div",$,[s(_,{size:"small",showGridlines:"",filters:i.value,value:p.value,paginator:!0,rows:50,pt:{bodyRow:{class:""}},rowsPerPageOptions:[5,10,20,50],globalFilterFields:["productoDisplay","pregunta"],paginatorTemplate:"FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown",tableStyle:"width: 100%"},{header:o(()=>[t("div",D,[s(x,{class:"w-1/2",modelValue:i.value.global.value,"onUpdate:modelValue":d[0]||(d[0]=e=>i.value.global.value=e),placeholder:"Usuario/Producto"},null,8,["modelValue"])])]),empty:o(()=>[g(" No existe Resultado ")]),loading:o(()=>[g(" Cargando... ")]),default:o(()=>[s(c,{field:"productoDisplay",header:"Producto"},{body:o(e=>[t("div",{innerHTML:e.data.productoDisplay},null,8,M)]),_:1}),s(c,{class:"px-5",field:"pregunta",header:"Pregunta"},{body:o(e=>[t("div",C,[t("div",{class:"mt-6 flex gap-3",innerHTML:e.data.pregunta},null,8,N),t("div",F,[t("div",H,[V,t("p",j,n(e.data.respuesta),1),t("p",R," Respondido el "+n(e.data.fecha_respuesta),1)])])])]),_:1})]),_:1},8,["filters","value"])])])]),_:1},8,["pagina"])],64)}}};export{ht as default};
