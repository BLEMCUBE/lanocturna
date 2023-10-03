
///** @type {import('tailwindcss').Config} */
export default {
    important:true,
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/js/**/*.vue',
        //"./src/**/*.{vue,js,ts,jsx,tsx}",
        './node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}',
        //'./node_modules/primeflex/primeflex.css'

    ],

    theme: {
        extend: {
            colors: {
                //primary: { "50": "#eff6ff", "100": "#dbeafe", "200": "#bfdbfe", "300": "#93c5fd", "400": "#60a5fa", "500": "#3b82f6", "600": "#2563eb", "700": "#1d4ed8", "800": "#1e40af", "900": "#1e3a8a", "950": "#172554" }
                primary: { "100": "#0063a9","900": "#013878" },
                secondary: { "100": "#f6d318","900": "#ffd700" },
                black:"#000000"
            },
            fontSize: {
                //'validation': '0.8rem',
                'xs': '0.9rem',
                'sm': '1rem',
            },
            fontFamily: {
                'body': [
                    'Roboto',
                    'ui-sans-serif',
                    'system-ui',
                    '-apple-system',
                    'system-ui',
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'Noto Sans',
                    'sans-serif',
                    'Apple Color Emoji',
                    'Segoe UI Emoji',
                    'Segoe UI Symbol',
                    'Noto Color Emoji'
                ],
                'sans': [
                    'Roboto',
                    'ui-sans-serif',
                    'system-ui',
                    '-apple-system',
                    'system-ui',
                    'Segoe UI',
                    'Roboto',
                    'Helvetica Neue',
                    'Arial',
                    'Noto Sans',
                    'sans-serif',
                    'Apple Color Emoji',
                    'Segoe UI Emoji',
                    'Segoe UI Symbol',
                    'Noto Color Emoji'
                ]
            },

        },
    },
    plugins:[
        require('@headlessui/tailwindcss')({ prefix: 'ui' })
    ],
    /*corePlugins: {
        preflight: true,
    },*/
    darkMode: "class",


};
