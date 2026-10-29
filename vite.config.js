import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import path from "path";
import fs from "fs";

export default defineConfig(({ mode }) => {
    let publicDirectory = "../bagisto-2.4/public";
    if (fs.existsSync("../../../public")) {
        publicDirectory = "../../../public";
    }

    return {
        build: {
            emptyOutDir: true,
            minify: "esbuild",
            cssCodeSplit: true,
            rollupOptions: {
                output: {
                    manualChunks: {
                        vue: ["vue"],
                        veeValidate: ["vee-validate", "@vee-validate/rules", "@vee-validate/i18n"],
                        vendor: ["axios", "mitt"]
                    }
                }
            }
        },

        plugins: [
            vue(),

            laravel({
                hotFile: path.join(publicDirectory, "portal-default-vite.hot"),
                publicDirectory: publicDirectory,
                buildDirectory: "themes/portal/default/build",
                input: [
                    "src/Resources/assets/css/app.css",
                    "src/Resources/assets/js/app.js",
                ],
                refresh: true,
                preload: false,
            }),
        ],

        experimental: {
            renderBuiltUrl(filename, { hostId, hostType, type }) {
                if (hostType === "css") {
                    return path.basename(filename);
                }
            },
        },
    };
});
