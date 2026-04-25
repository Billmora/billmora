import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import path from "path";
import tailwindcss from "@tailwindcss/vite";
import { fileURLToPath } from "url";
import fs from "fs";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const themeJsonPath = path.resolve(__dirname, 'theme.json');
let assetsOutDir = "/themes/client/moraine";

if (fs.existsSync(themeJsonPath)) {
    const themeData = JSON.parse(fs.readFileSync(themeJsonPath, 'utf-8'));
    if (themeData.assets) {
        assetsOutDir = themeData.assets;
    }
}

const finalOutDir = path.resolve(process.cwd(), `public${assetsOutDir}`);

export default defineConfig({
    build: {
        outDir: finalOutDir,
        emptyOutDir: true,
        chunkSizeWarningLimit: 600,
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