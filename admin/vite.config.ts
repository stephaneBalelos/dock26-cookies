import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    define: {
        'process.env': {},
    },
    build: {
        lib: {
            entry: {
                admin: 'src/main.ts',
            },
            name: 'dock26_cookies',
            fileName: (format, entryName) => `assets/js/${entryName}.${format}.js`,
            formats: ['iife'],
        },
        rollupOptions: {
            output: {
                entryFileNames: 'assets/js/[name].[format].js',
                assetFileNames: 'assets/[ext]/[name].[ext]',
            }
        },
    },
    resolve: {
        alias: {
            '@': '/src',
        },
    },
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: [
                    'import',
                    'mixed-decls',
                    'color-functions',
                    'global-builtin',
                ],
            }
        }
    }
})