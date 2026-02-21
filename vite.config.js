import { defineConfig } from 'vite';
import path from 'path';
import { pathToFileURL } from 'url';

export default defineConfig(async ({ command, mode }) => {
    const themeEnv = process.env.THEME;
    
    if (!themeEnv) {
        console.error('\x1b[31mError: THEME environment variable is not set.\x1b[0m');
        console.error('\x1b[33mPlease run vite with a theme specified, e.g., cross-env THEME=client/moraine vite build\x1b[0m\n');
        process.exit(1);
    }

    if (!themeEnv.includes('/')) {
        console.error('\x1b[31mError: Invalid THEME format.\x1b[0m');
        console.error('\x1b[33mFormat must be "type/themeName", e.g., "client/moraine" or "admin/default".\x1b[0m\n');
        process.exit(1);
    }

    const [type, themeName] = themeEnv.split('/');

    const themeConfigPath = path.resolve(__dirname, `resources/themes/${type}/${themeName}/vite.config.js`);

    console.log(`\nBuilding Theme: \x1b[36m${type}\x1b[0m / \x1b[32m${themeName}\x1b[0m`);
    console.log(`Config Path: ${themeConfigPath}\n`);

    try {
        const importUrl = pathToFileURL(themeConfigPath).href;
        
        const themeConfig = await import(importUrl);
        return themeConfig.default;
    } catch (error) {
        console.error(`\x1b[31mError: Cannot load vite config for theme '${themeName}'.\x1b[0m`);
        console.error(`\x1b[31mMake sure the path exists: ${themeConfigPath}\x1b[0m\n`);
        console.error(error);
        process.exit(1);
    }
});