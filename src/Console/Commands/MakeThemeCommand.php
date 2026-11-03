<?php

namespace Platform\Portal\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:make-theme
                            {name : Theme name (e.g. medical) or Vendor/Package (e.g. Acme/MedicalTheme)}
                            {--package : Generate as a standalone reusable Bagisto package inside packages/}
                            {--title= : Display title for the theme}
                            {--parent=portal : The parent theme to inherit from}
                            {--author= : Author or vendor name}
                            {--description= : Short description of the theme}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a complete, production-ready Portal Theme (A-to-Z) like Bagisto Package Generator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = trim($this->argument('name'));
        $isPackage = (bool) $this->option('package') || str_contains($rawName, '/');

        if ($isPackage) {
            return $this->generateThemePackage($rawName);
        }

        return $this->generateInAppTheme($rawName);
    }

    /**
     * Generate an In-App Theme under resources/themes/{name}.
     */
    protected function generateInAppTheme(string $rawName): int
    {
        $themeCode = Str::snake($rawName);
        $themeTitle = $this->option('title') ?: Str::headline($themeCode).' Portal';
        $parent = $this->option('parent') ?: 'portal';
        $author = $this->option('author') ?: 'Custom Developer';
        $description = $this->option('description') ?: "{$themeTitle} for storefront and portal pages.";
        $force = (bool) $this->option('force');

        $themeBaseDir = resource_path("themes/{$themeCode}");

        if (! $force && File::isDirectory($themeBaseDir)) {
            $this->error("Theme directory already exists: {$themeBaseDir}");
            $this->line('Use --force to overwrite.');

            return self::FAILURE;
        }

        // Directories structure (From A to Z)
        $directories = [
            "{$themeBaseDir}/assets/css",
            "{$themeBaseDir}/assets/js",
            "{$themeBaseDir}/assets/images",
            "{$themeBaseDir}/views/components/layouts/header",
            "{$themeBaseDir}/views/components/layouts/footer",
            "{$themeBaseDir}/views/components/layouts",
            "{$themeBaseDir}/views/home",
            "{$themeBaseDir}/views/page",
            "{$themeBaseDir}/views/sections",
        ];

        foreach ($directories as $dir) {
            File::ensureDirectoryExists($dir);
        }

        // 1. Manifest file (theme.php) - Zero-manual-registration
        File::put("{$themeBaseDir}/theme.php", $this->buildThemePhp(
            $themeCode,
            $themeTitle,
            $author,
            $description,
            $parent,
            "resources/themes/{$themeCode}/views",
            "resources/themes/{$themeCode}/assets"
        ));

        // 2. Standalone vite.config.js
        File::put("{$themeBaseDir}/vite.config.js", $this->buildInAppViteConfig($themeCode));

        // 3. Assets: CSS design tokens & JS entrypoint
        File::put("{$themeBaseDir}/assets/css/app.css", $this->buildCssTokens($themeTitle));
        File::put("{$themeBaseDir}/assets/js/app.js", $this->buildJsEntrypoint($themeTitle));

        // 4. Blade Layout Components: Header & Footer
        File::put("{$themeBaseDir}/views/components/layouts/header/index.blade.php", $this->buildHeaderView($themeTitle));
        File::put("{$themeBaseDir}/views/components/layouts/footer/index.blade.php", $this->buildFooterView($themeTitle));

        // 5. Blade Pages: Home & Show
        File::put("{$themeBaseDir}/views/home/index.blade.php", $this->buildHomeView());
        File::put("{$themeBaseDir}/views/page/show.blade.php", $this->buildShowView());

        // 6. Theme Preview Screenshot placeholder
        File::put("{$themeBaseDir}/assets/images/screenshot.svg", $this->buildScreenshotSvg($themeTitle));

        // 7. Inject NPM scripts into root package.json
        $this->injectNpmScript("build:theme:{$themeCode}", "vite build --config resources/themes/{$themeCode}/vite.config.js");
        $this->injectNpmScript("dev:theme:{$themeCode}", "vite --config resources/themes/{$themeCode}/vite.config.js");

        $this->newLine();
        $this->info("✨ In-App Theme [{$themeCode}] generated completely from A to Z!");
        $this->line("📁 Directory: <comment>{$themeBaseDir}</comment>");
        $this->line("⚙️  Auto-Registration: <info>{$themeBaseDir}/theme.php</info> (Active automatically, no manual config needed)");
        $this->line("⚡ Vite Config: <info>{$themeBaseDir}/vite.config.js</info>");
        $this->newLine();
        $this->comment('Commands available:');
        $this->line("    npm run build:theme:{$themeCode}   (Compile CSS/JS assets)");
        $this->line("    npm run dev:theme:{$themeCode}     (Hot reload dev server)");
        $this->newLine();
        $this->comment('👉 The theme is already visible in Bagisto Admin > Appearance > Themes!');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Generate a Standalone Bagisto Theme Package under packages/{Vendor}/{Package}.
     */
    protected function generateThemePackage(string $rawName): int
    {
        $parts = explode('/', str_replace('\\', '/', $rawName));
        $vendor = Str::studly($parts[0] ?? 'Webkul');
        $packageName = Str::studly($parts[1] ?? 'CustomTheme');

        if (! Str::endsWith($packageName, 'Theme')) {
            $packageName .= 'Theme';
        }

        $themeCode = Str::snake(Str::beforeLast($packageName, 'Theme') ?: $packageName);
        $composerVendor = Str::kebab($vendor);
        $composerPackage = Str::kebab($packageName);

        $themeTitle = $this->option('title') ?: Str::headline($themeCode).' Portal';
        $parent = $this->option('parent') ?: 'portal';
        $author = $this->option('author') ?: $vendor;
        $description = $this->option('description') ?: "{$themeTitle} Bagisto Theme Package.";
        $force = (bool) $this->option('force');

        $packageBaseDir = base_path("packages/{$vendor}/{$packageName}");

        if (! $force && File::isDirectory($packageBaseDir)) {
            $this->error("Theme package directory already exists: {$packageBaseDir}");
            $this->line('Use --force to overwrite.');

            return self::FAILURE;
        }

        // Full Bagisto Package Directory Structure
        $directories = [
            "{$packageBaseDir}/src/Config",
            "{$packageBaseDir}/src/Providers",
            "{$packageBaseDir}/src/Resources/assets/css",
            "{$packageBaseDir}/src/Resources/assets/js",
            "{$packageBaseDir}/src/Resources/assets/images",
            "{$packageBaseDir}/src/Resources/views/components/layouts/header",
            "{$packageBaseDir}/src/Resources/views/components/layouts/footer",
            "{$packageBaseDir}/src/Resources/views/home",
            "{$packageBaseDir}/src/Resources/views/page",
        ];

        foreach ($directories as $dir) {
            File::ensureDirectoryExists($dir);
        }

        // 1. composer.json
        File::put("{$packageBaseDir}/composer.json", $this->buildPackageComposerJson(
            $composerVendor,
            $composerPackage,
            $vendor,
            $packageName,
            $description
        ));

        // 2. Service Provider
        File::put(
            "{$packageBaseDir}/src/Providers/{$packageName}ServiceProvider.php",
            $this->buildPackageServiceProvider($vendor, $packageName)
        );

        // 3. src/Config/themes.php
        File::put(
            "{$packageBaseDir}/src/Config/themes.php",
            $this->buildThemePhp(
                $themeCode,
                $themeTitle,
                $author,
                $description,
                $parent,
                "packages/{$vendor}/{$packageName}/src/Resources/views",
                "packages/{$vendor}/{$packageName}/src/Resources/assets"
            )
        );

        // 4. Standalone package vite.config.js
        File::put(
            "{$packageBaseDir}/vite.config.js",
            $this->buildPackageViteConfig($themeCode, $vendor, $packageName)
        );

        // 5. Assets (CSS + JS)
        File::put("{$packageBaseDir}/src/Resources/assets/css/app.css", $this->buildCssTokens($themeTitle));
        File::put("{$packageBaseDir}/src/Resources/assets/js/app.js", $this->buildJsEntrypoint($themeTitle));

        // 6. Blade components
        File::put("{$packageBaseDir}/src/Resources/views/components/layouts/header/index.blade.php", $this->buildHeaderView($themeTitle));
        File::put("{$packageBaseDir}/src/Resources/views/components/layouts/footer/index.blade.php", $this->buildFooterView($themeTitle));
        File::put("{$packageBaseDir}/src/Resources/views/home/index.blade.php", $this->buildHomeView());
        File::put("{$packageBaseDir}/src/Resources/views/page/show.blade.php", $this->buildShowView());

        // 7. Screenshot
        File::put("{$packageBaseDir}/src/Resources/assets/images/screenshot.svg", $this->buildScreenshotSvg($themeTitle));

        // 8. Register in root composer.json PSR-4 autoload
        $this->registerInRootComposer($vendor, $packageName);

        // 9. Register in bootstrap/providers.php (Laravel 11/12 auto-load)
        $this->registerInBootstrapProviders($vendor, $packageName);

        // 10. Inject npm scripts
        $this->injectNpmScript("build:package:{$themeCode}", "vite build --config packages/{$vendor}/{$packageName}/vite.config.js");
        $this->injectNpmScript("dev:package:{$themeCode}", "vite --config packages/{$vendor}/{$packageName}/vite.config.js");

        $this->newLine();
        $this->info("🚀 Complete Bagisto Theme Package [{$vendor}/{$packageName}] scaffolded from A to Z!");
        $this->line("📁 Package Root: <comment>{$packageBaseDir}</comment>");
        $this->line("📦 Composer: <info>{$composerVendor}/{$composerPackage}</info>");
        $this->line("⚙️  Service Provider: <info>{$vendor}\\{$packageName}\\Providers\\{$packageName}ServiceProvider</info>");
        $this->newLine();
        $this->comment('Next step:');
        $this->line('    Run: composer dump-autoload');
        $this->line("    Run: npm run build:package:{$themeCode}");
        $this->newLine();
        $this->comment('👉 The theme package is ready for distribution or direct activation in Bagisto Admin!');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Render a stub file with placeholder replacements.
     */
    protected function renderStub(string $stubName, array $replacements = []): string
    {
        $stubPath = dirname(__DIR__, 3)."/stubs/theme/{$stubName}.stub";
        $content = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace("{{ {$key} }}", (string) $value, $content);
        }

        return $content;
    }

    /**
     * Build Theme configuration PHP code.
     */
    protected function buildThemePhp(
        string $code,
        string $title,
        string $author,
        string $description,
        string $parent,
        string $viewsPath,
        string $assetsPath
    ): string {
        return $this->renderStub('theme.php', [
            'themeCode' => $code,
            'themeTitle' => $title,
            'author' => $author,
            'description' => $description,
            'parent' => $parent,
        ]);
    }

    /**
     * Build In-App Theme vite.config.js.
     */
    protected function buildInAppViteConfig(string $code): string
    {
        return $this->renderStub('in-app-vite.config.js', [
            'themeCode' => $code,
        ]);
    }

    /**
     * Build Package vite.config.js.
     */
    protected function buildPackageViteConfig(string $code, string $vendor, string $packageName): string
    {
        return $this->renderStub('package-vite.config.js', [
            'themeCode' => $code,
            'vendor' => $vendor,
            'packageName' => $packageName,
        ]);
    }

    /**
     * Build Package composer.json.
     */
    protected function buildPackageComposerJson(
        string $composerVendor,
        string $composerPackage,
        string $vendor,
        string $packageName,
        string $description
    ): string {
        return $this->renderStub('composer.json', [
            'composerVendor' => $composerVendor,
            'composerPackage' => $composerPackage,
            'vendor' => $vendor,
            'packageName' => $packageName,
            'description' => $description,
        ]);
    }

    /**
     * Build Package Service Provider.
     */
    protected function buildPackageServiceProvider(string $vendor, string $packageName): string
    {
        return $this->renderStub('provider.php', [
            'vendor' => $vendor,
            'packageName' => $packageName,
            'themeCode' => Str::snake($packageName),
        ]);
    }

    /**
     * Build CSS design tokens.
     */
    protected function buildCssTokens(string $title): string
    {
        return $this->renderStub('app.css', [
            'themeTitle' => $title,
        ]);
    }

    /**
     * Build JS entrypoint.
     */
    protected function buildJsEntrypoint(string $title): string
    {
        return $this->renderStub('app.js', [
            'themeTitle' => $title,
        ]);
    }

    /**
     * Build Header Blade component.
     */
    protected function buildHeaderView(string $title): string
    {
        return $this->renderStub('header.blade.php', [
            'themeTitle' => $title,
        ]);
    }

    /**
     * Build Footer Blade view.
     */
    protected function buildFooterView(string $title): string
    {
        return $this->renderStub('footer.blade.php', [
            'themeTitle' => $title,
        ]);
    }

    /**
     * Build Home Blade view.
     */
    protected function buildHomeView(): string
    {
        return $this->renderStub('home.blade.php');
    }

    /**
     * Build Show View.
     */
    protected function buildShowView(): string
    {
        return $this->renderStub('show.blade.php');
    }

    /**
     * Build an SVG screenshot preview placeholder.
     */
    protected function buildScreenshotSvg(string $title): string
    {
        return $this->renderStub('screenshot.svg', [
            'themeTitle' => $title,
        ]);
    }

    /**
     * Register package in root composer.json autoload if present.
     */
    protected function registerInRootComposer(string $vendor, string $packageName): void
    {
        $composerJsonPath = base_path('composer.json');
        if (! File::exists($composerJsonPath)) {
            return;
        }

        try {
            $content = File::get($composerJsonPath);
            $json = json_decode($content, true);

            if (! is_array($json)) {
                return;
            }

            $namespace = "{$vendor}\\{$packageName}\\";
            $path = "packages/{$vendor}/{$packageName}/src";

            if (! isset($json['autoload']['psr-4'][$namespace])) {
                $json['autoload']['psr-4'][$namespace] = $path;
                File::put($composerJsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
            }
        } catch (\Throwable) {
            // Silently continue if composer.json cannot be parsed
        }
    }

    /**
     * Register the package ServiceProvider into bootstrap/providers.php (Laravel 11+).
     */
    protected function registerInBootstrapProviders(string $vendor, string $packageName): void
    {
        $providersPath = base_path('bootstrap/providers.php');
        if (! File::exists($providersPath)) {
            return;
        }

        try {
            $content = File::get($providersPath);
            $providerClass = "{$vendor}\\{$packageName}\\Providers\\{$packageName}ServiceProvider::class";

            if (! str_contains($content, $providerClass)) {
                $replacement = "    {$providerClass},\n];";
                $newContent = preg_replace('/\];\s*$/', $replacement, $content);
                if ($newContent && $newContent !== $content) {
                    File::put($providersPath, $newContent);
                }
            }
        } catch (\Throwable) {
            // Silently continue
        }
    }

    /**
     * Inject NPM script into root package.json if present.
     */
    protected function injectNpmScript(string $key, string $command): void
    {
        $packageJsonPath = base_path('package.json');
        if (! File::exists($packageJsonPath)) {
            return;
        }

        try {
            $content = File::get($packageJsonPath);
            $json = json_decode($content, true);

            if (! is_array($json)) {
                return;
            }

            if (! isset($json['scripts']) || ! is_array($json['scripts'])) {
                $json['scripts'] = [];
            }

            $json['scripts'][$key] = $command;

            File::put($packageJsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        } catch (\Throwable) {
            // Silently continue if package.json is read-only or malformed
        }
    }
}
