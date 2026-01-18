import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/index.scss',
            ],
            refresh: true,
        }),
    ],
    css: {
        postcss: {
            plugins: [
                require("tailwindcss")({
                    config: "./tailwind.config.js",
                }),
                require("autoprefixer"),
            ],
        },
    },
})
