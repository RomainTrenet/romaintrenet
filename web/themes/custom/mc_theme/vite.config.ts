import { defineConfig, type UserConfig } from "vite";
import fg from "fast-glob";

// Utility to remove scss or js extension.
const removeExt = (file: string) =>
  file.replace(/\.(scss|js)$/, "");

// Utility to keep internal path (F.E. : src/scss)
const extractName = (file: string, baseDir: string) =>
  removeExt(file.replace(baseDir, ""));

export default defineConfig(({ mode }) => {
  // Prepare rollup inputs.
  const inputs: Record<string, string> = {};

  // Find every SCSS except _*.scss.
  const scssFiles = fg.sync("src/scss/**/*.scss", {
    ignore: ["**/_*.scss"], // pas les partials
  });

  // Find every JS files.
  const jsFiles = fg.sync("src/js/**/*.js");

  // Record scss inside rollup list, prefix with scss_ to avoid css/js to have the same name.
  scssFiles.forEach(file => {
    const name = extractName(file, "src/scss/");
    inputs[`scss_${name}`] = file;
  });

  // Record js inside rollup list, prefix with js_ to avoid css/js to have the same name.
  jsFiles.forEach(file => {
    const name = extractName(file, "src/js/");
    inputs[`js_${name}`] = file;
  });

  // Return conf.
  return {
    root: "src",
    base: "",

    build: {
      outDir: "../dist",
      emptyOutDir: true,
      sourcemap: mode === "development",
      // minify: "esbuild"
      minify: mode === "production",
      rollupOptions: {
        input: inputs,
        output: {
          // Control JS files.
          entryFileNames: chunk => {
            // SCSS → dist/css/*.css
            if (chunk.name.startsWith("scss_")) {
              const clean = chunk.name.replace("scss_", "");
              // Ignored stub JS, needed by rollup.
              return `css/${clean}.js`;
            }

            // JS → dist/js/*.js
            if (chunk.name.startsWith("js_")) {
              const clean = chunk.name.replace("js_", "");
              return `js/${clean}.js`;
            }

            return "[name].js";
          },

          // Control scss files
          assetFileNames: asset => {
            const name = asset.names[0];

            // CSS file output
            if (name.endsWith(".css")) {
              const clean = name.replace("scss_", "");
              return `css/${clean}`;
            }

            return `assets/[name].[ext]`;
          }
        }
      },
    },

    css: {
      preprocessorOptions: {
        scss: {

        },
      },
    },

    server: {
      watch: {
        usePolling: true,
      },
    },
  } satisfies UserConfig
});
