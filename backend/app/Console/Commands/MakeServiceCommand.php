<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name : The name of the service class}';
    protected $description = 'Create a new service class';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name');
        $name = str_replace('\\', '/', $name);

        $parts = explode('/', $name);
        $className = Str::studly(array_pop($parts)) . 'Service';
        $namespace = 'App\\Services';

        if (!empty($parts)) {
            $namespace .= '\\' . implode('\\', array_map(fn ($p) => Str::studly($p), $parts));
        }

        $path = app_path('Services');
        if (!empty($parts)) {
            $path .= '/' . implode('/', array_map(fn ($p) => Str::studly($p), $parts));
        }

        if (!$files->isDirectory($path)) {
            $files->makeDirectory($path, 0755, true);
        }

        $filePath = "$path/$className.php";

        if ($files->exists($filePath)) {
            $this->error("Service [$className] already exists!");
            return Command::FAILURE;
        }

        $stub = <<<PHP
<?php

namespace $namespace;

class $className
{
    public function __construct()
    {
        //
    }
}
PHP;

        $files->put($filePath, $stub);

        $relative = Str::after($filePath, app_path() . '/');
        $this->info("Service [$relative] created successfully.");

        return Command::SUCCESS;
    }
}
