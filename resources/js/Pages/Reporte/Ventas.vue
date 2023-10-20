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

const date = ref();
const chart1 = ref();
const titulo = "Reporte Ventas"

//filtrado
const filtrado = (value) => {
    if (value[0] != null && value[1] != null) {
        router.get(
            "/reportes-ventas/",
            {
                inicio: moment(value[0]).format('YYYY-MM-DD'),
                fin: moment(value[1]).format('YYYY-MM-DD')
            },
            {
                preserveState: true,
                onSuccess: () => {
                    chart1.value.updateSeries([{
                        data: usePage().props.datos_grafico_ventas.datos
                    }])

                    chart1.value.updateOptions({

                        labels: usePage().props.datos_grafico_ventas.categoria,

                    })

                }
            }
        );
        date.value = [moment(value[0]).format('YYYY-MM-DD'), moment(value[1]).format('YYYY-MM-DD')];
    }
}
onMounted(() => {
    date.value = [subDays(new Date(), 30), new Date()];
    filtrado(date.value);


});

//grafico total ventas
const series = ref([]);
const chartOptions = ref({
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
                fontSize: 16
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
)

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
    <Head title="Reporte Ventas" />
    <AppLayout :pagina="[{ 'label': 'Reportes', link: false }, { 'label': titulo, link: false }]">

        <div class="card px-4 mb-4 col-span-12 rounded-lg">
            <div class="col-span-full flex justify-between items-center">
                <h5 class="text-2xl font-medium">Reporte Ventas</h5>
            </div>
            <div class="grid grid-cols-12 gap-4 mt-4 mb-2">
                <!--Contenido-->
                <!--total ventas-->
                <div class="grid grid-cols-1 col-span-full gap-4 lg:grid-cols-12  mt-2 2lg:grid-cols-12">
                    <div class="col-span-1 md:col-span-3 lg:col-span-3">
                        <date-picker @change="filtrado" type="date" range value-type="YYYY-MM-DD" format="DD/MM/YYYY"
                            v-model:value="date" :shortcuts="shortcuts" lang="es" :clearable="false"
                            placeholder="seleccione Fecha"></date-picker>
                    </div>
                </div>

                <div class="col-span-12  bg-white px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm ">
                    <h3 class="flex items-center mb-4 text-lg font-semibold text-gray-900">Gráfico de Ventas
                    </h3>
                    <div id="chart">
                        <VueApexCharts ref="chart1" type="area" height="350" class="w-auto" :options="chartOptions"
                            :series="series">
                        </VueApexCharts>
                    </div>
                </div>

                <!--Contenido-->
            </div>
        </div>

    </AppLayout>
</template>
