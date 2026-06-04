<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRepositoryCommand extends Command
{
    protected $signature = 'make:repository {name : The name of the repository (e.g. User or Admin/User)}';
    protected $description = 'Create a new repository interface and implementation';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name');
        $name = str_replace('\\', '/', $name);

        $parts = explode('/', $name);
        $className = Str::studly(array_pop($parts));
        $interfaceName = $className . 'RepositoryInterface';
        $repositoryName = $className . 'Repository';

        $folderName = Str::kebab($className);
        if (!empty($parts)) {
            $subFolders = implode('/', array_map(fn ($p) => Str::kebab($p), $parts));
            $folderName = $subFolders . '/' . $folderName;
        }

        $namespace = 'App\\Repositories\\' . str_replace('/', '\\', $folderName);

        $basePath = app_path('Repositories/' . $folderName);

        if (!$files->isDirectory($basePath)) {
            $files->makeDirectory($basePath, 0755, true);
        }

        $interfacePath = "$basePath/$interfaceName.php";
        $repositoryPath = "$basePath/$repositoryName.php";

        if ($files->exists($interfacePath) || $files->exists($repositoryPath)) {
            $this->error("Repository [$className] already exists!");
            return Command::FAILURE;
        }

        $interfaceStub = <<<PHP
<?php

namespace $namespace;

interface $interfaceName
{
    public function all();
    public function find(int \$id);
    public function create(array \$data);
    public function update(int \$id, array \$data);
    public function delete(int \$id);
}
PHP;

        $repositoryStub = <<<PHP
<?php

namespace $namespace;

class $repositoryName implements $interfaceName
{
    public function all()
    {
        //
    }

    public function find(int \$id)
    {
        //
    }

    public function create(array \$data)
    {
        //
    }

    public function update(int \$id, array \$data)
    {
        //
    }

    public function delete(int \$id)
    {
        //
    }
}
PHP;

        $files->put($interfacePath, $interfaceStub);
        $files->put($repositoryPath, $repositoryStub);

        $this->info("Repository [$interfaceName] created successfully.");
        $this->info("Repository [$repositoryName] created successfully.");

        return Command::SUCCESS;
    }
}
