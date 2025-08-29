import { defineConfig } from 'vite'

export default defineConfig({
    build: {
        lib: {
            entry: {
                main: 'src/main.ts',
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