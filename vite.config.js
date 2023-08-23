import { defineConfig,splitVendorChunkPlugin } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
//import { fileURLToPath, URL } from 'node:url';
export default defineConfig({

    plugins: [
        //splitVendorChunkPlugin(),
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    /*resolve: {
        alias: {
            '@': fileURLToPath(new URL('./src', import.meta.url))
        }
    },*/
    /*optimizeDeps: {
        include: [
          "@fawmi/vue-google-maps",
          "fast-deep-equal",
        ],
      },*/
   /* build: {
        rollupOptions: {

          input: 'resources/js/app.js',
          output: {

            entryFileNames: (`[name][hash].js`),
            chunkFileNames: (`assets/[name][hash].js`),
            assetFileNames: ({ name }) => {

              if (/\.css$/.test(name ?? '')) {
                return 'assets/[name]-[hash][extname]';
              }


              // ref: https://rollupjs.org/guide/en/#outputassetfilenames
              return 'assets/[name]-[hash][extname]';
            },
          }
        }
      }*/
});
