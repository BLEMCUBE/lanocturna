<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, reactive } from 'vue';
const { permissions } = usePage().props.auth
import DatePicker from 'vue-datepicker-next';
import 'vue-datepicker-next/index.css';
import { endOfMonth, endOfYear, startOfMonth, subDays, startOfYear } from 'date-fns';
import moment from 'moment';
import 'vue-datepicker-next/locale/es.es.js';
import VueApexCharts from "vue3-apexcharts";

const datos_ventas = ref([]);
const { datos_grafico_ventas } = usePage().props
const date = ref([new Date(), new Date()]);

//filtrado
const filtrado = (value) => {
    if (value[0] != null && value[1] != null) {
        router.get(
            "/inicio/",
            {
                inicio: moment(value[0]).format('YYYY-MM-DD'),
                fin: moment(value[1]).format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    grafVentas.value.series[0].data = [];
                    grafVentas.value.chartOptions.labels = [];
                    grafVentas.value.series[0].data = usePage().props.datos_grafico_ventas.datos;
                    grafVentas.value.chartOptions.labels = usePage().props.datos_grafico_ventas.categoria;
                }
            }
        );
    }
}
onMounted(() => {
    grafVentas.value.series[0].data = [];
    grafVentas.value.chartOptions.labels = [];
    grafVentas.value.series[0].data = usePage().props.datos_grafico_ventas.datos;
    grafVentas.value.chartOptions.labels = usePage().props.datos_grafico_ventas.categoria;

});
//grafico total ventas

const grafVentas = ref({
    series: [{
        name: 'VENTAS',
        type: 'area',
        data: []
    }],

    chartOptions: {
        chart: {
            height: 350,
            type: 'area',
            stacked: false,
            toolbar: {
                show: true,
                offsetX: 0,
                offsetY: 0,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: false,
                    customIcons: []
                },
                export: {
                    csv: {
                        filename: "ventas",
                        columnDelimiter: ',',
                        headerCategory: 'anio',
                        headerValue: 'value',
                        dateFormatter(timestamp) {
                            return new Date(timestamp).toDateString()
                        }
                    },
                    svg: {
                        filename: "ventas",
                    },
                    png: {
                        filename: "ventas",
                    }
                },
            },
        },
        stroke: {
            width: [2, 2],
            curve: 'straight'

        },
        responsive: [
            {
                breakpoint: 480,
                options: {
                    chart: {
                        width: 100
                    },
                    legend: {
                        position: "bottom"
                    }
                }
            }
        ],
        plotOptions: {
            bar: {
                columnWidth: '50%'
            }
        },

        fill: {
            opacity: [0.85, 0.25, 1],
            gradient: {
                inverseColors: false,
                shade: 'light',
                type: "vertical",
                opacityFrom: 0.85,
                opacityTo: 0.55,
                stops: [0, 100, 100, 100]
            }
        },
        labels: [],
        markers: {
            size: 3,
            strokeWidth: 2
        },
        xaxis: {
            //type: 'datetime'
            title: {
                    text: "VENTAS",
                    style: {
                        fontSize:16
                        //color: "#0EA5E9"
                    }
                }
        },
        yaxis: [
            {
                axisTicks: {
                    show: false
                },
                axisBorder: {
                    show: true,
                    //  color: "#0EA5E9"
                },

                labels: {
                    formatter: function (val) {
                        return "$ " + val.toFixed(2)
                    }
                },
                title: {
                    text: "Ventas",
                    style: {
                        //color: "#0EA5E9"
                    }
                }
            }
        ],

    }
})
const totalVentasLinea = ref({
    chart: {
        type: "area",
        stacked: false,
        toolbar: {
            show: true,
            offsetX: 0,
            offsetY: 0,
            tools: {
                download: true,
                selection: false,
                zoom: false,
                zoomin: false,
                zoomout: false,
                pan: false,
                reset: false,
                customIcons: []
            },
            export: {
                csv: {
                    filename: "ventas",
                    columnDelimiter: ',',
                    headerCategory: 'anio',
                    headerValue: 'value',
                    dateFormatter(timestamp) {
                        return new Date(timestamp).toDateString()
                    }
                },
                svg: {
                    filename: "ventas",
                },
                png: {
                    filename: "ventas",
                }
            },
        },
    },
    dataLabels: {
        enabled: false,
        formatter: function (val) {
            return "$ " + val.toFixed(2)
        },
        offsetY: -6,
        style: {
            fontSize: '10px',
            colors: ["#304758"]
        }
    },
    markers: {
        size: 3,
        strokeWidth: 2
    },



    xaxis: {
        categories: datos_ventas.categoria
    },
    plotOptions: {
        bar: {
            horizontal: false
        }
    },
    yaxis: [
        {
            axisTicks: {
                show: false
            },
            axisBorder: {
                show: true,
                //  color: "#0EA5E9"
            },

            labels: {
                formatter: function (val) {
                    return "$ " + val.toFixed(2)
                }
            },
            title: {
                text: "Ventas",
                style: {
                    //color: "#0EA5E9"
                }
            }
        }
    ],

    legend: {
        horizontalAlign: "botton",
        offsetX: 20
    },

})
//*datepicker  */
const shortcuts = [
    {
        text: 'Hoy',
        onClick() {
            const date = [new Date(), new Date()];
            return date;
        },
    },
    {
        text: 'Ayer',
        onClick() {
            const date = [subDays(new Date(), 1), subDays(new Date(), 1)];
            //date.setTime(date.getTime() - 3600 * 1000 * 24);

            return date;
        },
    },
    {
        text: 'Este mes',
        onClick() {
            const date = [startOfMonth(new Date()), endOfMonth(new Date())];

            return date;
        },
    },
    {
        text: 'Este año',
        onClick() {
            const date = [startOfYear(new Date()), endOfYear(new Date())];

            return date;
        },
    },
]



