import { defineConfig,splitVendorChunkPlugin } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        splitVendorChunkPlugin(),
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
    /*optimizeDeps: {
        include: [
          "@fawmi/vue-google-maps",
          "fast-deep-equal",
        ],
      },*/
    build: {
        rollupOptions: {

          input: 'resources/js/app.js',
          output: {

            entryFileNames: (`[name][hash].js`),
            chunkFileNames: (`assets/[name][hash].js`),
            assetFileNames: ({ name }) => {
              /*if (/\.(gif|jpe?g|png|svg)$/.test(name ?? '')) {
                return 'assets/images/[name]-[hash][extname]';
              }
              if (/\.(json)$/.test(name ?? '')) {
                return 'assets/[name]-[hash][extname]';
              }*/
              if (/\.css$/.test(name ?? '')) {
                return 'assets/[name]-[hash][extname]';
              }

              // default value
              // ref: https://rollupjs.org/guide/en/#outputassetfilenames
              return 'assets/[name]-[hash][extname]';
            },
          }
        }
      }
});
