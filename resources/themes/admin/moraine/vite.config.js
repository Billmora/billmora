import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    build: {
        outDir: path.resolve(process.cwd(), "public/themes/admin/moraine"),
        emptyOutDir: true,
        rollupOptions: {
            input: {
                style: path.resolve(__dirname, "css/app.css"),
                app: path.resolve(__dirname, "js/app.js"),
            },
            output: {
                entryFileNames: "js/[name].js",
                assetFileNames: "css/[name].css",
            },
        },
    },
    plugins: [
        laravel({
            input: [
                path.resolve(__dirname, "css/app.css"),
                path.resolve(__dirname, "js/app.js"),
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
