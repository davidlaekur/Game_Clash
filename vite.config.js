import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/intro.scss",
                "resources/js/app.js",
                "resources/js/islands.jsx",
                "resources/js/audio.js",
            ],
            refresh: true,
        }),
        react(),
    ],
});
