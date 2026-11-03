<?php

namespace Platform\Portal\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeSectionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:make-section
                            {name : The name of the section, e.g. PricingTable or BookingCalendar}
                            {--view= : Custom view name}
                            {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a new custom Portal Section Type and its Blade component';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = trim($this->argument('name'));
        $className = Str::studly($rawName);

        if (! Str::endsWith($className, 'Section')) {
            $className .= 'Section';
        }

        $baseName = Str::beforeLast($className, 'Section');
        $code = Str::snake($baseName);
        $title = Str::headline($baseName);
        $kebab = Str::kebab($baseName);

        $viewName = $this->option('view') ?: "portal.sections.{$kebab}";

        // Target Paths
        $classDir = app_path('Portal/Sections');
        $classPath = "{$classDir}/{$className}.php";

        $viewRelativePath = str_replace('.', '/', $viewName).'.blade.php';
        $viewPath = resource_path("views/{$viewRelativePath}");

        $force = (bool) $this->option('force');

        // Check file collision
        if (! $force && File::exists($classPath)) {
            $this->error("Section class already exists: {$classPath}");
            $this->line('Use --force to overwrite.');

            return self::FAILURE;
        }

        if (! $force && File::exists($viewPath)) {
            $this->error("Blade view already exists: {$viewPath}");
            $this->line('Use --force to overwrite.');

            return self::FAILURE;
        }

        // Ensure directories exist
        File::ensureDirectoryExists($classDir);
        File::ensureDirectoryExists(dirname($viewPath));

        // Generate files
        File::put($classPath, $this->buildClassStub($className, $code, $title, $viewName));
        File::put($viewPath, $this->buildViewStub($code, $title));

        $this->info("Section class created successfully: {$classPath}");
        $this->info("Blade view created successfully: {$viewPath}");
        $this->newLine();
        $this->comment('To register this section, add the following to your AppServiceProvider::boot():');
        $this->line("    \\Platform\\Portal\\Facades\\PortalSection::register(\\App\\Portal\\Sections\\{$className}::class);");
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Render a stub file with placeholder replacements.
     */
    protected function renderStub(string $stubName, array $replacements = []): string
    {
        $stubPath = dirname(__DIR__, 3)."/stubs/section/{$stubName}.stub";
        $content = File::get($stubPath);

        foreach ($replacements as $key => $value) {
            $content = str_replace("{{ {$key} }}", (string) $value, $content);
        }

        return $content;
    }

    /**
     * Build the PHP class contents from stub.
     */
    protected function buildClassStub(string $className, string $code, string $title, string $view): string
    {
        return $this->renderStub('section.php', [
            'className' => $className,
            'code' => $code,
            'title' => $title,
            'view' => $view,
        ]);
    }

    /**
     * Build the Blade component view stub.
     */
    protected function buildViewStub(string $code, string $title): string
    {
        return $this->renderStub('section.blade.php', [
            'code' => $code,
            'title' => $title,
        ]);
    }
}