</script>

<template>
    <Head title="Inicio" />
    <AppLayout>

        <div class="card px-4 mb-4 col-span-12 rounded-lg">
            <div class="px-  col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">Panel</h5>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-4 mb-2" v-if="permissions.includes('grafico-ventas')">
                <!--Contenido-->

                <!--total ventas-->
                <div class="grid grid-cols-1 col-span-full gap-4 lg:grid-cols-12  mt-2 2lg:grid-cols-12">
                    <div class="col-span-1 md:col-span-3 lg:col-span-3">
                        <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                            v-model:value="date" :shortcuts="shortcuts" lang="es"
                            placeholder="seleccione Fecha"></date-picker>
                    </div>
                </div>


                <div class="col-span-12  bg-white px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm ">
                    <h3 class="flex items-center mb-4 text-lg font-semibold text-gray-900">Gráfico de Ventas
                    </h3>
                    <div id="chart">
                        <VueApexCharts type="area" height="350" class="w-auto" :options="grafVentas.chartOptions"
                            :series="grafVentas.series">
                        </VueApexCharts>
                    </div>
                </div>
                <!--

                    <Link :href="route('ventas.index')" method="get" as="button"   v-if="permissions.includes('lista-ventas')"
                    class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer  hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                    <div class="flex justify-center text-white items-center p-2 mx-auto mb-2">
                        <i class="fa fa-shopping-cart fa-2x"></i>
                    </div>
                    <div class="font-medium text-center text-white dark:text-gray-400">Ventas</div>
                </Link>

                <Link :href="route('usuarios.index')" method="get" as="button"  v-if="permissions.includes('importaciones')"
                class="p-4 col-span-12 lg:col-span-3  rounded-lg bg-primary-900 shadow-lg cursor-pointer  hover:bg-primary-100 dark:hover:bg-gray-600 dark:bg-gray-700">
                <div class="flex justify-center text-white items-center p-2 mx-auto mb-2">
                    <i class="fa fa-ship icon fa-2x"></i>
                </div>
                <div class="font-medium text-center text-white dark:text-gray-400">Importaciones</div>
            </Link>
        -->

                <!--Contenido-->
            </div>
        </div>

    </AppLayout>
</template>
